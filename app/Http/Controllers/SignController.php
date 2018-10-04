<?php

namespace App\Http\Controllers;


use App\Notifications\WorkStart;
use App\Notifications\WorkStop;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $user=User::where('username','admin')->first();
        $user->notify(new WorkStart(Auth::user()));

        if(auth()->user()->isWorking()){
            abort(400);
        }

        $manager = new WorkTimesManager(auth()->user());

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
        $user=User::where('username','admin')->first();
        $user->notify(new WorkStop(Auth::user()));

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
