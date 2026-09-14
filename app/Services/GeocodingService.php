<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    /**
     * Geocode an address to coordinates using Nominatim API (OpenStreetMap)
     *
     * @param  string  $address  The address to geocode
     * @return array|null Array with 'latitude', 'longitude', 'address' or null if not found
     */
    public static function geocodeAddress(string $address): ?array
    {
        try {
            $response = Http::get('https://nominatim.openstreetmap.org/search', [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
            ]);

            if ($response->successful() && $response->json()) {
                $result = $response->json()[0];

                return [
                    'latitude' => (float) $result['lat'],
                    'longitude' => (float) $result['lon'],
                    'address' => $result['display_name'] ?? $address,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Geocoding error: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Reverse geocode coordinates to an address using Nominatim API
     *
     * @return string|null The address or null if not found
     */
    public static function reverseGeocode(float $latitude, float $longitude): ?string
    {
        try {
            $response = Http::get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'lat' => $latitude,
                'lon' => $longitude,
            ]);

            if ($response->successful()) {
                return $response->json()['address']['formatted'] ?? $response->json()['display_name'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Reverse geocoding error: '.$e->getMessage());
        }

        return null;
    }
}
