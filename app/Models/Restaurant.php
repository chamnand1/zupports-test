<?php

namespace App\Models;

class Restaurant
{
    public string $name;
    public ?string $address;
    public float $latitude;
    public float $longitude;
    public ?string $photoUrl;
    public ?string $placeId;

    public function __construct(array $data, string $apiKey)
    {
        $this->name = $data['name'] ?? '';
        $this->address = $data['vicinity'] ?? ($data['formatted_address'] ?? null);

        $this->latitude = $data['geometry']['location']['lat'] ?? 0.0;
        $this->longitude = $data['geometry']['location']['lng'] ?? 0.0;

        $this->placeId = $data['place_id'] ?? null;

        if (!empty($data['photos'][0]['photo_reference'])) {
            $photoRef = $data['photos'][0]['photo_reference'];
            $this->photoUrl = $this->generatePhotoUrl($photoRef, $apiKey);
        } else {
            $this->photoUrl = null;
        }
    }

    private function generatePhotoUrl(string $photoReference, string $apiKey, int $maxWidth = 400): string
    {
        return "https://maps.googleapis.com/maps/api/place/photo?maxwidth={$maxWidth}&photoreference={$photoReference}&key={$apiKey}";
    }
}
