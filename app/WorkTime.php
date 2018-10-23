<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkTime extends Model
{
    public $timestamps = false;


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function flags()
    {
        return $this->hasMany(Flag::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

}
