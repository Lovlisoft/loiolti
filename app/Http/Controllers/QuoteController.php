<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\QuoteGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

final class QuoteController extends Controller
{
    public function index(): Response
    {
        $products = Product::query()
            ->with(['material', 'quoteProfile'])
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'material_id', 'trips_required', 'quote_profile_id']);

        return Inertia::render('Quote/Index', [
            'products' => $products,
            'googleMapsApiKey' => config('quote.google_maps_api_key'),
        ]);
    }

    /**
     * Generate quote string for product + location + periodo (JSON for AJAX).
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'location' => ['required', 'string', 'max:500'],
            'periodo' => ['required', 'string', 'in:mes,semana'],
        ]);

        $product = Product::query()->findOrFail($validated['product_id']);
        $location = trim($validated['location']);

        $service = QuoteGeneratorService::fromConfig();
        $cotizacion = $service->generate($product, $location, $validated['periodo']);

        return response()->json([
            'type' => 'success',
            'cotizacion' => $cotizacion,
        ]);
    }
}
