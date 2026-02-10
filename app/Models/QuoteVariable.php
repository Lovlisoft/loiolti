<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteVariable extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'source',
        'data_type',
        'format',
        'default_value',
        'example_value',
        'help_text',
        'color',
    ];
}
