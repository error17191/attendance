<?php

namespace App\Http\Controllers;


use App\Notifications\WorkStart;
use App\Notifications\WorkStop;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Utilities\WorKTime;
use App\Utilities\Flag;
use Illuminate\Http\JsonResponse;

class SignController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','is_tracked']);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function startWork(Request $request):JsonResponse
    {
        //TODO: refactor this action


        /** @var \App\User $user */
        $user = auth()->user();
        $id = $user->id;

        //TODO: see if this check is necessary
        //check if the status is wrong
        WorKTime::fixStatus($user);

        //reject the request to start a new work time when the user is already in a work time
        if($user->isWorking()){
            return response()->json([
                'status' => 'already_working',
                'message' => 'User is already working'
            ],422);
        }

        //validate request data
        $v = Validator::make($request->only('task', 'project_id'),[
            'task' => 'required',
            'task.content' => 'required|string',
            'task.id' => 'exists:tasks,id',
            'project_id' => 'required|exists:projects,id'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'validation_errors',
                'errors' => $v->errors()->toArray()
            ],422);
        }

        //start new work time
        $workTime = WorKTime::start($id,$request->task,$request->project_id);
        $workTime->save();

        $user->status = 'on';
        $user->save();

        return response()->json([
            'workTimeSign' => WorKTime::sign($workTime),
            'today_time' => [
                'seconds' => $workTime->day_seconds,
                'partitions' => partition_seconds($workTime->day_seconds)
            ]
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function stopWork():JsonResponse
    {
        //TODO: refactor this action

//        $admin =User::where('username','admin')->first();
//        $admin->notify(new WorkStop(Auth::user()));

        /** @var \App\User $user */
        $user = auth()->user();
        $id = $user->id;

        //TODO: see if this check is necessary
        //check if the status is wrong
        WorKTime::fixStatus($user);

        //validate if user is working
        if(!$user->isWorking()){
            return response()->json([
                'status' => 'not_working',
                'message' => 'user is not working'
            ],422);
        }

        if(WorKTime::noActive($id,now()->toDateString())){
            $workTime = WorKTime::active($id,now()->toDateString(),true);
            $stop = (new Carbon($workTime->day))->hour(23)->minute(59)->second(59);

            //handle flags
            if($user->isUsingFlag()){
                $flag = Flag::current($id);
                $flag = Flag::stop($flag,$stop);
                if(Flag::hasTimeLimit($flag->type) && Flag::passedTimeLimit($id,$flag->type,$flag->day,$stop)){
                    $seconds = (new Carbon($flag->started_at))->diffInSeconds($workTime->started_at) + $flag->seconds;
                }
                $flag->save();
            }
            if(empty($seconds)){
                $seconds = 0;
            }
            $workTime = WorKTime::stop($workTime,$stop,$seconds);
            $workTime->save();

            if(now()->diffInDays($workTime->started_work_at) <= 1){
                $secondWorkTime = WorKTime::start($id,$workTime->status,today());
                $secondWorkTime = WorKTime::stop($secondWorkTime);
                $secondWorkTime->save();
            }

            $user->flag = 'off';
            $user->status = 'off';
            $user->save();

            return response()->json([
                'workTimeSign' => !empty($secondWorkTime) ?
                    WorKTime::sign($secondWorkTime) : WorKTime::sign($workTime),
                'today_time' => [
                    'seconds' => !empty($secondWorkTime) ?
                        $secondWorkTime->day_seconds : $workTime->day_seconds,
                    'partitions' => !empty($secondWorkTime) ?
                        partition_seconds($secondWorkTime->day_seconds) :
                        partition_seconds($workTime->day_seconds)
                ]
            ]);
        }

        $workTime = WorKTime::active($id,now()->toDateString());

        if($user->isUsingFlag()){
           $flag = Flag::current($id);
           $flag = Flag::stop($flag);
            if(Flag::hasTimeLimit($flag->type) && Flag::passedTimeLimit($id,$flag->type,$flag->day,now())){
                $seconds = (new Carbon($flag->started_at))->diffInSeconds($workTime->started_at) + $flag->seconds;
            }
            $flag->save();
        }
        if(empty($seconds)){
            $seconds = 0;
        }
        $workTime = WorKTime::stop($workTime,now(),$seconds);
        $workTime->save();

        $user->flag = 'off';
        $user->status = 'off';
        $user->save();


        return response()->json([
            'workTimeSign' => WorKTime::sign($workTime),
            'today_time' => [
                'seconds' => $workTime->day_seconds,
                'partitions' => partition_seconds($workTime->day_seconds)
            ]
        ]);
    }

}
