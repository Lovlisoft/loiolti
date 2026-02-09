<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuoteTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'content',
        'content_html',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(QuoteTemplateBlock::class)->orderBy('sort_order');
    }

    public function quoteProfiles(): HasMany
    {
        return $this->hasMany(QuoteProfile::class);
    }
}
