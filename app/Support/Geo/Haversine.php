<?php

namespace App\Support\Geo;

final class Haversine
{
    /**
     * Great-circle distance in meters (WGS84 sphere approximation).
     */
    public static function meters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000.0;
        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $dPhi = deg2rad($lat2 - $lat1);
        $dLambda = deg2rad($lon2 - $lon1);

        $a = sin($dPhi / 2) ** 2 + cos($phi1) * cos($phi2) * sin($dLambda / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
