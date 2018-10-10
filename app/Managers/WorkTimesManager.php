<?php

namespace App\Managers;

use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use App\WorkTime;
use App\User;

class WorkTimesManager
{
    /**
     * @var string
     */
    protected $today;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $todayWorkTimes;

    /**
     * @var \App\WorkTime
     */
    protected $lastWorkTime;

    /**
     * @var \App\User
     */
    protected $user;

    /**
     * @var \Carbon\Carbon
     */
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
        $this->lastWorkTime = WorkTime::where('user_id',$this->user->id)
            ->orderBy('started_work_at','desc')->first();
        if($this->user->isWorking() && !$this->hasActiveWorkTime()){
            $this->user->status = 'off';
            $this->user->save();
        }elseif(!$this->user->isWorking() && $this->hasActiveWorkTime()){
            $this->user->status = 'on';
            $this->user->save();
        }
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

    public function hasActiveWorkTime():bool
    {
        return $this->hasAnyWorkTime() && $this->lastWorkTime->stopped_work_at == null;
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
    public function daySeconds():int
    {
        if(!$this->startedWorkingToday()){
            return 0;
        }
        return $this->todayWorkTimes->sum('seconds');
    }

    public function daySecondsTillNow():int
    {
        if($this->hasActiveWorkTime()){
            return $this->daySeconds() + (new Carbon($this->lastWorkTime->started_work_at))->diffInSeconds($this->now);
        }
        return $this->daySeconds();
    }

    /**
     * Start a new work time
     *
     * @param string $status
     * @return array|null
     */
    public function startWorkTime(string $status,bool $end = false)
    {
        if($this->user->isWorking() || $this->hasActiveWorkTime()){
            return null;
        }
        $workTime = new WorkTime();
        $workTime->user_id = $this->user->id;
        $workTime->status = $status;
        $workTime->day = $this->today;
        $workTime->started_work_at = $end ? $this->now->hour(0)->minute(0)->second(0) : $this->now;
        $workTime->day_seconds = $this->daySeconds();
        $workTime->save();
        $this->user->status = 'on';
        $this->user->save();
        if($end){
            $this->endWorkTime();
        }
        return [
            'workTimeSign' => $this->workTimeSign($workTime),
            'workTime' => $workTime->fresh()
        ];
    }

    /**
     * End the current work time
     *
     * @return array|null
     */
    public function endWorkTime()
    {
        if(!$this->user->isWorking() || !$this->hasActiveWorkTime()){
            return null;
        }
        if($this->lastWorkTime->day != $this->today){
           $endTime = $this->now->yesterday()->hour(23)->minute(59)->second(59);
        }
        $workTime = $this->lastWorkTime;
        $workTime->stopped_work_at = !empty($endTime) ?: $this->now;
        $workTime->seconds = $this->getSeconds(new Carbon($workTime->started_work_at),$this->now);
        $workTime->day_seconds += $workTime->seconds;
        $workTime->save();
        end_flag($this->user,true);
        $this->user->status = 'off';
        $this->user->save();
        if(!empty($endTime)){
            $this->startWorkTime($workTime->status,true);
        }
        return [
            'workTimeSign' => $this->workTimeSign($workTime),
            'workTime' => $workTime->fresh()
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

    public function getSeconds(Carbon $startTime,Carbon $endTime):int
    {
        return $endTime->diffInSeconds($startTime);
    }


}