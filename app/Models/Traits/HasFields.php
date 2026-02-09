<?php

namespace App\Models\Traits;

use App\Models\Field;
use Illuminate\Support\Arr;

trait HasFields
{
    public function fields()
    {
        return $this->morphMany(Field::class, 'fieldable');
    }

    public function updateField(string $key, $value)
    {
        $field = $this->fields()->where('field', $key)->firstOr(function () use ($key, $value) {
            return $this->fields()->create([
                'field' => $key,
                'value' => $value,
            ]);
        });

        $field->value = $value;

        $field->update();
    }

    public function getField(string $key)
    {
        return optional($this->fields()->where('field', $key)->first())->value;
    }

    public function __get($field)
    {
        return parent::__get($field) ?? Arr::get($this->attributes, $field, $this->getField($field));
    }
}