<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $products = Product::query()
            ->with(['material', 'quoteProfile'])
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'material_id', 'trips_required', 'quote_profile_id']);

        return Inertia::render('Dashboard', [
            'products' => $products,
            'googleMapsApiKey' => config('quote.google_maps_api_key'),
        ]);
    }
}
