<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\QuoteTemplate;
use App\Models\QuoteVariable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class QuoteTemplateController extends Controller
{
    public function index(): Response
    {
        $templates = QuoteTemplate::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'is_active', 'sort_order', 'updated_at']);

        return Inertia::render('QuoteTemplates/Index', [
            'templates' => $templates,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('QuoteTemplates/Create', [
            'variables' => QuoteVariable::query()->orderBy('key')->get(['key', 'name', 'source', 'data_type', 'example_value', 'help_text', 'color']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:quote_templates,slug'],
            'content' => ['required', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        QuoteTemplate::query()->create($validated);

        return redirect()->route('quote-templates.index')->with('success', 'Plantilla creada correctamente.');
    }

    public function edit(QuoteTemplate $quoteTemplate): Response
    {
        return Inertia::render('QuoteTemplates/Edit', [
            'template' => $quoteTemplate->only(['id', 'name', 'slug', 'content', 'is_active', 'sort_order']),
            'variables' => QuoteVariable::query()->orderBy('key')->get(['key', 'name', 'source', 'data_type', 'example_value', 'help_text', 'color']),
        ]);
    }

    public function update(Request $request, QuoteTemplate $quoteTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:quote_templates,slug,' . $quoteTemplate->id],
            'content' => ['required', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $quoteTemplate->update($validated);

        return redirect()->route('quote-templates.index')->with('success', 'Plantilla actualizada correctamente.');
    }

    public function destroy(QuoteTemplate $quoteTemplate): RedirectResponse
    {
        $quoteTemplate->delete();

        return redirect()->route('quote-templates.index')->with('success', 'Plantilla eliminada.');
    }
}
