<?php

namespace App\Http\Controllers;

use App\WorkTime;
use Validator;
use App\Utilities\WorKTime as WTU;
use Illuminate\Http\JsonResponse;

class StopWorkController extends Controller
{
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
//        WTU::fixStatus($user);

        //validate if user is working
        if(!$user->isWorking()){
            return response()->json([
                'status' => 'not_working',
                'message' => 'user is not working'
            ],422);
        }

        if($user->isUsingFlag()){
            return response()->json([
                'status' => 'using_flag',
                'message' => 'user is using flag'
            ],422);
        }

//        if(WTU::noActive($id,now()->toDateString())){
//            $workTime = WTU::active($id,now()->toDateString(),true);
//            $stop = (new Carbon($workTime->day))->hour(23)->minute(59)->second(59);
//
//            //handle flags
//            if($user->isUsingFlag()){
//                $flag = Flag::current($id);
//                $flag = Flag::stop($flag,$stop);
//                if(Flag::hasTimeLimit($flag->type) && Flag::passedTimeLimit($id,$flag->type,$flag->day,$stop)){
//                    $seconds = (new Carbon($flag->started_at))->diffInSeconds($workTime->started_at) + $flag->seconds;
//                }
//                $flag->save();
//            }
//            if(empty($seconds)){
//                $seconds = 0;
//            }
//            $workTime = WTU::stop($workTime,$stop,$seconds);
//            $workTime->save();
//
//            if(now()->diffInDays($workTime->started_work_at) <= 1){
//                $secondWorkTime = WTU::start($id,$workTime->task , $workTime->task->project_id ,today());
//                $secondWorkTime = WTU::stop($secondWorkTime);
//                $secondWorkTime->save();
//            }
//
//            $user->flag = 'off';
//            $user->status = 'off';
//            $user->save();
//
//            return response()->json([
//                'workTimeSign' => !empty($secondWorkTime) ?
//                    WTU::sign($secondWorkTime) : WTU::sign($workTime),
//                'today_time' => [
//                    'seconds' => ($daySeconds = (!empty($secondWorkTime) ?
//                        WTU::daySeconds($id,$secondWorkTime->day)
//                        : WTU::daySeconds($id,$workTime->day ) ) ) ,
//                    'partitions' => !empty($secondWorkTime) ?
//                        partition_seconds($daySeconds) :
//                        partition_seconds($daySeconds)
//                ]
//            ]);
//        }

        $workTime = WorkTime::openForUser();
        $stoppingTime = now();
        if(! now()->isSameDay($workTime->started_work_at)){
            $stoppingTime = $workTime->started_work_at->endOfDay();
        }
        $workTime = WTU::stop($workTime,$stoppingTime,0);

        return response()->json([
            'success' => $workTime->save(),
        ]);
    }
}
