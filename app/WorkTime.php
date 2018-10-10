<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkTime extends Model
{
    public $timestamps = false;

//    protected $casts = [
//        'started_work_at' => 'datetime',
//        'stopped_work_at' => 'datetime',
//    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function flags()
    {
        return $this->hasMany(Flag::class);
    }

}
