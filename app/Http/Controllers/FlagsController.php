<?php

namespace App\Http\Controllers;

use App\Jobs\StopUserAfterFlagExpires;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Utilities\WorKTime;
use App\Utilities\Flag;

class FlagsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function startFlag(Request $request):JsonResponse
    {
        //TODO refactor this action

        /** @var \App\User $user */
        $user = auth()->user();
        $id = $user->id;

        //validate flag type
        $v = Validator::make($request->only('type'),[
            'type' => 'required|string'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'invalid_type',
                'message' => 'type is required and must be string'
            ],422);
        }

        if(!Flag::exists($request->type)){
           return response()->json([
               'status' => 'invalid_flag',
               'message' => 'this flag is not valid'
           ],422);
        }

        //TODO: see if this check is necessary
        //fix if is using flag property is wrong
        Flag::fixUserFlag($user);

        //TODO: see if this check is necessary
        //fix if is user status is wrong
        WorKTime::fixStatus($user);

        //validate that user is not working or using flag
        if(!$user->isWorking() || $user->isUsingFlag()){
            return response()->json([
                'status' => 'is_not_working_or_using_flag',
                'message' => 'user is already working or using flag'
            ],422);
        }

        $type = $request->type;
        $flagDaySeconds = Flag::daySeconds($id,$type,now()->toDateString());
        $flagHasTimeLimit = Flag::hasTimeLimit($type);
        $flagTimeLimit = Flag::timeLimit($type);
        if($flagHasTimeLimit && $flagTimeLimit <= $flagDaySeconds){
            return response()->json([
                'status' => 'flag_passed_time_limit',
                'message' => 'user has used the limit of this flag'
            ],422);
        }
        if($flagHasTimeLimit){
            $seconds = $flagTimeLimit - $flagDaySeconds;
            StopUserAfterFlagExpires::dispatch($user->id)->delay(now()->addSeconds($seconds));
        }
        $workTimeId = WorKTime::active($id,now()->toDateString())->id;
        $flag = Flag::start($id,$type,$workTimeId);
        $flag->save();

        $user->flag = 'on';
        $user->save();


        return response()->json([
            'status' => 'success',
            'message' => 'user started using ' . $type . ' flag'
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function endFlag():JsonResponse
    {
        //TODO refactor this action

        /** @var \App\User $user */
        $user = auth()->user();
        $id = $user->id;

        //TODO: see if this check is necessary
        //fix if is using flag property is wrong
        Flag::fixUserFlag($user);

        //TODO: see if this check is necessary
        //fix if is user status is wrong
        WorKTime::fixStatus($user);

        //validate work and flag status
        if(!$user->isWorking() || !$user->isUsingFlag()){
            return response()->json([
                'status' => 'not_working_or_using_flag',
                'message' => 'user either not working or not using a flag'
            ],422);
        }

        $flag = Flag::current($id);
        $type = $flag->type;

        //if the flag started before today
        if(now()->toDateString() > $flag->day){
            $stop = (new Carbon($flag->day))->hour(23)->minute(59)->second(59);
            $flag = Flag::stop($flag,$stop);
            $workTime = $flag->workTime;
            $seconds = Flag::hasTimeLimit($type) && Flag::passedTimeLimit($id,$type,$flag->day,$stop) ?
                (new Carbon($flag->started_at))->diffInSeconds($workTime->started_work_at) + $flag->seconds :
                $stop->diffInSeconds($workTime->started_work_at) + 1;
            $workTime = WorKTime::stop($workTime,$stop,$seconds);
            $flag->save();
            $workTime->save();

            if(now()->diffInDays($flag->started_at) <= 1){
                $start = today();
                $secondWorkTime = WorKTime::start($id,$workTime->status,$start);
                $secondWorkTime->save();
            }

            $user->flag = 'off';
            $user->status = empty($secondWorkTime) ? 'off' : 'on';

            return response()->json([
                'status' => 'stopped_flag',
                'message' => 'user stopped using ' . $type . ' flag'
            ]);
        }

        $flag = Flag::stop($flag);
        $flag->save();
        $user->flag = 'off';
        $user->save();

        if(Flag::hasTimeLimit($flag->type) && Flag::passedTimeLimit($id,$type,$flag->day,$flag->stopped_at)){
            $workTime = $flag->workTime;
            $seconds = (new Carbon($flag->started_at))->diffInSeconds($workTime->started_work_at) + $flag->seconds;
            $workTime = WorKTime::stop($workTime,now(),$seconds);
            $workTime->save();

            $secondWorkTime = WorKTime::start($id,$workTime->status);
            $secondWorkTime->save();
        }


        return response()->json([
            'status' => 'success',
            'message' => 'user stopped using ' . $type  . ' flag'
        ]);

    }
}
