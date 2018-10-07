<?php

namespace App\Http\Controllers;

use App\WorkTime;
use App\Managers\FlagManager;
use Carbon\Carbon;
use App\Managers\WorkTimesManager;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function init()
    {
        $manager = new WorkTimesManager(auth()->user());
        if($manager->startedWorkingToday()){
            $todayWorkSeconds = $manager->daySecondsTillNow();
            $todayWorkTimePartitions = partition_seconds($todayWorkSeconds);
        }else{
            $todayWorkSeconds = 0;
            $todayWorkTimePartitions = ['hours' => 0, 'minutes' => 0, 'seconds' => 0];
        }
        $firstOfMonth = today()->firstOfMonth();
        $daysPassed = today()->diffInDays($firstOfMonth);// Days passed of current month
        $workHoursIdeal = 0;
        for ($i = 0; $i <= $daysPassed; $i++) {
            $day = (new Carbon($firstOfMonth))->addDays($i);
            if (!$day->isWeekend()) {
                $workHoursIdeal += app('settings')->getRegularTime()['regularHours'];
            }
        }
        $workSecondsIdeal = 60 * 60 * $workHoursIdeal;
        $workSecondsActual = WorkTime::whereBetween('day', [today()->firstOfMonth(), today()->subDay()])->sum('seconds');
        $workSecondsActual += $todayWorkSeconds;
        if ($workSecondsActual > $workSecondsIdeal) {
            $diffType = 'more';
        } else {
            $diffType = 'less';
        }
        $diffSeconds = abs($workSecondsIdeal - $workSecondsActual);
        return response()->json([
            'flags' => get_all_flags(auth()->user()),
            'workTimeSigns' => $manager->todayWorkTimeSigns(),
            'status' => auth()->user()->status,
            'today_time' => [
                'seconds' => $todayWorkSeconds,
                'partitions' => $todayWorkTimePartitions,
                'workStatus' => $manager->lastWorkStatus()
            ],
            'month_report' => [
                'actual' => [
                    'seconds' => $workSecondsActual,
                    'partitions' => partition_seconds($workSecondsActual)
                ],
                'ideal' => [
                    'seconds' => $workSecondsIdeal,
                    'partitions' => partition_seconds($workSecondsIdeal)
                ],
                'diff' => [
                    'type' => $diffType,
                    'seconds' => $diffSeconds,
                    'partitions' => partition_seconds($diffSeconds)
                ]
            ]
        ]);
    }
}
