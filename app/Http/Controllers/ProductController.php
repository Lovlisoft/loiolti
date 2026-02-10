<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PaymentPlan;
use App\Models\Product;
use App\Models\ProductMaterial;
use App\Models\QuoteProfile;
use Database\Seeders\QuoteCalculationRuleSeeder;
use Database\Seeders\QuoteProfileSeeder;
use Database\Seeders\QuoteTemplateSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ProductController extends Controller
{
    /**
     * Ensure quote profiles (Speech 1, 2, 3) exist so the product form dropdown has options.
     * Runs minimal quote seeders only when no profiles exist.
     */
    private function ensureQuoteProfilesExist(): void
    {
        if (QuoteProfile::query()->count() > 0) {
            return;
        }

        (new QuoteTemplateSeeder())->run();
        (new QuoteCalculationRuleSeeder())->run();
        (new QuoteProfileSeeder())->run();
    }

    /**
     * Derive message_id (1, 2, 3) from quote_profile slug so DB stays consistent without showing message in the form.
     */
    private function messageIdFromQuoteProfile(?string $quoteProfileId): int
    {
        if (! $quoteProfileId) {
            return 1;
        }
        $profile = QuoteProfile::query()->find($quoteProfileId);
        if (! $profile || ! preg_match('/^speech-(\d)$/', $profile->slug ?? '', $m)) {
            return 1;
        }

        return (int) $m[1];
    }

    public function index(): Response
    {
        $products = Product::query()
            ->with(['material', 'message', 'paymentPlan', 'quoteProfile'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }

    public function create(): Response
    {
        $this->ensureQuoteProfilesExist();

        return Inertia::render('Products/Create', [
            'materials' => ProductMaterial::query()->orderBy('name')->get(['id', 'name']),
            'paymentPlans' => PaymentPlan::query()->orderBy('payments')->get(),
            'quoteProfiles' => QuoteProfile::query()->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $quoteProfileId = $request->input('quote_profile_id') ?: null;
        $request->merge([
            'payment_plan_id' => $request->input('payment_plan_id') ?: null,
            'quote_profile_id' => $quoteProfileId,
            'message_id' => $this->messageIdFromQuoteProfile($quoteProfileId),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'material_id' => ['required', 'integer', 'exists:product_materials,id'],
            'message_id' => ['required', 'integer', 'exists:product_messages,id'],
            'included' => ['nullable', 'string', 'max:65535'],
            'trips_required' => ['required', 'integer', 'min:1'],
            'payment_plan_id' => ['nullable', 'integer', 'exists:payment_plans,id'],
            'quote_profile_id' => ['nullable', 'integer', 'exists:quote_profiles,id'],
        ]);

        Product::query()->create($validated);

        return redirect()->route('products.index')->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product): Response
    {
        $this->ensureQuoteProfilesExist();
        $product->load(['material', 'message', 'paymentPlan', 'quoteProfile']);

        if ($product->quote_profile_id === null && $product->message_id >= 1 && $product->message_id <= 3) {
            $profile = QuoteProfile::query()->where('slug', 'speech-' . $product->message_id)->first();
            if ($profile) {
                $product->quote_profile_id = $profile->id;
            }
        }

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'materials' => ProductMaterial::query()->orderBy('name')->get(['id', 'name']),
            'paymentPlans' => PaymentPlan::query()->orderBy('payments')->get(),
            'quoteProfiles' => QuoteProfile::query()->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $quoteProfileId = $request->input('quote_profile_id') ?: null;
        $request->merge([
            'payment_plan_id' => $request->input('payment_plan_id') ?: null,
            'quote_profile_id' => $quoteProfileId,
            'message_id' => $this->messageIdFromQuoteProfile($quoteProfileId),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'material_id' => ['required', 'integer', 'exists:product_materials,id'],
            'message_id' => ['required', 'integer', 'exists:product_messages,id'],
            'included' => ['nullable', 'string', 'max:65535'],
            'trips_required' => ['required', 'integer', 'min:1'],
            'payment_plan_id' => ['nullable', 'integer', 'exists:payment_plans,id'],
            'quote_profile_id' => ['nullable', 'integer', 'exists:quote_profiles,id'],
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Producto actualizado correctamente.');
    }
}
