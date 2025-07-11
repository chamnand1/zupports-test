<?php

namespace Tests\Unit\Services;

use App\Services\RestaurantService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RestaurantServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Prevent actual logging during tests
        Log::spy();

        // Set fake config values for Google Maps service
        config()->set('services.google_maps.key', 'fake-key');
        config()->set('services.google_maps.geocode_url', 'https://fake-geocode-url');
        config()->set('services.google_maps.places_url', 'https://fake-places-url');
        config()->set('services.google_maps.radius', 500);
    }

    public function test_it_returns_restaurants_when_geocode_and_places_succeed()
    {
        // Fake successful responses for both Geocode and Places APIs
        Http::fake([
            'https://fake-geocode-url*' => Http::response([
                'results' => [
                    [
                        'geometry' => [
                            'location' => ['lat' => 13.7563, 'lng' => 100.5018]
                        ]
                    ]
                ]
            ], 200),
            'https://fake-places-url*' => Http::response([
                'results' => [
                    ['name' => 'Restaurant A', 'place_id' => 'abc'],
                    ['name' => 'Restaurant B', 'place_id' => 'def'],
                ]
            ], 200),
        ]);

        $service = new RestaurantService();

        $results = $service->getRestaurantsByKeyword('Bangkok');

        // Assert we get two restaurants
        $this->assertCount(2, $results);
        $this->assertEquals('Restaurant A', $results[0]['name']);
    }

    public function test_it_returns_empty_when_geocode_fails()
    {
        // Fake a failed response for Geocode API
        Http::fake([
            'https://fake-geocode-url*' => Http::response([], 500),
        ]);

        $service = new RestaurantService();

        $results = $service->getRestaurantsByKeyword('Bangkok');

        // Assert no restaurants are returned
        $this->assertEmpty($results);

        // Assert error log was triggered
        Log::shouldHaveReceived('error')->once();
    }

    public function test_it_returns_empty_when_geocode_returns_no_location()
    {
        // Fake Geocode API returns no results
        Http::fake([
            'https://fake-geocode-url*' => Http::response([
                'results' => [],
            ], 200),
        ]);

        $service = new RestaurantService();

        $results = $service->getRestaurantsByKeyword('Nowhere');

        // Assert no restaurants are returned
        $this->assertEmpty($results);

        // Assert info log was triggered
        Log::shouldHaveReceived('info')->once();
    }

    public function test_it_returns_empty_when_places_fails()
    {
        // Fake Geocode API returns a valid location
        Http::fake([
            'https://fake-geocode-url*' => Http::response([
                'results' => [
                    [
                        'geometry' => [
                            'location' => ['lat' => 13.7563, 'lng' => 100.5018]
                        ]
                    ]
                ]
            ], 200),
            // Fake failed response for Places API
            'https://fake-places-url*' => Http::response([], 500),
        ]);

        $service = new RestaurantService();

        $results = $service->getRestaurantsByKeyword('Bangkok');

        // Assert no restaurants are returned
        $this->assertEmpty($results);

        // Assert error log was triggered
        Log::shouldHaveReceived('error')->once();
    }
}
