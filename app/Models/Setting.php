<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Field
{
    public function settingOwner()
    {
        return $this->fieldable();
    }
}
