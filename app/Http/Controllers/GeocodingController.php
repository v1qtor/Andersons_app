<?php

namespace App\Http\Controllers;

use App\Services\GeocodingService;
use Illuminate\Http\Request;

class GeocodingController extends Controller
{
    /**
     * Geocode an address to coordinates
     */
    public function geocodeAddress(Request $request)
    {
        $validated = $request->validate([
            'address' => 'required|string|min:3',
        ]);

        $result = GeocodingService::geocodeAddress($validated['address']);

        if ($result) {
            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Address not found. Please try a different search.',
        ], 404);
    }

    /**
     * Reverse geocode coordinates to an address
     */
    public function reverseGeocode(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $address = GeocodingService::reverseGeocode($validated['latitude'], $validated['longitude']);

        if ($address) {
            return response()->json([
                'success' => true,
                'address' => $address,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Could not find address for these coordinates.',
        ], 404);
    }
}
