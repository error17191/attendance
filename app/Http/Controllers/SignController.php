<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Validator;

class SignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function startWork(Request $request)
    {
        if (auth()->user()->isWorking()) {
            abort(400);
        }

        $v = Validator::make($request->only('workStatus'),[
            'workStatus' => 'required|string'
        ]);

        if($v->fails()){
            abort(400);
        }

        $result = auth()->user()->startWork($request->workStatus);

        return response()->json([
            'sign' => $result['sign'],
            'today_time' => [
                'seconds' => $result['workTime']->seconds,
                'partitions' => $result['workTime']->partitionSeconds()
            ]
        ]);
    }

    public function stopWork()
    {
        if (auth()->user()->isStopped()) {
            abort(400);
        }

        $result = auth()->user()->stopWork();

        return response()->json([
            'sign' => $result['sign'],
            'today_time' => [
                'seconds' => $result['workTime']->seconds,
                'partitions' => $result['workTime']->partitionSeconds()
            ]
        ]);
    }

}
