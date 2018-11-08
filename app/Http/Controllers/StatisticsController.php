<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Utilities\Statistics;
use Illuminate\Support\Facades\Validator;

class StatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function monthReportAdmin(Request $request):JsonResponse
    {
        $v = Validator::make($request->only(['month','year']),[
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
            'monthStatistics' => Statistics::monthReport($id,$month,$year),
            'status' => 'success'
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function monthReportUser(Request $request):JsonResponse
    {
        $v = Validator::make($request->only(['month','year']),[
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
            'monthStatistics' => Statistics::monthReport($id,$month,$year),
            'status' => 'success'
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dayReportAdmin(Request $request):JsonResponse
    {
        $v = Validator::make($request->only('date'),[
            'date' => 'required|date|date_format:Y-m-d'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'invalid_date',
                'message' => 'no date or wrong format'
            ],422);
        }

        $v = Validator::make($request->only('userId'),[
            'userId' => 'required|integer|exists:users,id'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'invalid_user',
                'message' => 'user does not exist'
            ],422);
        }

        return response()->json([
            'status' => 'success',
            'statistics' => Statistics::dayReport($request->userId,$request->date)
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function yearReportAdmin(Request $request):JsonResponse
    {
        $v = Validator::make($request->only('year'),[
            'year' => 'required|integer|min:2010|max:now()->year'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'invalid_year',
                'message' => 'year is missing or not available'
            ],422);
        }

        $v = Validator::make($request->only('userId'),[
            'userId' => 'required|integer|exists:users,id'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'invalid_user_id',
                'message' => 'user does not exist'
            ],422);
        }

        return response()->json([
            'status' => 'success',
            'statistics' => Statistics::yearReport($request->userId,$request->year)
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function daySummary(Request $request):JsonResponse
    {
        $v = Validator::make($request->only('date'),[
            'date' => 'required|date|date_format:Y-m-d'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'invalid_date',
                'message' => 'no date given or wrong formatted'
            ],422);
        }

        $v = Validator::make($request->only('users'),[
            'users' => 'nullable|array',
            'users.*' => 'nullable|integer|exists:users,id'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'invalid_users',
                'message' => 'users contains invalid users'
            ],422);
        }

        return response()->json([
            'status' => 'success',
            'summary' => Statistics::daySummary($request->date,$request->users)
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function monthSummary(Request $request):JsonResponse
    {
        $v = Validator::make($request->only(['month','year']),[
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2010|max:now()->year'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'invalid_date',
                'message' => 'invalid or missing month or year'
            ],422);
        }

        $v = Validator::make($request->only('users'),[
            'users' => 'nullable|array',
            'users.*' => 'nullable|integer|exists:users,id'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'invalid_users',
                'message' => 'users contains invalid users'
            ],422);
        }

        return response()->json([
            'status' => 'success',
            'summary' => Statistics::monthSummary($request->month,$request->year,$request->users)
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function yearSummary(Request $request):JsonResponse
    {
        $v = Validator::make($request->only('year'),[
            'year' => 'required|integer|min:2010|max:now()->year'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'invalid_year',
                'message' => 'year is invalid or missing'
            ],422);
        }

        $v = Validator::make($request->only('users'),[
            'users' => 'nullable|array',
            'users.*' => 'nullable|integer|exists:users,id'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'invalid_users',
                'message' => 'users contains invalid users'
            ],422);
        }

        return response()->json([
            'status' => 'success',
            'summary' => Statistics::yearSummary($request->year,$request->users)
        ]);
    }
}
