<?php

namespace App\Services;

use App\Models\TelemetryPoint;
use App\Models\Trip;
use Illuminate\Support\Facades\DB;

class TelemetryBatchService
{
    public function __construct(
        private readonly GeoDistanceService $geo,
    ) {}

    /**
     * Ingests a telemetry batch idempotently using client_event_id deduplication,
     * filters noisy GPS points (> 50m accuracy), and recalculates trip distance.
     *
     * @param  array<int, array<string, mixed>>  $points
     * @return array{accepted: int, duplicates: int, rejected_noisy: int, actual_distance_km: float}
     */
    public function ingest(Trip $trip, array $points): array
    {
        return DB::transaction(function () use ($trip, $points) {
            $accepted = 0;
            $duplicates = 0;
            $rejectedNoisy = 0;

            foreach ($points as $item) {
                $lat = (float) ($item['latitude'] ?? 0);
                $lon = (float) ($item['longitude'] ?? 0);
                $acc = (float) ($item['accuracy_meters'] ?? 999);

                if (($lat === 0.0 && $lon === 0.0) || $acc <= 0 || $acc > 50.0) {
                    $rejectedNoisy++;
                    continue;
                }

                $eventId = (string) $item['client_event_id'];
                $exists = TelemetryPoint::query()
                    ->where('trip_id', $trip->id)
                    ->where('client_event_id', $eventId)
                    ->exists();

                if ($exists) {
                    $duplicates++;
                    continue;
                }

                TelemetryPoint::query()->create([
                    'trip_id' => $trip->id,
                    'client_event_id' => $eventId,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'accuracy_meters' => $acc,
                    'speed_kmh' => (float) ($item['speed_kmh'] ?? 0),
                    'heading' => (float) ($item['heading'] ?? 0),
                    'recorded_at' => $item['recorded_at'],
                ]);
                $accepted++;
            }

            $orderedPoints = TelemetryPoint::query()
                ->where('trip_id', $trip->id)
                ->orderBy('recorded_at')
                ->get(['latitude', 'longitude'])
                ->map(fn ($p) => ['latitude' => $p->latitude, 'longitude' => $p->longitude])
                ->all();

            $distanceKm = $this->geo->totalPathKm($orderedPoints);
            $trip->update([
                'actual_distance_km' => $distanceKm,
                'status' => $trip->status === 'scheduled' && $accepted > 0 ? 'in_progress' : $trip->status,
            ]);

            return [
                'accepted' => $accepted,
                'duplicates' => $duplicates,
                'rejected_noisy' => $rejectedNoisy,
                'actual_distance_km' => $distanceKm,
            ];
        });
    }
}
