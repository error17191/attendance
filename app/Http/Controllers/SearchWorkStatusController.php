<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\WorkTime;

class SearchWorkStatusController extends Controller
{
    public function index(Request $request)
    {
        if(!auth()->user()){
            abort(400);
        }
        if(!$request->q){
            return response()->json([
                'status' => []
            ]);
        }
        $status = WorkTime::where('user_id',auth()->user()->id)
            ->where('status','like',$request->q .'%')
            ->take(10)->get();
        return response()->json([
            'status' => $status
        ]);
    }
}
