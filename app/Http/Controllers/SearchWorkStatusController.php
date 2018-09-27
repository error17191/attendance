<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\WorkTime;

class SearchWorkStatusController extends Controller
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

        $status = WorkTime::where('user_id',auth()->user()->id)
            ->where('status','like',$request->q .'%')
            ->take(10)->select('status')->get()->pluck('status');
        return response()->json([
            'status' => $status
        ]);
    }
}
