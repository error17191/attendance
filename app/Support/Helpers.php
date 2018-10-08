<?php

use App\Flag;
use App\Managers\WorkTimesManager;

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

function end_flag($user,$stopWork = false)
{
    //TODO decide to leave as function or move to the class
    $flag = get_current_flag($user);

    if(!$user->isWorking() || !$user->isUsingFlag() || !$flag){
        return response()->json([
            'message' => 'you are not working or using any flag'
        ]);
    }

    $type = $flag->type;
    $flagSeconds = now()->diffInSeconds($flag->started_at);
    $flagUsedSeconds = get_flag_used_time_today($type,$user);
    $todayFlagSeconds = $flagUsedSeconds + $flagSeconds;


    if(flag_has_time_limit($type) && $todayFlagSeconds > get_flag_time_limit_seconds($type)){
        $workTime = $flag->workTime;
        $workTime->stopped_work_at = now();
        $workTime->seconds = now()->diffInSeconds($workTime->started_work_at) +
            get_flag_time_limit_seconds($type) - $todayFlagSeconds;
        $workTime->day_seconds += $workTime->seconds;
        $workTime->save();

        $flag->stopped_at = now();
        $flag->seconds = get_flag_time_limit_seconds($type) - $flagUsedSeconds;
        $flag->save();

        $user->status = 'off';
        $user->flag = 'off';
        $user->save();

        if(!$stopWork){
            (new WorkTimesManager($user))->startWorkTime($workTime->status);
        }

        return response()->json([
            'message' => 'you passed your lost time by ' . ($todayFlagSeconds - app('settings')->getFlags()[$type])
        ]);
    }

    $flag->stopped_at = now();
    $flag->seconds = $flagSeconds;
    $flag->save();

    $user->flag = 'off';
    $user->save();

    return response()->json([
        'message' => 'you stopped using ' . $type . ' flag'
    ]);
}

function get_all_flags($user)
{
    $allFlags = [];
    foreach (app('settings')->getFlags() as $flag => $limit) {
        $usedSeconds = get_flag_used_time_today($flag,$user);
        $allFlags[] = [
            'type' => $flag,
            'timeLimit' => flag_has_time_limit($flag) ?
                partition_seconds(get_flag_time_limit_seconds($flag)) : $limit,
            'limitSeconds' => flag_has_time_limit($flag) ?
                get_flag_time_limit_seconds($flag) : $limit,
            'remainingSeconds' => flag_has_time_limit($flag) ?
                get_flag_time_limit_seconds($flag) - $usedSeconds : $limit,
            'remainingTime' => flag_has_time_limit($flag) ?
                partition_seconds(get_flag_time_limit_seconds($flag) - $usedSeconds) : $limit,
            'inUse' => flag_in_use($flag,$user)
        ];
    }
    return $allFlags;
}

function get_flag_used_time_today($type,$user)
{
    return Flag::where('user_id',$user->id)
        ->where('type',$type)
        ->where('day',now()->toDateString())
        ->sum('seconds');
}

function flag_in_use($type,$user)
{
    return Flag::where('user_id',$user->id)
        ->where('type',$type)
        ->where('day',now()->toDateString())
        ->where('stopped_at',null)
        ->get()->count() == 1;
}

function flag_has_time_limit($type)
{
    return gettype(app('settings')->getFlags()[$type]) != 'string';
}

function get_flag_time_limit_seconds($type)
{
    return app('settings')->getFlags()[$type];
}

function get_current_flag($user)
{
    return Flag::where('user_id',$user->id)
        ->where('day',now()->toDateString())
        ->where('stopped_at',null)->first();
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