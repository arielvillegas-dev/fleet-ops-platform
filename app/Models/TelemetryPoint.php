<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelemetryPoint extends Model
{
    protected $fillable = [
        'trip_id',
        'client_event_id',
        'latitude',
        'longitude',
        'accuracy_meters',
        'speed_kmh',
        'heading',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy_meters' => 'float',
        'speed_kmh' => 'float',
        'heading' => 'float',
        'recorded_at' => 'datetime',
    ];
}
