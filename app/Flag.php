<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Flag extends Model
{
    protected $fillable = [
        'user_id','work_time_id','type','day','started_at','stopped_at','seconds'
    ];

    public function workTime()
    {
        return $this->belongsTo(WorkTime::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
