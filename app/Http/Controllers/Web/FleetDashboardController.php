<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Route as FleetRoute;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Services\RouteCsvImporterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FleetDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = $request->string('status')->toString();

        $tripsQuery = Trip::query()->with(['vehicle', 'route'])->withCount('telemetryPoints')->latest('id');
        if ($statusFilter !== '') {
            $tripsQuery->where('status', $statusFilter);
        }

        return view('dashboard', [
            'vehicles' => Vehicle::query()->orderBy('code')->get(),
            'routes' => FleetRoute::query()->orderBy('code')->get(),
            'trips' => $tripsQuery->get(),
            'statusFilter' => $statusFilter,
        ]);
    }

    public function storeVehicle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:32', 'unique:vehicles,code'],
            'plate' => ['required', 'string', 'max:24'],
            'capacity' => ['required', 'integer', 'between:4,120'],
        ]);

        Vehicle::query()->create([...$validated, 'status' => 'active']);

        return redirect()->route('dashboard')->with('status', 'Vehicle created successfully.');
    }

    public function storeTrip(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'route_id' => ['required', 'exists:routes,id'],
            'operator_name' => ['required', 'string', 'max:100'],
        ]);

        Trip::query()->create([
            ...$validated,
            'status' => 'scheduled',
            'actual_distance_km' => 0,
        ]);

        return redirect()->route('dashboard')->with('status', 'Trip scheduled successfully.');
    }

    public function importRoutesCsv(Request $request, RouteCsvImporterService $importer): RedirectResponse
    {
        $validated = $request->validate([
            'csv_content' => ['required', 'string', 'max:50000'],
        ]);

        $result = $importer->import($validated['csv_content']);
        $msg = "Imported {$result['imported']}, updated {$result['updated']}.";
        if (! empty($result['errors'])) {
            $msg .= ' Warnings: '.implode(' | ', $result['errors']);
        }

        return redirect()->route('dashboard')->with('status', $msg);
    }
}
