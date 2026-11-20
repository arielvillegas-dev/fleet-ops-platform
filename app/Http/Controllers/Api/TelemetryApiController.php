<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Services\TelemetryBatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelemetryApiController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'fleet-ops-platform',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function ingestBatch(Trip $trip, Request $request, TelemetryBatchService $service): JsonResponse
    {
        $expectedToken = (string) env('DEMO_TELEMETRY_TOKEN', 'demo-fleet-token');
        $provided = (string) $request->bearerToken();
        if ($expectedToken !== '' && ! hash_equals($expectedToken, $provided)) {
            return response()->json(['error' => 'Unauthorized telemetry client'], 401);
        }

        $validated = $request->validate([
            'points' => ['required', 'array', 'min:1', 'max:200'],
            'points.*.client_event_id' => ['required', 'string', 'max:64'],
            'points.*.latitude' => ['required', 'numeric', 'between:-90,90'],
            'points.*.longitude' => ['required', 'numeric', 'between:-180,180'],
            'points.*.accuracy_meters' => ['required', 'numeric', 'min:0'],
            'points.*.speed_kmh' => ['nullable', 'numeric', 'min:0'],
            'points.*.heading' => ['nullable', 'numeric', 'between:0,360'],
            'points.*.recorded_at' => ['required', 'date'],
        ]);

        $summary = $service->ingest($trip, $validated['points']);

        return response()->json($summary);
    }

    public function tripSummary(Trip $trip): JsonResponse
    {
        $trip->load(['vehicle', 'route', 'telemetryPoints']);

        return response()->json([
            'data' => [
                'id' => $trip->id,
                'status' => $trip->status,
                'operator_name' => $trip->operator_name,
                'vehicle' => $trip->vehicle?->code,
                'route' => $trip->route?->name,
                'planned_distance_km' => (float) ($trip->route?->planned_distance_km ?? 0),
                'actual_distance_km' => (float) $trip->actual_distance_km,
                'points_count' => $trip->telemetryPoints->count(),
            ],
        ]);
    }
}
