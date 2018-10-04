<?php

namespace App\Managers;

use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use App\WorkTime;
use App\User;

class WorkTimesManager
{
    /** @var string  */
    protected $today;

    /** @var \Illuminate\Database\Eloquent\Collection */
    protected $todayWorkTimes;

    /** @var \App\User */
    protected $user;

    /** @var \Carbon\Carbon */
    protected $now;

    /**
     * Construct the DayWorkTimes manager
     *
     * @param \App\User $user
     * @return void
     */
    public function __construct(User $user)
    {
        $this->now = now();
        $this->today = now()->toDateString();
        $this->user = $user;
        $this->todayWorkTimes = WorkTime::where('day',$this->today)
            ->where('user_id',$this->user->id)->get();
    }

    /**
     * Check if user started work today
     *
     * @return bool
     */
    public function startedWorkingToday():bool
    {
        return $this->todayWorkTimes->count() > 0;
    }

    /**
     * Gets the last work day
     *
     * @return string
     */
    public function lastWorkDay():string
    {
        if($this->startedWorkingToday()){
            return $this->todayLastWorkTime()->day;
        }
        return WorkTime::where('day','<',today())
            ->orderBy('day','desc')->first()->day;
    }

    /**
     * Gets the status of the last work time
     *
     * @return string
     */
    public function lastWorkStatus():string
    {
        if(!$this->hasAnyWorkTime()){
            return '';
        }
        if($this->startedWorkingToday()){
            return $this->todayLastWorkTime()->status;
        }
        return WorkTime::where('day',$this->lastWorkDay())
            ->orderBy('day_seconds','desc')->first()->status;
    }

    /**
     * Gets the last work time
     *
     * @return \App\WorkTime|null
     */
    public function todayLastWorkTime()
    {
        if(!$this->startedWorkingToday()){
            return null;
        }
        return $this->todayWorkTimes->sortByDesc('started_work_at')->first();
    }

    /**
     * Calculate the worked seconds today till now
     *
     * @return int
     */
    public function daySecondsTillNow():int
    {
        if(!$this->startedWorkingToday()){
            return 0;
        }
        if($this->user->isWorking()){
            return $this->todayLastWorkTime()->day_seconds + $this->secondsTillNow();
        }
        return $this->todayLastWorkTime()->day_seconds;
    }

    /**
     * Calculate the seconds from the start of last work time till now if the user is working
     *
     * @return int
     */
    public function secondsTillNow():int
    {
        if(!$this->startedWorkingToday() || !$this->user->isWorking()){
            return 0;
        }
        return $this->now->diffInSeconds($this->todayLastWorkTime()->started_work_at);
    }

    /**
     * Start a new work time
     *
     * @param string $status
     * @return array|null
     */
    public function startWorkTime(string $status)
    {
        if($this->user->isWorking()){
            return null;
        }
        $workTime = new WorkTime();
        $workTime->user_id = $this->user->id;
        $workTime->status = $status;
        $workTime->day = $this->today;
        $workTime->started_work_at = $this->now;
        $workTime->day_seconds = $this->daySecondsTillNow();
        $workTime->save();
        $this->user->status = 'on';
        $this->user->save();
        return [
            'workTimeSign' => $this->workTimeSign($workTime),
            'workTime' => $workTime
        ];
    }

    /**
     * End the current work time
     *
     * @return array|null
     */
    public function endWorkTime()
    {
        if(!$this->user->isWorking()){
            return null;
        }
        $workTime = $this->todayLastWorkTime();
        $workTime->stopped_work_at = $this->now;
        $workTime->seconds = $this->secondsTillNow();
        $workTime->day_seconds = $this->daySecondsTillNow();
        $workTime->save();
        $flagMessage = end_flag($this->user,true);
        $this->user->status = 'off';
        $this->user->save();
        return [
            'workTimeSign' => $this->workTimeSign($workTime),
            'workTime' => $workTime,
            'flagMessage' => $flagMessage
        ];
    }

    /**
     * Format the work time to get the sign data
     *
     * @param \App\WorkTime $workTime
     * @return array
     */
    public function workTimeSign(WorkTime $workTime):array
    {
        return [
            'started_at' => (new Carbon($workTime->started_work_at))->toTimeString(),
            'stopped_at' => $workTime->stopped_work_at != null ?
                (new Carbon($workTime->stopped_work_at))->toTimeString() : null,
            'status' => $workTime->status
        ];
    }

    /**
     * Format a collection of work times to get the work times data
     *
     * @param \Illuminate\Database\Eloquent\Collection $workTimes
     * @return array
     */
    public function workTimesSigns(Collection $workTimes):array
    {
        $workTimesSigns = [];
        foreach ($workTimes as $workTime) {
            $workTimesSigns[] = $this->workTimeSign($workTime);
        }
        return $workTimesSigns;
    }

    /**
     * Format the work times of this day to get today work time signs data
     *
     * @return array|null
     */
    public function todayWorkTimeSigns()
    {
        if(!$this->startedWorkingToday()){
            return [];
        }
        return $this->workTimesSigns($this->todayWorkTimes->sortBy('started_work_at'));
    }

    /**
     * Check if the user has any work time
     *
     * @return bool
     */
    public function hasAnyWorkTime():bool
    {
        return WorkTime::where('user_id',$this->user->id)->get()->count() > 0;
    }
}