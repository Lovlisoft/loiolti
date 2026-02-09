<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuoteProfile extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'quote_template_id',
        'conditions',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function quoteTemplate(): BelongsTo
    {
        return $this->belongsTo(QuoteTemplate::class);
    }

    public function calculationRules(): BelongsToMany
    {
        return $this->belongsToMany(
            QuoteCalculationRule::class,
            'quote_profile_calculation_rules'
        )
            ->withPivot('execution_order')
            ->withTimestamps()
            ->orderByPivot('execution_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'quote_profile_id');
    }
}
