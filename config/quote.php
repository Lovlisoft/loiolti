<?php

declare(strict_types=1);

return [
    'origin' => env('QUOTE_ORIGIN_COORDINATES', '25.694905,-100.3520877'),
    'distance_base_meters' => (int) env('QUOTE_DISTANCE_BASE_METERS', 30000),
    'price_per_km' => (float) env('QUOTE_PRICE_PER_KM', 30),
    'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
];
