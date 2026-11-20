<?php

use App\Http\Controllers\Api\TelemetryApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', [TelemetryApiController::class, 'health']);
    Route::get('/trips/{trip}/summary', [TelemetryApiController::class, 'tripSummary']);
    Route::post('/trips/{trip}/telemetry-batch', [TelemetryApiController::class, 'ingestBatch']);
});
