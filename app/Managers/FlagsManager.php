<?php


namespace App\Managers;

use Carbon\Carbon;
use App\WorkTime;
use App\Flag;
use App\User;

class FlagsManager
{
    /** @var \App\User */
    protected $user;

    /** @var \App\Managers\WorkTimesManager */
    protected $workTimeManager;

    /** @var \Carbon\Carbon */
    protected $now;

    /** @var string */
    protected $today;

    /** @var \App\WorkTime */
    protected $currentWorkTime;

    /** @var \Illuminate\Database\Eloquent\Collection */
    protected $todayFlags;

    /** @var \Illuminate\Database\Eloquent\Collection */
    protected $allFlags;

    /** @var \App\Flag */
    protected $currentFlag;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->now = now();
        $this->today = now()->toDateString();
    }

    public function setCurrentWorkTime()
    {
        if(!$this->user->isWorking()){
            return;
        }
        $this->currentWorkTime = $this->workTimeManager->todayLastWorkTime();
    }

    public function setAllFlags()
    {
        $this->allFlags = Flag::where('user_id',$this->user->id)->get();
    }

    public function setTodayFlags()
    {
        $this->todayFlags = Flag::where('user_id',$this->user->id)
            ->where('day',$this->today)->get();
    }

    public function setCurrentFlag()
    {
        if(!$this->user->isWorking()){
            return;
        }
        $this->currentFlag = Flag::where('user_id',$this->user->id)
            ->where('day',$this->today)
            ->orderBy('started_at','desc')
            ->first();
    }

    public function currentFlagSeconds()
    {
        if(!$this->user->isWorking()){
            return 0;
        }
        return $this->now->diffInSeconds($this->currentFlag->started_at);
    }

    public function startFlag(string $type)
    {
        if(!$this->user->isWorking()){
            return;
        }
        $this->setCurrentFlag();
        $this->setCurrentWorkTime();
        $flag = new Flag();
        $flag->user_id = $this->user->id;
        $flag->work_time_id = $this->currentWorkTime->id;
        $flag->type = $type;
        $flag->day = $this->today;
        $flag->started_at = $this->now;
        $flag->save();
    }

    public function endFlag()
    {
        if(!$this->user->isWorking()){
            return;
        }
        $this->setCurrentFlag();
        $flag = $this->currentFlag;
        $flag->stopped_at = $this->now;
        $flag->seconds = $this->currentFlagSeconds();
        $flag->save();
    }
}