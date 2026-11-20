<?php

namespace App\Services;

class GeoDistanceService
{
    public const EARTH_RADIUS_KM = 6371.0;

    public function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * (sin($dLon / 2) ** 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c;
    }

    /**
     * @param  iterable<int, array{latitude: float, longitude: float}>  $points
     */
    public function totalPathKm(iterable $points): float
    {
        $total = 0.0;
        $prev = null;

        foreach ($points as $point) {
            if ($prev !== null) {
                $total += $this->haversineKm(
                    (float) $prev['latitude'],
                    (float) $prev['longitude'],
                    (float) $point['latitude'],
                    (float) $point['longitude']
                );
            }
            $prev = $point;
        }

        return round($total, 3);
    }
}
