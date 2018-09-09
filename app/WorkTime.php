<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkTime extends Model
{
    public $timestamps = false;

    public function startWork()
    {
        $this->started_work_at = now();
        $this->save();
    }

    public function updateMinutes()
    {
        $seconds = now()->diffInSeconds($this->started_work_at);
        $this->seconds += $seconds;
        $this->save();
    }
}
