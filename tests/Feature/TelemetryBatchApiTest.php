<?php

namespace Tests\Feature;

use App\Models\Route as FleetRoute;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelemetryBatchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_ingests_telemetry_batch_idempotently_and_rejects_noisy_fixes(): void
    {
        $vehicle = Vehicle::query()->create(['code' => 'BUS-01', 'plate' => 'P-001', 'capacity' => 40]);
        $route = FleetRoute::query()->create([
            'code' => 'R-01',
            'name' => 'Line 1',
            'origin_name' => 'North',
            'destination_name' => 'South',
            'planned_distance_km' => 5.0,
        ]);
        $trip = Trip::query()->create([
            'vehicle_id' => $vehicle->id,
            'route_id' => $route->id,
            'operator_name' => 'Demo Driver',
            'status' => 'scheduled',
        ]);

        $payload = [
            'points' => [
                [
                    'client_event_id' => 'evt-1',
                    'latitude' => 13.6929,
                    'longitude' => -89.2182,
                    'accuracy_meters' => 8.0,
                    'speed_kmh' => 30,
                    'heading' => 90,
                    'recorded_at' => '2026-11-20T10:00:00Z',
                ],
                [
                    'client_event_id' => 'evt-2',
                    'latitude' => 13.6980,
                    'longitude' => -89.2182,
                    'accuracy_meters' => 10.0,
                    'speed_kmh' => 35,
                    'heading' => 90,
                    'recorded_at' => '2026-11-20T10:01:00Z',
                ],
                [
                    'client_event_id' => 'evt-noisy',
                    'latitude' => 13.7000,
                    'longitude' => -89.2182,
                    'accuracy_meters' => 180.0,
                    'speed_kmh' => 10,
                    'heading' => 0,
                    'recorded_at' => '2026-11-20T10:02:00Z',
                ],
            ],
        ];

        $this->postJson("/api/v1/trips/{$trip->id}/telemetry-batch", $payload)
            ->assertUnauthorized();

        $first = $this->withToken('demo-fleet-token')
            ->postJson("/api/v1/trips/{$trip->id}/telemetry-batch", $payload);

        $first->assertOk()
            ->assertJsonPath('accepted', 2)
            ->assertJsonPath('rejected_noisy', 1)
            ->assertJsonPath('duplicates', 0);

        $this->assertGreaterThan(0.5, $first->json('actual_distance_km'));

        // Idempotent replay
        $replay = $this->withToken('demo-fleet-token')
            ->postJson("/api/v1/trips/{$trip->id}/telemetry-batch", $payload);

        $replay->assertOk()
            ->assertJsonPath('accepted', 0)
            ->assertJsonPath('duplicates', 2);

        $this->getJson("/api/v1/trips/{$trip->id}/summary")
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.points_count', 2);
    }
}
