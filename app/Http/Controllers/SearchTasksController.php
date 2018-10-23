<?php

namespace App\Http\Controllers;

use App\Task;
use Illuminate\Http\Request;

class SearchTasksController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if(!$request->q){
            return response()->json([
                'status' => []
            ]);
        }

        $tasks = Task::where('content','like',$request->q .'%')
            ->take(10)->get();
        return response()->json([
            'tasks' => $tasks
        ]);
    }
}
