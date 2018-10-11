<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
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

        $id = auth()->user()->id;

        //validate flag type
        if(!isset(app('settings')->getFlags()[$request->type])){
           return response()->json([
               'status' => 'invalid_flag',
               'message' => 'this flag is not valid'
           ],422);
        }

        //TODO: see if this check is necessary
        //fix if is using flag property is wrong
        if(auth()->user()->isUsingFlag() && !Flag::where('user_id',$id)
                                                ->where('stopped_at',null)
                                                ->first()){
            auth()->user()->flag = 'off';
            auth()->user()->save();
        }elseif(!auth()->user()->isUsingFlag() && Flag::where('user_id',$id)
                                                    ->where('stopped_at',null)
                                                    ->first()){
            auth()->user()->flag = 'on';
            auth()->user()->save();
        }

        //TODO: see if this check is necessary
        //fix if is user status is wrong
        if(!auth()->user()->isWorking() && WorkTime::where('user_id',$id)
                                            ->where('stopped_work_at',null)->first()){
            auth()->user()->status = 'on';
            auth()->user()->save();
        }elseif(auth()->user()->isWorking() && !WorkTime::where('user_id',$id)
                                                 ->where('stopped_work_at',null)->first()){
            auth()->user()->status = 'off';
            auth()->user()->save();
        }

        //validate that user is not working or using flag
        if(!auth()->user()->isWorking() || auth()->user()->isUsingFlag()){
            return response()->json([
                'status' => 'is_not_working_or_using_flag',
                'message' => 'user is already working or using flag'
            ],422);
        }

        $type = $request->type;

        if(gettype(app('settings')->getFlags()[$type]) == 'integer' &&
            app('settings')->getFlags()[$type] <= Flag::where('user_id',$id)
                                                            ->where('day',now()->toDateString())
                                                            ->where('type',$type)->sum('seconds')){
            return response()->json([
                'status' => 'flag_passed_time_limit',
                'message' => 'user has used the limit of this flag'
            ],422);
        }


        $flag = new Flag();
        $flag->user_id = $id;
        $flag->work_time_id = WorkTime::where('day',now()->toDateString())
            ->where('user_id',$id)
            ->where('stopped_work_at',null)->first()->id;
        $flag->started_at = now();
        $flag->day = now()->toDateString();
        $flag->type = $type;
        $flag->save();

        auth()->user()->flag = 'on';
        auth()->user()->save();


        return response()->json([
            'status' => 'flag_started',
            'message' => 'user started using ' . $type . ' flag'
        ]);
    }

    public function endFlag()
    {
        //TODO refactor this action

        $id = auth()->user()->id;

        //TODO: see if this check is necessary
        //fix if is using flag property is wrong
        if(auth()->user()->isUsingFlag() && !Flag::where('user_id',$id)
                                                ->where('stopped_at',null)
                                                ->first()){
            auth()->user()->flag = 'off';
            auth()->user()->save();
        }elseif(!auth()->user()->isUsingFlag() && Flag::where('user_id',$id)
                                                    ->where('stopped_at',null)
                                                    ->first()){
            auth()->user()->flag = 'on';
            auth()->user()->save();
        }

        //TODO: see if this check is necessary
        //fix if is user status is wrong
        if(!auth()->user()->isWorking() && WorkTime::where('user_id',$id)
                                            ->where('stopped_work_at',null)->first()){
            auth()->user()->status = 'on';
            auth()->user()->save();
        }elseif(auth()->user()->isWorking() && !WorkTime::where('user_id',$id)
                                                 ->where('stopped_work_at',null)->first()){
            auth()->user()->status = 'off';
            auth()->user()->save();
        }

        //validate work and flag status
        if(!auth()->user()->isWorking() || !auth()->user()->isUsingFlag()){
            return response()->json([
                'status' => 'not_working_or_using_flag',
                'message' => 'user either not working or not using a flag'
            ],422);
        }

        $flag = Flag::where('user_id',$id)
            ->where('stopped_at',null)
            ->where('seconds',0)
            ->first();
        $type = $flag->type;

        //if the flag started before today
        if(now()->toDateString() > $flag->day){
            $flag->stopped_at = (new Carbon($flag->started_at))->hour(23)->minute(59)->second(59);
            $flagSeconds = $flag->stopped_at->diffInSeconds($flag->started_at) + 1;
            $flagUsedSeconds = Flag::where('user_id',$id)
                ->where('type',$type)
                ->where('day',$flag->day)
                ->sum('seconds');
            $todayFlagSeconds = $flagSeconds + $flagUsedSeconds;

            $workTime = $flag->workTime;
            $workTime->stopped_work_at = (new Carbon($workTime->started_work_at))->hour(23)->minute(59)->second(59);

            if(gettype(app('settings')->getFals()[$type]) != 'integer'){
                $flag->seconds = $flagSeconds;
                $workTime->seconds = $workTime->stopped_work_at->diffInSeconds($workTime->started_work_at) + 1;
            }elseif($todayFlagSeconds > app('settings')->getFlags()[$type]){
                $flag->seconds = app('settings')->getFlags()[$type] - $flagUsedSeconds;
                $workTime->seconds = (new Carbon($workTime->started_work_at))->diffInSeconds($flag->started_at) + $flag->sconds;
            }else{
                $flag->seconds = $flagSeconds;
                $workTime->secnds = $workTime->stopped_work_at->diffInSeconds($workTime->started_work_at) + 1;
            }
            $flag->save();

            $workTime->day_seconds += $workTime->seconds;
            $workTime->save();

            if(now()->diffInDays($flag->started_at) <= 1){
                $secondWorkTime = new WorkTime();
                $secondWorkTime->user_id = $id;
                $secondWorkTime->status = $workTime->status;
                $secondWorkTime->day = now()->toDateString();
                $secondWorkTime->started_work_at = now()->hour(0)->minute(0)->second(0);
                $secondWorkTime->save();
            }

            auth()->user()->flag = 'off';
            auth()->user()->status = empty($secondWorkTime) ? 'off' : 'on';

            return response()->json([
                'status' => 'stopped_flag',
                'message' => 'user stopped using ' . $type . ' flag'
            ]);
        }

        $flag->stopped_at = now();
        $flagSeconds = $flag->stopped_at->diffInSeconds($flag->started_at);
        $flagUsedSeconds = Flag::where('user_id',$id)
            ->where('type',$type)
            ->where('day',$flag->day)
            ->sum('seconds');
        $todayFlagSeconds = $flagSeconds + $flagUsedSeconds;

        if(gettype(app('settings')->getFlags()[$type]) != 'integer'){
            $flag->seconds = $flagSeconds;
        }elseif($todayFlagSeconds > app('settings')->getFlags()[$type]){
            $flag->seconds = app('settings')->getFlags()[$type] - $flagUsedSeconds;

            $workTime = $flag->workTime;
            $workTime->stopped_work_at = now();
            $workTime->seconds = (new Carbon($workTime->started_work_at))->diffInSeconds($flag->started_at) + $flag->seconds;
            $workTime->day_seconds += $workTime->seconds;
            $workTime->save();

            $secondWorkTime = new WorkTime();
            $secondWorkTime->user_id = $id;
            $secondWorkTime->status = $workTime->status;
            $secondWorkTime->day = now()->toDateString();
            $secondWorkTime->started_work_at = now();
            $secondWorkTime->save();
        }else{
            $flag->seconds = $flagSeconds;
        }

        $flag->save();

        auth()->user()->flag = 'off';
        auth()->user()->save();

        return response()->json([
            'status' => 'flag_stopped',
            'message' => 'user stopped using ' . $type  . ' flag'
        ]);

    }
}
