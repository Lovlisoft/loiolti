<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteContext extends Model
{
    protected $fillable = [
        'key',
        'name',
        'data_type',
        'allowed_values',
        'required',
    ];

    protected function casts(): array
    {
        return [
            'allowed_values' => 'array',
            'required' => 'boolean',
        ];
    }
}
