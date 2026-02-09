<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'material_id',
        'message_id',
        'included',
        'trips_required',
        'payment_plan_id',
        'quote_profile_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'trips_required' => 'integer',
        ];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(ProductMaterial::class, 'material_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(ProductMessage::class, 'message_id');
    }

    public function paymentPlan(): BelongsTo
    {
        return $this->belongsTo(PaymentPlan::class, 'payment_plan_id');
    }

    public function quoteProfile(): BelongsTo
    {
        return $this->belongsTo(QuoteProfile::class);
    }
}
