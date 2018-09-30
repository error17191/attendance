<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Validator;
use App\Managers\WorkTimesManager;

class SignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function startWork(Request $request)
    {
        $manager = new WorkTimesManager(auth()->user());
        if(auth()->user()->isWorking()){
            abort(400);
        }
        $v = Validator::make($request->only('workStatus'),[
            'workStatus' => 'required|string'
        ]);
        if($v->fails()){
            abort(400);
        }
        $result = $manager->startWorkTime($request->workStatus);
        return response()->json([
            'workTimeSign' => $result['workTimeSign'],
            'today_time' => [
                'seconds' => $result['workTime']->day_seconds,
                'partitions' => partition_seconds($result['workTime']->day_seconds)
            ]
        ]);
    }

    public function stopWork()
    {
        $manager = new WorkTimesManager(auth()->user());

        if(!auth()->user()->isWorking()){
            abort(400);
        }
        $result = $manager->endWorkTime();
        return response()->json([
            'workTimeSign' => $result['workTimeSign'],
            'today_time' => [
                'seconds' => $result['workTime']->day_seconds,
                'partitions' => partition_seconds($result['workTime']->day_seconds)
            ]
        ]);
    }

}
