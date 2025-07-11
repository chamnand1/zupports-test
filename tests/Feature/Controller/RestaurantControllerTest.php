<?php

namespace Tests\Feature\Controllers;

use App\Services\RestaurantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class RestaurantControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake config for Google Maps
        Config::set('services.google_maps.key', 'fake-key');
        Config::set('services.google_maps.cache_ttl', 3600);

        // Clear cache before each test
        Cache::flush();
    }

    public function test_it_returns_empty_restaurants_when_keyword_is_missing()
    {
        $response = $this->get(route('restaurants.search'));

        $response->assertInertia(fn (AssertableInertia $page) => 
            $page->component('Restaurants')
                ->where('restaurants', [])
                ->where('keyword', null)
        );
    }

    public function test_it_returns_restaurants_when_keyword_provided()
    {
        // Mock RestaurantService
        $fakeService = $this->mock(RestaurantService::class);
        $fakeService->shouldReceive('getRestaurantsByKeyword')
            ->with('Bangkok')
            ->andReturn([
                ['name' => 'Test Restaurant', 'place_id' => 'abc']
            ]);

        // Send request with keyword
        $response = $this->get(route('restaurants.search', ['keyword' => 'Bangkok']));

        // Assert Inertia response
        $response->assertInertia(fn (AssertableInertia $page) =>
            $page->component('Restaurants')
                ->has('restaurants', 1)
                ->where('keyword', 'Bangkok')
                ->where('restaurants.0.name', 'Test Restaurant')
                ->where('restaurants.0.placeId', 'abc')
        );
    }

    public function test_it_uses_cache_when_keyword_is_cached()
    {
        $keyword = 'Bangkok';
        $cacheKey = 'restaurants_' . hash('xxh3', $keyword);

        // Pre-store fake data in cache
        Cache::put($cacheKey, [
            ['name' => 'Cached Restaurant', 'place_id' => 'cached_id']
        ], 3600);

        // Partial mock service to ensure it won't be called
        $this->partialMock(RestaurantService::class, function ($mock) {
            $mock->shouldNotReceive('getRestaurantsByKeyword');
        });

        // Send request
        $response = $this->get(route('restaurants.search', ['keyword' => $keyword]));

        // Assert Inertia response uses cached data
        $response->assertInertia(fn (AssertableInertia $page) =>
            $page->component('Restaurants')
                ->has('restaurants', 1)
                ->where('keyword', $keyword)
                ->where('restaurants.0.name', 'Cached Restaurant')
                ->where('restaurants.0.placeId', 'cached_id')
        );
    }
}
