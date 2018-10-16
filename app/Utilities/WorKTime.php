<?php

namespace App\Utilities;

use Illuminate\Database\Eloquent\Collection;
use App\User;
use App\WorkTime as WorkTimeModel;
use Carbon\Carbon;

class WorKTime
{
    public static function fixStatus(User $user):void
    {
        if($user->isWorking() && !WorkTimeModel::where('user_id',$user->id)->where('stopped_work_at',null)->first()){
            $user->status = 'off';
            $user->save();
        }elseif(!$user->isWorking() && WorkTimeModel::where('user_id',$user->id)->where('stopped_work_at',null)->first()){
            $user->status = 'on';
            $user->save();
        }
    }

    public static function daySeconds(int $id,string $day):int
    {
        return WorkTimeModel::where('user_id',$id)
            ->where('day',$day)
            ->sum('seconds');
    }

    public static function secondsTillNow(int $id,string $day):int
    {
        if(static::noActive($id,$day)){
            return static::daySeconds($id,$day);
        }
        return static::daySeconds($id,$day) + now()->diffInSeconds(static::active($id,$day)->started_work_at);
    }

    public static function today(int $id):Collection
    {
        return WorkTimeModel::where('user_id',$id)
            ->where('day',now()->toDateString())
            ->get();
    }

    public static function startedToday(int $id):bool
    {
        return static::today($id)->count() > 0;
    }

    public static function sign(WorkTimeModel $workTime):array
    {
        return [
            'started_at' => (new Carbon($workTime->started_work_at))->toTimeString(),
            'stopped_at' => $workTime->stopped_work_at != null ?
                (new Carbon($workTime->stopped_work_at))->toTimeString() : null,
            'status' => $workTime->status
        ];
    }

    public static function todaySigns(int $id):array
    {
        $signs = [];
        foreach (static::today($id) as $workTime) {
            $signs[] = static::sign($workTime);
        }
        return $signs;
    }

    public static function noActive(int $id,string $day):bool
    {
        return WorkTimeModel::where('user_id',$id)
            ->where('day',$day)
            ->where('stopped_work_at',null)
            ->where('seconds',0)
            ->get()->count() <= 0;
    }

    public static function active(int $id,string $day,bool $before = false):WorkTimeModel
    {
        $sign = $before ? '<' : '=';
        return WorkTimeModel::where('user_id',$id)
            ->where('day',$sign,$day)
            ->where('stopped_work_at',null)
            ->where('seconds',0)
            ->orderBy('started_work_at','desc')
            ->first();
    }

    public static function hasAny(int $id):bool
    {
        return WorkTimeModel::where('user_id',$id)->first() != null;
    }

    public static function last(int $id):WorkTimeModel
    {
        return WorkTimeModel::where('user_id',$id)
            ->orderBy('started_work_at','desc')
            ->first();
    }

    public static function start(int $id,string $status,Carbon $start = null):WorkTimeModel
    {
        $workTime = new WorkTimeModel();
        $workTime->user_id = $id;
        $workTime->status = $status;
        $workTime->day = now()->toDateString();
        $workTime->started_work_at = $start ?: now();
        $workTime->day_seconds = static::daySeconds($id,$workTime->day);
        return $workTime;
    }

    public static function stop(WorkTimeModel $workTime,Carbon $stop = null,int $seconds = 0):WorkTimeModel
    {
        $workTime->stopped_work_at = $stop ?: now();
        $workTime->seconds = $seconds ?: $workTime->stopped_work_at->diffInSeconds($workTime->started_work_at);
        $workTime->day_seconds += $workTime->seconds;
        return $workTime;
    }
}