<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class QuoteCalculationRule extends Model
{
    protected $fillable = [
        'key',
        'name',
        'execution_order',
        'formula_type',
        'formula',
        'conditions',
        'output_variable_key',
    ];

    protected function casts(): array
    {
        return [
            'execution_order' => 'integer',
            'conditions' => 'array',
        ];
    }

    public function quoteProfiles(): BelongsToMany
    {
        return $this->belongsToMany(
            QuoteProfile::class,
            'quote_profile_calculation_rules'
        )
            ->withPivot('execution_order')
            ->withTimestamps()
            ->orderByPivot('execution_order');
    }
}
