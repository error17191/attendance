<?php

namespace App\Utilities;

use App\Task;
use Illuminate\Database\Eloquent\Collection;
use App\User;
use App\WorkTime as WorkTimeModel;
use Carbon\Carbon;

class WorKTime
{
    /**
     * Fix incorrect user status used in testing
     *
     * @param \App\User $user
     * @return void
     */
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

    /**
     * Calculate the worked seconds in given day
     *
     * @param int $id
     * @param string $day
     * @return int
     */
    public static function daySeconds(int $id,string $day):int
    {
        return WorkTimeModel::where('user_id',$id)
            ->where('day',$day)
            ->sum('seconds');
    }

    /**
     * Calculate the worked seconds in a day till this second
     *
     * @param int $id
     * @param string $day
     * @return int
     */
    public static function secondsTillNow(int $id,string $day):int
    {
        if(static::noActive($id,$day)){
            return static::daySeconds($id,$day);
        }
        return static::daySeconds($id,$day) + now()->diffInSeconds(static::active($id,$day)->started_work_at);
    }

    /**
     * Fetch the work times for this day
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function today(int $id):Collection
    {
        return WorkTimeModel::where('user_id',$id)
            ->where('day',now()->toDateString())
            ->get();
    }

    /**
     * Check if user started any work time this day
     *
     * @param int $id
     * @return bool
     */
    public static function startedToday(int $id):bool
    {
        return static::today($id)->count() > 0;
    }

    /**
     * Format a work time model to work sign
     *
     * @param \App\WorkTime $workTime
     * @return array
     */
    public static function sign(WorkTimeModel $workTime):array
    {
        return [
            'started_at' => (new Carbon($workTime->started_work_at))->toTimeString(),
            'stopped_at' => $workTime->stopped_work_at != null ?
                (new Carbon($workTime->stopped_work_at))->toTimeString() : null,
            'status' => $workTime->status
        ];
    }

    /**
     * Fetch all work signs this day
     *
     * @param int $id
     * @return array
     */
    public static function todaySigns(int $id):array
    {
        $signs = [];
        foreach (static::today($id) as $workTime) {
            $signs[] = static::sign($workTime);
        }
        return $signs;
    }

    /**
     * Check if the user have any active work time in a given day
     *
     * @param int $id
     * @param string $day
     * @return bool
     */
    public static function noActive(int $id,string $day):bool
    {
        return WorkTimeModel::where('user_id',$id)
            ->where('day',$day)
            ->where('stopped_work_at',null)
            ->where('seconds',0)
            ->get()->count() <= 0;
    }

    /**
     * Fetch the active work time for a given day or before this day if before flag set to true
     *
     * @param int $id
     * @param string $day
     * @param bool|false $before
     * @return \App\WorkTime
     */
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

    /**
     * Check if the user has any work time
     *
     * @param int $id
     * @return bool
     */
    public static function hasAny(int $id):bool
    {
        return WorkTimeModel::where('user_id',$id)->first() != null;
    }

    /**
     * Fetch last work time for the user
     *
     * @param int $id
     */
    public static function last(int $id)
    {
        return WorkTimeModel::where('user_id',$id)
            ->orderBy('started_work_at','desc')
            ->first();
    }

    /**
     * Start a work time
     *
     * @param int $id
     * @param $task
     * @param $project_id
     * @param \Carbon\Carbon|null $start
     * @return \App\WorkTime
     */
    public static function start(int $id,$task,$project_id ,Carbon $start = null):WorkTimeModel
    {
        $workTime = new WorkTimeModel();
        $workTime->user_id = $id;
        $workTime->project_id = $project_id;
        if(isset($task['id'])){
            $workTime->task_id = $task->id;
        }else{
            $newTask = new Task();
            $newTask->content = $task->content;
            $newTask->project_id = $project_id;
            $newTask->user_id = $id;
            $newTask->save();
            $workTime->task_id = $newTask->id;
        }
        $workTime->day = now()->toDateString();
        $workTime->started_work_at = $start ?: now();
        $workTime->day_seconds = static::daySeconds($id,$workTime->day);
        return $workTime;
    }

    /**
     * Stop a work time
     *
     * @param \App\WorkTime $workTime
     * @param \Carbon\Carbon|null $stop
     * @param int|0 $seconds
     * @return \App\WorkTime
     */
    public static function stop(WorkTimeModel $workTime,Carbon $stop = null,int $seconds = 0):WorkTimeModel
    {
        $workTime->stopped_work_at = $stop ?: now();
        $workTime->seconds = $seconds ?: $workTime->stopped_work_at->diffInSeconds($workTime->started_work_at);
        $workTime->day_seconds += $workTime->seconds;
        return $workTime;
    }
}
