<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RestaurantService
{
    /**
     * Google Maps API key.
     */
    private string $apiKey;

    /**
     * URL for Google Geocode API.
     */
    private string $geocodeUrl;

    /**
     * URL for Google Places API.
     */
    private string $placesUrl;

    /**
     * Search radius for nearby restaurants (in meters).
     */
    private int $radius;

    /**
     * Whether to verify SSL when making HTTP requests.
     */
    private bool $shouldVerify;

    /**
     * Initialize service with configuration values.
     */
    public function __construct()
    {
        $this->apiKey = config('services.google_maps.key');
        $this->geocodeUrl = config('services.google_maps.geocode_url');
        $this->placesUrl = config('services.google_maps.places_url');
        $this->radius = config('services.google_maps.radius', 2000);
        $this->shouldVerify = !app()->isLocal(); // Disable SSL verification in local environment
    }

    /**
     * Get a list of nearby restaurants based on the given keyword.
     * 
     * @param string $keyword
     * @return array
     */
    public function getRestaurantsByKeyword(string $keyword): array
    {
        // Try to get latitude & longitude from Geocode API
        $location = $this->fetchGeocodeLocation($keyword);

        if (!$location) {
            // If no location is found, return an empty list
            return [];
        }

        // Fetch restaurants nearby the given location
        return $this->fetchNearbyRestaurants($location['lat'], $location['lng']);
    }

    /**
     * Fetch geographic coordinates from the Geocode API using a keyword (address or location name).
     * 
     * @param string $keyword
     * @return array|null
     */
    private function fetchGeocodeLocation(string $keyword): ?array
    {
        $response = Http::withOptions([
            'verify' => $this->shouldVerify,
        ])->get($this->geocodeUrl, [
            'address' => $keyword,
            'key' => $this->apiKey,
        ]);

        if ($response->failed()) {
            Log::error('Geocode API request failed', ['response' => $response->body()]);
            return null;
        }

        $data = $response->json();

        // Get first location result from Geocode API
        $location = data_get($data, 'results.0.geometry.location');

        if (!$location) {
            Log::info('Geocode API returned no results', ['keyword' => $keyword]);
            return null;
        }

        return $location;
    }

    /**
     * Fetch a list of nearby restaurants from the Places API based on coordinates.
     * 
     * @param float $lat
     * @param float $lng
     * @return array
     */
    private function fetchNearbyRestaurants(float $lat, float $lng): array
    {
        $response = Http::withOptions([
            'verify' => $this->shouldVerify,
        ])->get($this->placesUrl, [
            'location' => "{$lat},{$lng}",
            'radius' => $this->radius,
            'type' => 'restaurant',
            'key' => $this->apiKey,
        ]);

        if ($response->failed()) {
            Log::error('Places API request failed', ['response' => $response->body()]);
            return [];
        }

        $data = $response->json();

        // Get results array from Places API response
        $results = data_get($data, 'results', []);

        return $results;
    }
}
