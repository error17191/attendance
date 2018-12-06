<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Utilities\WorKTime;
use Illuminate\Http\JsonResponse;

class StartWorkController extends Controller
{


    public function __construct()
    {
        $this->middleware(['auth']);
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
//        WorKTime::fixStatus($user);

        //reject the request to start a new work time when the user is already in a work time
        if($user->isWorking()){
            return response()->json([
                'status' => 'already_working',
                'message' => 'User is already working'
            ],422);
        }

        //validate request data
        $v = Validator::make($request->only('task_id', 'project_id'),[
            'task_id' => 'required|exists:tasks,id',
            'project_id' => 'required|exists:projects,id'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'validation_errors',
                'errors' => $v->errors()->toArray()
            ],422);
        }
        //start new work time
        $workTime = WorKTime::start($id, $request->task_id,$request->project_id);

        return response()->json([
            'success' => $workTime->save()
        ]);
    }
}
