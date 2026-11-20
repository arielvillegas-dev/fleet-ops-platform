<?php

use App\Http\Controllers\Web\FleetDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FleetDashboardController::class, 'index'])->name('dashboard');
Route::post('/vehicles', [FleetDashboardController::class, 'storeVehicle'])->name('vehicles.store');
Route::post('/trips', [FleetDashboardController::class, 'storeTrip'])->name('trips.store');
Route::post('/routes/import-csv', [FleetDashboardController::class, 'importRoutesCsv'])->name('routes.import');
