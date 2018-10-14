<?php

namespace App\Utilities;

use App\User;
use App\WorkTime;
use Carbon\Carbon;

class WorKTimeUtility
{
    public static function fixStatus(User $user):void
    {
        if($user->isWorking() && !WorkTime::where('user_id',$user->id)->where('stopped_work_at',null)->first()){
            $user->status = 'off';
            $user->save();
        }elseif(!$user->isWorking() && WorkTime::where('user_id',$user->id)->where('stopped_work_at',null)->first()){
            $user->status = 'on';
            $user->save();
        }
    }

    public static function daySeconds(int $id,string $day):int
    {
        return WorkTime::where('user_id',$id)
            ->where('day',$day)
            ->sum('seconds');
    }

    public static function noActiveWorkTime(int $id,string $day):bool
    {
        return WorkTime::where('user_id',$id)
            ->where('day',$day)
            ->where('stopped_work_at',null)
            ->where('seconds',0)
            ->get()->count() <= 0;
    }

    public static function activeWorkTime(int $id,string $day,bool $before = false):WorkTime
    {
        $sign = $before ? '<' : '=';
        return WorkTime::where('user_id',$id)
            ->where('day',$sign,$day)
            ->where('stopped_work_at',null)
            ->where('seconds',0)
            ->orderBy('started_work_at','desc')
            ->first();
    }

    public static function startWorkTime(int $id,string $status,Carbon $start = null):WorkTime
    {
        $workTime = new WorkTime();
        $workTime->user_id = $id;
        $workTime->status = $status;
        $workTime->day = now()->toDateString();
        $workTime->started_work_at = $start ? $start : now();
        $workTime->day_seconds = static::daySeconds($id,$workTime->day);
        return $workTime;
    }
}