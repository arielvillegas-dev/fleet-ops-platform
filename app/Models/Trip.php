<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    protected $fillable = [
        'vehicle_id',
        'route_id',
        'operator_name',
        'status',
        'started_at',
        'finished_at',
        'actual_distance_km',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'actual_distance_km' => 'float',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function telemetryPoints(): HasMany
    {
        return $this->hasMany(TelemetryPoint::class)->orderBy('recorded_at');
    }
}
