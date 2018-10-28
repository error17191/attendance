<?php

namespace App\Utilities;

use App\Flag as FlagModel;
use App\User;
use Carbon\Carbon;

class Flag
{
    /**
     * Fix the user flag field in testing cases
     *
     * @param \App\User $user
     * @return void
     */
    public static function fixUserFlag(User $user):void
    {
        if($user->isUsingFlag() && !static::hasActive($user->id)){
            $user->flag = 'off';
            $user->save();
        }elseif(!$user->isUsingFlag() && static::hasActive($user->id)){
            $user->flag = 'on';
            $user->save();
        }
    }

    /**
     * Fetch the current used flag
     *
     * @param int $id
     * @return \App\Flag
     */
    public static function current(int $id):FlagModel
    {
        return FlagModel::where('user_id',$id)
            ->whereNull('stopped_at')
            ->orderBy('started_at','desc')
            ->first();
    }

    /**
     * Calculate the used seconds for given flag till this moment or a given moment
     *
     * @param int $id
     * @param string $type
     * @param string $day
     * @param \Carbon\Carbon $stop
     * @return int
     */
    public static function secondsTillNow(int $id,string $type,string $day,Carbon $stop):int
    {
       if(static::hasActive($id,$day)){
           return static::daySeconds($id,$type,$day) + $stop->diffInSeconds(static::current($id)->started_at);
       }
       return static::daySeconds($id,$type,$day);
    }

    /**
     * Calculate the used seconds of given flag in given day
     *
     * @param int $id
     * @param string $type
     * @param string $day
     * @return int
     */
    public static function daySeconds(int $id,string $type,string $day):int
    {
        return FlagModel::where('user_id',$id)
            ->where('day',$day)
            ->where('type',$type)
            ->sum('seconds');
    }

    /**
     * Check if a given flag type already exists
     *
     * @param string $type
     * @return bool
     */
    public static function exists(string $type):bool
    {
        return !empty(app('settings')->getFlags()[$type]);
    }

    /**
     * Check if the flag of a given type has a time limit
     *
     * @param string $type
     * @return bool
     */
    public static function hasTimeLimit(string $type):bool
    {
        return gettype(app('settings')->getFlags()[$type]) == 'integer';
    }

    /**
     * Check if a user have an active flag today or in a given day
     *
     * @param int $id
     * @param string|null $day
     * @return bool
     */
    public static function hasActive(int $id,string $day = null):bool
    {
        $builder = FlagModel::where('user_id',$id)
            ->where('stopped_at',null)
            ->where('seconds',0);
        if($day){
            $builder->where('day',$day);
        }
        return  $builder->first() != null;
    }

    /**
     * Gets the time limit of a flag of a given type
     *
     * @param string $type
     * @return int
     */
    public static function timeLimit(string $type):int
    {
        return app('settings')->getFlags()[$type];
    }

    /**
     * Calculate the used seconds of a flag in a given day till a given moment
     *
     * @param int $id
     * @param string $type
     * @param string $day
     * @param \Carbon\Carbon $stop
     * @return bool
     */
    public static function passedTimeLimit(int $id,string $type,string $day,Carbon $stop):bool
    {
        return static::secondsTillNow($id,$type,$day,$stop) > static::timeLimit($type);
    }

    /**
     * Check if a flag of a given type is in use
     *
     * @param int $id
     * @param string $type
     * @return bool
     */
    public static function inUse(int $id,string $type):bool
    {
        return FlagModel::where('user_id',$id)
            ->where('type',$type)
            ->where('stopped_at',null)
            ->where('seconds',0)
            ->get()->count() == 1;
    }

    /**
     * Fetch the condition of all flags this day
     *
     * @param int $id
     * @return array
     */
    public static function today(int $id):array
    {
        $all = [];
        foreach (app('settings')->getFlags() as $type => $limit) {
            if(static::hasTimeLimit($type)){
                $usedSeconds = static::daySeconds($id,$type,now()->toDateString());
            }
            $all[] = [
                'type' => $type,
                'timeLimit' => static::hasTimeLimit($type) ?
                    partition_seconds($limit) : $limit,
                'limitSeconds' => $limit,
                'remainingSeconds' => static::hasTimeLimit($type) ?
                    $limit - $usedSeconds : $limit,
                'remainingTime' => static::hasTimeLimit($type) ?
                    partition_seconds($limit - $usedSeconds) : $limit,
                'inUse' => static::inUse($id,$type)
            ];
        }
        return $all;
    }

    /**
     * Fetch all editable flags
     *
     * @return array
     */
    public static function editable():array
    {
        $flags = app('settings')->getFlags();
        unset($flags['lost_time']);
        $editable = [];
        foreach ($flags as $key => $value) {
            $editable[] = [
                'name' => $key,
                'limit' => $value,
                'highlighted' => false
            ];
        }
        return $editable;
    }

    /**
     * Start a flag
     *
     * @param int $id
     * @param string $type
     * @param int $workTimeId
     * @return \App\Flag
     */
    public static function start(int $id,string $type,int $workTimeId):FlagModel
    {
        $flag = new FlagModel();
        $flag->user_id = $id;
        $flag->work_time_id = $workTimeId;
        $flag->started_at = now();
        $flag->day = now()->toDateString();
        $flag->type = $type;
        return $flag;
    }

    /**
     * Stop a flag
     *
     * @param \App\Flag $flag
     * @param \Carbon\Carbon|null $stop
     * @return \App\Flag
     */
    public static function stop(FlagModel $flag,Carbon $stop = null):FlagModel
    {
        $flag->stopped_at = $stop ?: now();
        if(static::hasTimeLimit($flag->type) && static::passedTimeLimit($flag->user_id,$flag->type,$flag->day,$flag->stopped_at)){
            $flag->seconds = static::timeLimit($flag->type) - static::daySeconds($flag->user_id,$flag->type,$flag->day);
        }else{
            $flag->seconds = $flag->stopped_at->diffInSeconds($flag->started_at);
        }
        if($stop){
            $flag->seconds++;
        }
        return $flag;
    }



}
