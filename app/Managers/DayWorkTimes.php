<?php

namespace App\Managers;

use App\WorkTime;
use App\User;

class DayWorkTimes
{
    /** @var string  */
    protected $today;

    /** @var \Illuminate\Database\Eloquent\Collection */
    protected $todayWorkTimes;

    /** @var \App\User */
    protected $user;

    /** @var \Carbon\Carbon */
    protected $now;

    public function __construct(User $user)
    {
        $this->now = now();
        $this->today = now()->toDateString();
        $this->user = $user;
        $this->todayWorkTimes = WorkTime::where('day',$this->today)
            ->where('user_id',$this->user->id)->get();
    }

    public function startedWorkingToday()
    {
        return $this->todayWorkTimes->count() > 0;
    }

    public function lastWorkDay()
    {
        if($this->startedWorkingToday()){
            return $this->lastWorkTime()->day;
        }else{
            return WorkTime::where('day'.'<',today())
                ->orderBy('day','desc')->first()->day;
        }
    }

    public function lastWorkStatus()
    {
        if($this->startedWorkingToday()){
            return $this->lastWorkTime()->status;
        }else{
            return WorkTime::where('day',$this->lastWorkDay())
                ->orderBy('day_seconds','desc')->first()->status;
        }
    }

    public function lastWorkTime()
    {
        if($this->isWorking()){
            return $this->todayWorkTimes->where('started_work_at','!=',null)->first();
        }else{
            return $this->todayWorkTimes->sortByDesc('day_seconds')->first();
        }
    }

    public function isWorking()
    {
        return $this->user->status == 'on';
    }

    public function daySecondsTillNow()
    {
        if(!$this->startedWorkingToday()){
            return 0;
        }
        if($this->isWorking()){
            return $this->lastWorkTime()->day_seconds + $this->secondsTillNow();
        }else{
            return $this->lastWorkTime()->day_seconds;
        }
    }

    public function secondsTillNow()
    {
        if(!$this->startedWorkingToday()){
            return 0;
        }
        if(!$this->isWorking()){
            return 0;
        }
        return $this->now->diffInSeconds($this->lastWorkTime()->started_work_at);
    }

    public function startWorkTime($status)
    {
        if($this->isWorking()){
            return null;
        }
        $workTime = new WorkTime();
        $workTime->day = $this->today;
        $workTime->started_work_at = $this->now;
        $workTime->user_id = $this->user->id;
        $workTime->status = $status;
        $workTime->day_seconds = $this->daySecondsTillNow();
        $workTime->save();
        $this->user->status = 'on';
        $this->user->save();
        $sign =(new Sings($this->user))->sign('start',$workTime->id);
        return [
            'workTime' => $workTime,
            'sign' => $sign
        ];
    }

    public function endWorkTime()
    {
        if(!$this->isWorking()){
            return null;
        }
        $currentWorkTime = $this->lastWorkTime();
        $currentWorkTime->seconds = $this->secondsTillNow();
        $currentWorkTime->day_seconds = $this->daySecondsTillNow();
        $currentWorkTime->started_work_at = null;
        $currentWorkTime->save();
        $this->user->status = 'off';
        $this->user->save();
        $sign = (new Sings($this->user))->sign('stop',$currentWorkTime->id);
        return [
            'workTime' => $currentWorkTime,
            'sign' => $sign
        ];
    }
}