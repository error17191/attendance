<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkTime extends Model
{
    public $timestamps = false;

    public function updateSeconds()
    {
        $seconds = now()->diffInSeconds($this->started_work_at);
        $this->seconds += $seconds;
        $this->started_work_at = null;
    }

    public function partitionSeconds()
    {
        return partition_seconds($this->seconds);
    }

    public function secondsTillNow()
    {
        if (!$this->started_work_at) {
            return $this->seconds;
        }
        return $this->seconds + now()->diffInSeconds($this->started_work_at);
    }

    public function partitionSecondsTillNow()
    {
        if (!$this->started_work_at) {
            return partition_seconds($this->seconds);
        }
        $seconds = $this->seconds + now()->diffInSeconds($this->started_work_at);
        return partition_seconds($seconds);
    }
}
