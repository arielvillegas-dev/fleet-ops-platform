<?php

namespace Tests\Unit;

use App\Services\GeoDistanceService;
use PHPUnit\Framework\TestCase;

class GeoDistanceServiceTest extends TestCase
{
    public function test_computes_haversine_distance_between_two_points(): void
    {
        $geo = new GeoDistanceService();
        $km = $geo->haversineKm(13.6929, -89.2182, 13.7019, -89.2182);

        $this->assertGreaterThan(0.95, $km);
        $this->assertLessThan(1.05, $km);
    }

    public function test_total_path_sums_consecutive_segments(): void
    {
        $geo = new GeoDistanceService();
        $total = $geo->totalPathKm([
            ['latitude' => 13.6929, 'longitude' => -89.2182],
            ['latitude' => 13.6960, 'longitude' => -89.2182],
            ['latitude' => 13.7000, 'longitude' => -89.2182],
        ]);

        $this->assertGreaterThan(0.7, $total);
    }
}
