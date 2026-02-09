<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

final class ProductController extends Controller
{
    public function index(): Response
    {
        $products = Product::query()
            ->with(['material', 'message', 'paymentPlan'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }
}
