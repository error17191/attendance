<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Flag;
use App\WorkTime;

class FlagsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function startFlag(Request $request)
    {
        //TODO refactor this action
        if(!isset(app('settings')->getFlags()[$request->type])){
            abort(400,'this falg is not available');
        }
        if(!auth()->user()->isWorking() || auth()->user()->isUsingFlag()){
            abort(400,'you are not signed or using another flag');
        }

        if(flag_has_time_limit($request->type) &&
            get_flag_used_time_today($request->type,auth()->user()) >= get_flag_time_limit_seconds($request->type)){
            abort(400,'you reached the time limit of the flag');
        }
        $flag = new Flag();
        $flag->user_id = auth()->user()->id;
        $flag->work_time_id = WorkTime::where('day',now()->toDateString())
            ->where('user_id',auth()->user()->id)
            ->where('stopped_work_at',null)->first()->id;
        $flag->started_at = now();
        $flag->day = now()->toDateString();
        $flag->type = $request->type;
        $flag->save();
        $user = auth()->user();
        $user->flag = 'on';
        $user->save();
        return response()->json([
            'message' => 'you started using ' . $request->type . ' flag'
        ]);
    }

    public function endFlag()
    {
        //TODO refactor this action


        if(!auth()->user()->isWorking() || !auth()->user()->isUsingFlag()){
            abort(400,'you are not signed or not using any flag');
        }

        end_flag(auth()->user());
    }
}
