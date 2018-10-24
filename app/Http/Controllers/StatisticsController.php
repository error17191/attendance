<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Utilities\Statistics;
use Validator;

class StatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function monthReportAdmin(Request $request):JsonResponse
    {
        $v = Validator::make($request->only(['month','year','user_id']),[
            'month' => 'required|integer|min:1|max:12',
            'year' => 'nullable|integer',
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'invalid_month',
                'message' => 'month is required and must be an integer between 1 and 12'
            ],422);
        }

        $v = Validator::make($request->only('userId'),[
            'userId' => 'integer|min:1|exists:users,id'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'user_id_invalid',
                'message' => 'user does not exist'
            ],422);
        }

        $id = $request->userId;
        $month = $request->month;
        $year = $request->year ?: now()->year;

        return response()->json([
            'monthStatistics' => Statistics::monthReport($id,$month,$year)
        ]);
    }

    public function monthReportUser(Request $request):JsonResponse
    {
        $v = Validator::make($request->only(['month','year','user_id']),[
            'month' => 'required|integer|min:1|max:12',
            'year' => 'nullable|integer',
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'invalid_month',
                'message' => 'month is required and must be an integer between 1 and 12'
            ],422);
        }

        $id = auth()->user()->id;
        $month = $request->month;
        $year = $request->year ?: now()->year;

        return response()->json([
            'monthStatistics' => Statistics::monthReport($id,$month,$year)
        ]);
    }
}
