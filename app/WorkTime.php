<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkTime extends Model
{
    public $timestamps = false;

    protected $dates = [
        'started_work_at',
        'stopped_work_at'
    ];

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

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public static function openForUser($id = null)
    {
        $id = $id ?: auth()->id();
        return static::where('user_id', $id)
            ->whereNull('stopped_work_at')->first();
    }
}
