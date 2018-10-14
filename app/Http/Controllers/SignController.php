<?php

namespace App\Http\Controllers;


use App\Flag;
use App\Notifications\WorkStart;
use App\Notifications\WorkStop;
use App\User;
use App\WorkTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Utilities\WorKTimeUtility;

class SignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function startWork(Request $request)
    {
        //TODO: refactor this action

        $admin = User::where('username','admin')->first();
        $admin->notify(new WorkStart(Auth::user()));

        /** @var \App\User $user */
        $user = auth()->user();
        $id = $user->id;

        //TODO: see if this check is necessary
        //check if the status is wrong
        WorKTimeUtility::fixStatus($user);

        //reject the request to start a new work time when the user is already in a work time
        if($user->isWorking()){
            return response()->json([
                'status' => 'already_working',
                'message' => 'User is already working'
            ],422);
        }

        //validate request data
        $v = Validator::make($request->only('workStatus'),[
            'workStatus' => 'required|string'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'work_status_required',
                'message' => 'Work status is required'
            ],422);
        }

        //start new work time
        $workTime = WorKTimeUtility::startWorkTime($id,$request->workStatus);
        $workTime->save();

        $user->status = 'on';
        $user->save();

        return response()->json([
            'workTimeSign' => [
                'started_at' => (new Carbon($workTime->started_work_at))->toTimeString(),
                'stopped_at' => null,
                'status' => $workTime->status
            ],
            'today_time' => [
                'seconds' => $workTime->day_seconds,
                'partitions' => partition_seconds($workTime->day_seconds)
            ]
        ]);
    }

    public function stopWork()
    {
        //TODO: refactor this action

        $admin =User::where('username','admin')->first();
        $admin->notify(new WorkStop(Auth::user()));

        /** @var \App\User $user */
        $user = auth()->user();
        $id = $user->id;

        //TODO: see if this check is necessary
        //check if the status is wrong
        WorKTimeUtility::fixStatus($user);

        //validate if user is working
        if(!$user->isWorking()){
            return response()->json([
                'status' => 'not_working',
                'message' => 'user is not working'
            ],422);
        }

        if(WorKTimeUtility::noActiveWorkTime($id,now()->toDateString())){
            $workTime = WorKTimeUtility::activeWorkTime($id,now()->toDateString(),true);
            $workTime->stopped_work_at = (new Carbon($workTime->started_work_at))->hour(23)->minute(59)->second(59);

            //handle flags
            if($user->isUsingFlag()){
                $flag = Flag::where('work_time_id',$workTime->id)
                    ->where('user_id',$id)
                    ->where('stopped_at',null)
                    ->where('seconds',0)->first();
                $flag->stopped_at = (new Carbon($flag->day))->hour(23)->minute(59)->second(59);

                if($flag && gettype(app('settings')->getFlags()[$flag->type]) != 'integer'){
                    $flag->seconds = $flag->stopped_at->diffInSeconds($flag->started_at) + 1;
                    $flag->save();
                    $workTime->seconds = (new Carbon($workTime->stopped_work_at))->diffInSeconds($workTime->started_work_at) + 1;
                }elseif($flag && gettype(app('settings')->getFlags()[$flag->type]) == 'integer'){
                    $flagSeconds = $flag->stopped_at->diffInSeconds($flag->started_at) + 1;
                    $flagUsedSeconds = Flag::where('user_id',$id)->where('day',$flag->day)->sum('seconds');
                    $todayFlagSeconds = $flagUsedSeconds + $flagSeconds;
                    if($todayFlagSeconds > app('settings')->getFalgs()[$flag->type]){
                        $flag->seconds = app('settings')->getFlags()[$flag->type] - $flagUsedSeconds;
                        $workTime->seconds = (new Carbon($workTime->started_work_at))->diffInSeconds($flag->started_at) + $flag->sconds;
                    }else {
                        $workTime->seconds = (new Carbon($workTime->stopped_work_at))->diffInSeconds($workTime->started_work_at) + 1;
                        $flag->seconds = (new Carbon($flag->stopped_at))->diffInSeconds($flag->started_at) + 1;
                    }
                    $flag->save();
                }
            }

            if(empty($workTime->seconds)){
                $workTime->seconds = (new Carbon($workTime->stopped_work_at))->diffInSeconds($workTime->started_work_at) + 1;
            }

            //save first part from work
            $workTime->day_seconds += $workTime->seconds;
            $workTime->save();

            if(now()->diffInDays($workTime->started_work_at) <= 1){
                $secondWorkTime = new WorkTime();
                $secondWorkTime->user_id = $id;
                $secondWorkTime->status = $workTime->status;
                $secondWorkTime->day = now()->toDateString();
                $secondWorkTime->started_work_at = now()->hour(0)->minute(0)->second(0);
                $secondWorkTime->stopped_work_at = now();
                $secondWorkTime->seconds = $secondWorkTime->stopped_work_at->diffInSeconds($secondWorkTime->started_work_at);
                $secondWorkTime->day_seconds = $secondWorkTime->seconds;
                $secondWorkTime->save();
            }

            $user->flag = 'off';
            $user->status = 'off';
            $user->save();

            return response()->json([
                'workTimeSign' => [
                    'started_at' => !empty($secondWorkTime) ?
                        (new Carbon($secondWorkTime->started_work_at))->toTimeString() :
                        (new Carbon($workTime->started_work_at))->toTimeString(),
                    'stopped_at' => !empty($secondWorkTime) ?
                        (new Carbon($secondWorkTime->stopped_work_at))->toTimeString() :
                        (new Carbon($workTime->stopped_work_at))->toTimeString(),
                    'status' => !empty($secondWorkTime) ?
                        $secondWorkTime->status : $workTime->status
                ],
                'today_time' => [
                    'seconds' => !empty($secondWorkTime) ?
                        $secondWorkTime->day_seconds : $workTime->day_seconds,
                    'partitions' => !empty($secondWorkTime) ?
                        partition_seconds($secondWorkTime->day_seconds) :
                        partition_seconds($workTime->day_seconds)
                ]
            ]);
        }

        $workTime = WorkTime::where('user_id',$id)
            ->where('day',now()->toDateString())
            ->where('stopped_work_at',null)
            ->where('seconds',0)->first();

        $workTime->stopped_work_at = now();

        if(auth()->user()->isUsingFlag()){
            $flag = Flag::where('work_time_id',$workTime->id)
                ->where('user_id',$id)
                ->where('stopped_at',null)
                ->where('seconds',0)->first();
            if($flag && gettype(app('settings')->getFlags()[$flag->type]) != 'integer'){
                $flag->stopped_at = now();
                $flag->seconds = $flag->stopped_at->diffInSeconds($flag->started_at);
                $flag->save();
            }elseif($flag && gettype(app('settings')->getFlags()[$flag->type]) == 'integer'){
                $flag->stopped_at = now();
                $flagSeconds = $flag->stopped_at->diffInSeconds($flag->started_at);
                $flagUsedSeconds = Flag::where('user_id',$id)->where('day',$flag->day)->sum('seconds');
                $todayFlagSeconds = $flagUsedSeconds + $flagSeconds;
                if($todayFlagSeconds > app('settings')->getFlags()[$flag->type]){
                    $flag->seconds = app('settings')->getFlags()[$flag->type] - $flagUsedSeconds;
                    $workTime->seconds = (new Carbon($workTime->started_work_at))->diffInSeconds($flag->started_at) + $flag->seconds;
                }else{
                    $flag->seconds = (new Carbon($flag->stopped_at))->diffInSeconds($flag->started_at);
                }
                $flag->save();
            }
        }

        if(empty($workTime->seconds)){
            $workTime->seconds = $workTime->stopped_work_at->diffInSeconds($workTime->started_work_at);
        }

        $workTime->day_seconds += $workTime->seconds;
        $workTime->save();

        $user->flag = 'off';
        $user->status = 'off';
        $user->save();


        return response()->json([
            'workTimeSign' => [
                'started_at' => (new Carbon($workTime->started_work_at))->toTimeString(),
                'stopped_at' => (new Carbon($workTime->stopped_work_at))->toTimeString(),
                'status' => $workTime->status
            ],
            'today_time' => [
                'seconds' => $workTime->day_seconds,
                'partitions' => partition_seconds($workTime->day_seconds)
            ]
        ]);
    }

}
