<?php

use App\Flag;
use App\WorkTime;

function json_encodei($mixed)
{
    return json_encode($mixed,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function partition_seconds($seconds)
{
    $hours = (int)floor($seconds / 60 / 60);
    $seconds -= $hours * 60 * 60;
    $minutes = (int)floor($seconds / 60);
    $seconds = (int)$seconds - $minutes * 60;

    return compact('hours', 'minutes', 'seconds');
}

function end_flag($user)
{
    $flag = Flag::where('user_id',$user->id)
        ->where('day',now()->toDateString())
        ->where('stopped_at',null)->first();
    if(!$user->isWorking() || !$user->isUsingFlag() || !$flag){
        return;
    }
    $type = $flag->type;
    $flagSeconds = Flag::where('day',now()->toDateString())
        ->where('user_id',$user->id)
        ->where('type',$type)->sum('seconds');
    if($flagSeconds > app('settings')->getFlags()[$type] * 60 * 60){
        $workTime = WorkTime::where('day',now()->toDateString())
            ->where('user_id',$user->id)
            ->where('stopped_work_at',null)->first();
        $workTime->stopped_work_at = now();
        $workTime->seconds = now()->diffInSeconds($workTime->started_work_at) -
            $flagSeconds - app('settings')->getFlags()[$type];
        $workTime->day_seconds += $workTime->seconds;
        $workTime->save();
        $flag->stopped_at = now();
        $flag->seconds = now()->diffInSeconds($flag->started_at) -
            $flagSeconds - app('settings')->getFlags()[$type];
        $flag->save();
        $user->status = 'off';
        $user->flag = 'off';
        $user->save();
        return;
    }
    $flag->stopped_at = now();
    $flag->seconds = now()->diffInSeconds($flag->started_at);
    $flag->save();
    $user->flag = 'off';
    $user->save();
}


function week_days()
{
    return [
        [
            'name' => 'Sunday',
            'short_name' => 'Sun',
            'index' => 0
        ],
        [
            'name' => 'Monday',
            'short_name' => 'Mon',
            'index' => 1
        ],
        [
            'name' => 'Tuseday',
            'short_name' => 'Tus',
            'index' => 2
        ],
        [
            'name' => 'Wednesday',
            'short_name' => 'Wed',
            'index' => 3
        ],
        [
            'name' => 'Thursday',
            'short_name' => 'Thu',
            'index' => 4
        ],
        [
            'name' => 'Friday',
            'short_name' => 'Fri',
            'index' => 5
        ],
        [
            'name' => 'Saturday',
            'short_name' => 'Sat',
            'index' => 6
        ],
    ];
}

function months()
{
    return [
        [
            'index' => 1,
            'name' => 'January',
            'short_name' => 'Jan',
            'days' => 31
        ],
        [
            'index' => 2,
            'name' => 'February',
            'short_name' => 'Feb',
            'days' => 28
        ],
        [
            'index' => 3,
            'name' => 'March',
            'short_name' => 'Mar',
            'days' => 31
        ],
        [
            'index' => 4,
            'name' => 'April',
            'short_name' => 'Apr',
            'days' => 30
        ],
        [
            'index' => 5,
            'name' => 'May',
            'short_name' => 'May',
            'days' => 31
        ],
        [
            'index' => 6,
            'name' => 'June',
            'short_name' => 'Jun',
            'days' => 30
        ],
        [
            'index' => 7,
            'name' => 'July',
            'short_name' => 'Jul',
            'days' => 31
        ],
        [
            'index' => 8,
            'name' => 'August',
            'short_name' => 'Aug',
            'days' => 31
        ],
        [
            'index' => 9,
            'name' => 'September',
            'short_name' => 'Sep',
            'days' => 30
        ],
        [
            'index' => 10,
            'name' => 'October',
            'short_name' => 'Oct',
            'days' => 31
        ],
        [
            'index' => 11,
            'name' => 'November',
            'short_name' => 'Nov',
            'days' => 30
        ],
        [
            'index' => 12,
            'name' => 'December',
            'short_name' => 'Dec',
            'days' => 31
        ],
    ];
}