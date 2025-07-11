<?php

namespace App\Http\Controllers\Restaurants;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Services\RestaurantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class RestaurantController extends Controller
{
    /**
     * The service responsible for fetching restaurant data from Google APIs.
     */
    private RestaurantService $restaurantService;

    /**
     * Inject RestaurantService via constructor.
     *
     * @param RestaurantService $restaurantService
     */
    public function __construct(RestaurantService $restaurantService)
    {
        $this->restaurantService = $restaurantService;
    }

    /**
     * Search restaurants by a given keyword.
     * 
     * This method:
     * - Validates if the keyword exists.
     * - Uses cache to avoid unnecessary API calls.
     * - Calls the RestaurantService to fetch data if not cached.
     * - Maps raw API results to Restaurant model instances.
     * - Returns an Inertia response with data for the frontend.
     *
     * @param Request $request
     * @return Response
     */
    public function search(Request $request): Response
    {
        $keyword = $request->input('keyword');

        // Return empty state if keyword is missing
        if (empty($keyword)) {
            return Inertia::render('Restaurants', [
                'restaurants' => [],
                'keyword' => null,
            ]);
        }

        // Generate a unique, fast hash-based cache key
        $cacheKey = 'restaurants_' . hash('xxh3', $keyword);

        // Cache TTL in seconds (default to 1 hour if not configured)
        $cacheTtl = config('services.google_maps.cache_ttl', 3600);

        // Retrieve results from cache or fetch from service if missing
        $results = Cache::remember($cacheKey, $cacheTtl, fn () =>
            $this->restaurantService->getRestaurantsByKeyword($keyword)
        );

        // API key used for Restaurant model (for photo or additional Google API usage)
        $apiKey = config('services.google_maps.key');

        // Map raw data array to Restaurant model instances
        $restaurants = array_map(fn($item) => new Restaurant($item, $apiKey), $results);

        // Return Inertia response with restaurant data and search keyword
        return Inertia::render('Restaurants', [
            'restaurants' => $restaurants,
            'keyword' => $keyword,
        ]);
    }
}
