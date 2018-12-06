<?php

namespace App\Http\Controllers;

use App\Task;
use Illuminate\Http\Request;

class TasksController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function search(Request $request)
    {
        if (!$request->q) {
            return response()->json([
                'tasks' => []
            ]);
        }

        $tasks = Task::where('content', 'like', $request->q . '%')
            ->take(10)->get();
        return response()->json([
            'tasks' => $tasks
        ]);
    }

    public function index(Request $request)
    {
        $request->validate(
            [
                'project' => 'required|integer|exists:projects,id'
            ]
        );

        return response()->json([
            'tasks' => Task::where('user_id', auth()->id())
                ->where('project_id', $request->project)->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:200',
            'project_id' => 'required|integer|exists:projects,id'
        ]);

        $task = new Task();
        $task->project_id = $request->project_id;
        $task->content = $request->get('content');
        $task->user_id = auth()->id();
        $task->save();

        return response()->json([
            'task' => $task
        ]);
    }
}
