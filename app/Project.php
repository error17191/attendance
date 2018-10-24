<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    public function scopeVisible($builder)
    {
        return $builder->where('hidden', 0);
    }
}
