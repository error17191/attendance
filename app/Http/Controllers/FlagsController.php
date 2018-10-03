<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Managers\FlagManager;
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
//        $manager = new FlagManager(auth()->user());
        if(!isset(app('settings')->getFlags()[$request->type])){
            abort(400);
        }
        if(!auth()->user()->isWorking() || auth()->user()->isUsingFlag()){
            abort(400);
        }
//        $message = $manager->startFlag($request->type);
//        return response()->json([
//            'message' => $message
//        ]);
        if(Flag::where('day',now()->toDateString())
            ->where('user_id',auth()->user()->id)
            ->where('type',$request->type)->sum('seconds')
            >=
            app('settings')->getFlags()[$request->type] * 60 * 60
            && app('settings')->getFlags()[$request->type] != 'no time limit'){
            abort(400);
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
//        $manager = new FlagManager(auth()->user());
//        $message = $manager->endFlag();
//        return response()->json([
//            'message' => $message
//        ]);

        if(!auth()->user()->isWorking() || !auth()->user()->isUsingFlag()){
            abort(400);
        }
//        if(($flagSeconds = Flag::where('day',now()->toDateString())
//                ->where('user_id',auth()->user()->id)
//                ->where('stopped_at',$request->type)->sum('seconds'))
//            >
//            app('settings')->getFlags()[$request->type] * 60 * 60){
//            $workTime = WorkTime::where('day',now()->toDateString())
//                ->where('user_id',auth()->user()->id)
//                ->where('stopped_work_at',null)->first();
//            $workTime->stopped_work_at = now();
//            $workTime->seconds = now()->diffInSeconds($workTime->started_work_at) -
//                $flagSeconds - app('settings')->getFlags()[$request->type];
//            $workTime->day_seconds += $workTime->seconds;
//            $workTime->save();
//            $flag = Flag::where('user_id',auth()->user()->id)
//                ->where('day',now()->toDateString())
//                ->where('type',$request->type)
//                ->where('stopped_at',null)->first();
//            $flag->stopped_at = now();
//            $flag->seconds = now()->diffInSeconds($flag->started_at) -
//                $flagSeconds - app('settings')->getFlags()[$request->type];
//            $flag->save();
//            $user = auth()->user();
//            $user->status = 'off';
//            $user->flag = 'off';
//            $user->save();
//            return;
//        }
//        $flag = Flag::where('user_id',auth()->user()->id)
//            ->where('day',now()->toDateString())
//            ->where('type',$request->type)
//            ->where('stopped_at',null)->first();
//        $flag->stopped_at = now();
//        $flag->seconds = now()->diffInSeconds($flag->started_at);
//        $flag->save();
//        $user = auth()->user();
//        $user->flag = 'off';
//        $user->save();
        end_flag(auth()->user());
    }
}
