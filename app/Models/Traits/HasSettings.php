<?php

namespace App\Models\Traits;

use App\Models\Field;
use Illuminate\Support\Arr;

trait HasSettings
{
    use HasFields;

    protected $fieldPrefix = 'setting';

    public function settings() 
    {
        return $this->fields()->where('field', 'like', $this->fieldPrefix . ".%");
    }

    public function getSetting($key)
    {
        return $this->getField("{$this->fieldPrefix}.{$key}");
    }

    public function updateSetting($key, $value)
    {
        return $this->updateField("{$this->fieldPrefix}.{$key}", $value);
    }
}