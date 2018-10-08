<?php


namespace App\Managers;

use Illuminate\Database\Eloquent\Collection;
use App\Flag;
use App\User;

class FlagManager
{
    //TODO decide to remove the class or leave it

    /** @var \App\User */
    protected $user;

    /** @var \App\Managers\WorkTimesManager */
    protected $workTimeManager;

    /** @var \Carbon\Carbon */
    protected $now;

    /** @var string */
    protected $today;

    /** @var float */
    protected $flagTypeTimeLimit = 0.0;

    /** @var string  */
    protected $flagType = '';

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->now = now();
        $this->today = now()->toDateString();
        $this->workTimeManager = new WorkTimesManager($this->user);
        if($this->user->isUsingFlag()){
            $this->setFlagType($this->currentFlag()->type);
//            $this->handleOverLimitFlag();
        }

    }

    public function currentWorkTime()
    {
        if(!$this->user->isWorking()){
            return null;
        }
        return $this->workTimeManager->todayLastWorkTime();
    }

    public function allFlags(bool $all = false):Collection
    {
        if($all){
            return Flag::where('user_id',$this->user->id)->get();
        }
        return Flag::where('user_id',$this->user->id)
            ->where('type',$this->flagType)->get();
    }

    public function todayFlags(bool $all = false):Collection
    {
        if($all){
            return Flag::where('user_id',$this->user->id)
                ->where('day',$this->today)->get();
        }
        return Flag::where('user_id',$this->user->id)
            ->where('day',$this->today)
            ->where('type',$this->flagType)->get();
    }

    public function currentFlag()
    {
        if(!$this->user->isWorking() || !$this->user->isUsingFlag()){
            return null;
        }
        return Flag::where('user_id',$this->user->id)
            ->where('day',$this->today)
            ->where('stopped_at',null)
            ->first();
    }

    public function setFlagTypeTimeLimit()
    {
        if(!$this->flagType || app('settings')->getFlags()[$this->flagType] === 'no time limit'){
            $this->flagTypeTimeLimit = 0.0;
        }
        $this->flagTypeTimeLimit = app('settings')->getFlags()[$this->flagType] * 60 * 60;
    }

    public function allFlagsTimeLimit()
    {
        $flagsTimeLimits = [];
        foreach (app('settings')->getFlags() as $flag => $value) {
            $this->setFlagType($flag);
            $flagsTimeLimits[] = [
                'type' => $flag,
                'timeLimit' => gettype($value) != 'string' ?
                    partition_seconds($value * 60 * 60) : 'no time limit',
                'limitSeconds' => $value * 60 * 60,
                'remainingTime' => $this->flagTypeRemainingTime() > 0 ?
                    partition_seconds($this->flagTypeRemainingTime()) :
                    ['hours' => 0 , 'minutes' => 0 , 'seconds' => 0],
                'remainingSeconds' => $this->flagTypeRemainingTime(),
                'inUse' => $this->currentFlag() != null ?
                    $this->currentFlag()->type == $flag :
                    false
            ];
        }
        return $flagsTimeLimits;
    }

    public function setFlagType(string $flagType)
    {
        $this->flagType = $flagType;
        $this->setFlagTypeTimeLimit();
    }

    public function flagTypeHasTimeLimit():bool
    {
        return $this->flagTypeTimeLimit > 0;
    }

    public function flagTypeSecondsTillNow():int
    {
        if($this->user->isUsingFlag() && $this->currentFlag()->type == $this->flagType){
            return $this->todayFlags()->sum('seconds') + $this->currentFlagSeconds();
        }
        return $this->todayFlags()->sum('seconds');
    }

    public function flagTypeIsAvailable():bool
    {
        return $this->flagTypeTimeLimit - $this->flagTypeSecondsTillNow() > 0;
    }

    public function flagTypeRemainingTime():int
    {
        return $this->flagTypeTimeLimit - $this->flagTypeSecondsTillNow();
    }

    public function currentFlagSeconds()
    {
        if(!$this->user->isWorking() || !$this->user->isUsingFlag()){
            return 0;
        }
        return $this->now->diffInSeconds($this->currentFlag()->started_at);
    }

    public function handleOverLimitFlag():bool
    {
        if($this->flagTypeRemainingTime() >= 0){
            return false;
        }
        $this->workTimeManager->endWorkTime();
        /** @var \App\WorkTime $workTime */
        $workTime = $this->currentFlag()->worktime;
        $workTime->seconds += $this->flagTypeRemainingTime();
        $workTime->save();
        return true;
    }

    public function startFlag(string $type)
    {
        if(!$this->user->isWorking() || $this->user->isUsingFlag()){
            return [
                'message' => 'you are not signed or already using a flag'
            ];
        }
        $this->setFlagType($type);
        if(!$this->flagTypeIsAvailable()){
            return [
                'message' => 'you already used all your limit of ' . $this->flagType . ' flag'
            ];
        }
        $flag = new Flag();
        $flag->user_id = $this->user->id;
        $flag->work_time_id = $this->currentWorkTime()->id;
        $flag->type = $this->flagType;
        $flag->day = $this->today;
        $flag->started_at = $this->now;
        $flag->save();
        $this->user->flag = 'on';
        $this->user->save();
        return [
            'message' => 'you started using ' . $this->flagType . ' flag'
        ];
    }

    public function endFlag()
    {
        if(!$this->user->isWorking() || !$this->user->isUsingFlag()){
            return [
                'message' => 'you are not signed or not using any flag'
            ];
        }
        /** @var \App\Flag $flag */
        $flag = $this->currentFlag();
        $flag->stopped_at = $this->now;
        $flag->seconds = $this->handleOverLimitFlag() ?
            $this->currentFlagSeconds() + $this->flagTypeRemainingTime() :
            $this->currentFlagSeconds();
        $flag->save();
        $this->user->flag = 'off';
        $this->user->save();
        return [
            'message' => 'you stopped ' . $this->flagType . ' flag'
        ];
    }
}