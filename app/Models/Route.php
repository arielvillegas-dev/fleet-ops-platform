<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = ['code', 'name', 'origin_name', 'destination_name', 'planned_distance_km'];
}
