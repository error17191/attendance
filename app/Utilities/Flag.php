<?php

namespace App\Utilities;

use App\Flag as FlagModel;
use App\User;
use Carbon\Carbon;

class Flag
{
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

    public static function current(int $id):FlagModel
    {
        return FlagModel::where('user_id',$id)
            ->orderBy('started_at','desc')
            ->first();
    }

    public static function secondsTillNow(int $id,string $type,string $day,Carbon $stop):int
    {
       if(static::hasActive($id,$day)){
           return static::daySeconds($id,$type,$day) + $stop->diffInSeconds(static::current($id)->started_at);
       }
       return static::daySeconds($id,$type,$day);
    }

    public static function daySeconds(int $id,string $type,string $day):int
    {
        return FlagModel::where('user_id',$id)
            ->where('day',$day)
            ->where('type',$type)
            ->sum('seconds');
    }

    public static function exists(string $type):bool
    {
        return !empty(app('settings')->getFlags()[$type]);
    }

    public static function hasTimeLimit(string $type):bool
    {
        return gettype(app('settings')->getFlags()[$type]) == 'integer';
    }

    public static function hasActive(int $id,string $day = null):bool
    {
        $builder = FlagModel::where('user_id',$id)
            ->where('stopped_at',null)
            ->where('seconds',0)
            ->first();
        if($day){
            return $builder->where('day',$day) != null;
        }
        return  $builder != null;
    }

    public static function timeLimit(string $type):int
    {
        return app('settings')->getFlags()[$type];
    }

    public static function passedTimeLimit(int $id,string $type,string $day,Carbon $stop):bool
    {
        return static::secondsTillNow($id,$type,$day,$stop) > static::timeLimit($type);
    }

    public static function inUse(int $id,string $type):bool
    {
        return FlagModel::where('user_id',$id)
            ->where('type',$type)
            ->where('stopped_at',null)
            ->where('seconds',0)
            ->get()->count() == 1;
    }

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