<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    public function scopeVisible($builder)
    {
        return $builder->where('hidden', 0);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function userTasks($id)
    {
        return $this->tasks()->where('user_id', $id)->get();
    }
}
