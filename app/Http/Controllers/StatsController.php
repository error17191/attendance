<?php

namespace App\Http\Controllers;

use App\WorkTime;
use Carbon\Carbon;
use App\Managers\DayWorkTimes;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function init()
    {
        $dayWorkTimeManager = new DayWorkTimes(auth()->user());
        // Today numbers
//        $todayWorkTime = auth()->user()->todayTime();
//        if ($todayWorkTime) {
//            $todayWorkSeconds = $todayWorkTime->secondsTillNow();
//            $todayWorkTimePartitions = $todayWorkTime->partitionSecondsTillNow();
//        } else {
//            $todayWorkSeconds = 0;
//            $todayWorkTimePartitions = ['hours' => 0, 'minutes' => 0, 'seconds' => 0];
//        }

        if($dayWorkTimeManager->startedWorkingToday()){
            $todayWorkSeconds = $dayWorkTimeManager->daySecondsTillNow();
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
                $workHoursIdeal += 8;
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
            'signs' => auth()->user()->todaySigns,
            'status' => auth()->user()->status,
            'today_time' => [
                'seconds' => $todayWorkSeconds,
                'partitions' => $todayWorkTimePartitions,
                'workStatus' => $dayWorkTimeManager->lastWorkStatus()
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
