<?php

namespace App\Http\Controllers;


class SignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function startWork()
    {
        if (auth()->user()->isWorking()) {
            abort(400);
        }

        $result = auth()->user()->startWork();

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
