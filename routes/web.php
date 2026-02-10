<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\QuoteTemplateController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\User\OauthController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\User\LoginLinkController;

Route::get('/', [WelcomeController::class, 'home'])->name('home');

Route::prefix('auth')->group(
    function () {
        // OAuth
        Route::get('/redirect/{provider}', [OauthController::class, 'redirect'])->name('oauth.redirect');
        Route::get('/callback/{provider}', [OauthController::class, 'callback'])->name('oauth.callback');
        // Magic Link
        Route::middleware('throttle:login-link')->group(function () {
            Route::post('/login-link', [LoginLinkController::class, 'store'])->name('login-link.store');
            Route::get('/login-link/{token}', [LoginLinkController::class, 'login'])
                ->name('login-link.login')
                ->middleware('signed');
        });
    }
);

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::get('/quote', [QuoteController::class, 'index'])->name('quote.index');
    Route::post('/quote/generate', [QuoteController::class, 'generate'])->name('quote.generate');

    Route::get('/quote-templates', [QuoteTemplateController::class, 'index'])->name('quote-templates.index');
    Route::get('/quote-templates/create', [QuoteTemplateController::class, 'create'])->name('quote-templates.create');
    Route::post('/quote-templates', [QuoteTemplateController::class, 'store'])->name('quote-templates.store');
    Route::get('/quote-templates/{quoteTemplate}/edit', [QuoteTemplateController::class, 'edit'])->name('quote-templates.edit');
    Route::put('/quote-templates/{quoteTemplate}', [QuoteTemplateController::class, 'update'])->name('quote-templates.update');
    Route::delete('/quote-templates/{quoteTemplate}', [QuoteTemplateController::class, 'destroy'])->name('quote-templates.destroy');

    Route::delete('/auth/destroy/{provider}', [OauthController::class, 'destroy'])->name('oauth.destroy');

    Route::resource('/subscriptions', SubscriptionController::class)
        ->names('subscriptions')
        ->only(['index', 'create', 'store', 'show']);
});
