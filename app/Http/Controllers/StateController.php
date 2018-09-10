<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class StateController extends Controller
{
    public function init()
    {
        $todayTime = auth()->user()->todayTime();

        if(today()->isWeekend()){

        }

        return response()->json([
            'signs' => auth()->user()->todaySigns,
            'status' => auth()->user()->status,
            'today_time' => [
                'seconds' => $todayTime ? $todayTime->secondsTillNow() : 0,
                'partitions' => $todayTime ?  $todayTime->partitionSecondsTillNow(): ['hours' => 0, 'minutes' => 0, 'seconds' => 0]
            ],
            'month_report' => [

            ]
        ]);
    }
}
