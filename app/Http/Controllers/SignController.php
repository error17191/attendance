<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Validator;
use App\Managers\DayWorkTimes;

class SignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function startWork(Request $request)
    {
//        if (auth()->user()->isWorking()) {
//            abort(400);
//        }
        $manager = new DayWorkTimes(auth()->user());

        if($manager->isWorking()){
            abort(400);
        }

        $v = Validator::make($request->only('workStatus'),[
            'workStatus' => 'required|string'
        ]);

        if($v->fails()){
            abort(400);
        }

//        $result = auth()->user()->startWork($request->workStatus);

        $result = $manager->startWorkTime($request->workStatus);

//        return response()->json([
//            'sign' => $result['sign'],
//            'today_time' => [
//                'seconds' => $result['workTime']->seconds,
//                'partitions' => $result['workTime']->partitionSeconds()
//            ]
//        ]);

        return response()->json([
            'sign' => $result['sign'],
            'today_time' => [
                'seconds' => $result['workTime']->day_seconds,
                'partitions' => partition_seconds($result['workTime']->day_seconds)
            ]
        ]);
    }

    public function stopWork()
    {
//        if (auth()->user()->isStopped()) {
//            abort(400);
//        }
        $manager = new DayWorkTimes(auth()->user());

        if(!$manager->isWorking()){
            abort(400);
        }
//        $result = auth()->user()->stopWork();

        $result = $manager->endWorkTime();

//        return response()->json([
//            'sign' => $result['sign'],
//            'today_time' => [
//                'seconds' => $result['workTime']->seconds,
//                'partitions' => $result['workTime']->partitionSeconds()
//            ]
//        ]);

        return response()->json([
            'sign' => $result['sign'],
            'today_time' => [
                'seconds' => $result['workTime']->day_seconds,
                'partitions' => partition_seconds($result['workTime']->day_seconds)
            ]
        ]);
    }

}
