<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteTemplateBlock extends Model
{
    protected $fillable = [
        'quote_template_id',
        'key',
        'content',
        'condition_type',
        'condition_config',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'condition_config' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function quoteTemplate(): BelongsTo
    {
        return $this->belongsTo(QuoteTemplate::class);
    }
}
