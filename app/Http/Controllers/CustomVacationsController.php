<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomVacationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request):JsonResponse
    {
        if($request->target == 'specific'){
            $users['employees'] = json_decode($request->employees,true);
            $v = Validator::make($users,[
                'employees' => 'array',
                'employees.*' => 'integer|min:1|exists:users,id'
            ]);

            if($v->fails()){
                return response()->json([
                    'status' => 'invalid_employee',
                    'message' => 'one or more employee dose not exist'
                ],422);
            }

            $customVacations = DB::table('users')
                ->leftJoin('users_custom_vacations','users.id','users_custom_vacations.user_id')
                ->leftJoin('custom_vacations','users_custom_vacations.vacation_id','custom_vacations.id')
                ->select('custom_vacations.*')
                ->whereIn('users.id',$users['employees'])
                ->where('custom_vacations.global',0)
                ->orderBy('custom_vacations.date')
                ->get()->unique('date')->values();
        }else{
            $customVacations = DB::table('custom_vacations')
                ->where('global',1)
                ->orderBy('date')
                ->get();
        }

        return response()->json([
            'custom_vacations' => $customVacations,
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request):JsonResponse
    {
        $v = Validator::make($request->only('dates'),[
            'dates' => 'required|array',
            'dates.*' => 'date|date_format:Y-m-d'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'invalid_dates',
                'message' => 'date must be date formatted as y-m-d'
            ],422);
        }

        if (!$request->global) {
            $dates = [];
            foreach ($request->dates as $date) {
                if(DB::table('custom_vacations')->where('date',$date)->get()->count()){
                    continue;
                }
                $dates[] = $date;
            }

            $v = Validator::make($request->only('users'), [
                'users' => 'array',
                'users.*' => 'integer|min:1|exists:users,id'
            ]);

            if ($v->fails()) {
                return response()->json([
                    'status' => 'invalid_users',
                    'message' => 'one or more users does not exist'
                ],422);
            }

            $customVacations = [];
            foreach ($dates as $date){
                $customVacations[] = [
                    'date' => $date,
                    'global' => 0
                ];
            }

            DB::table('custom_vacations')->insert($customVacations);
            $vacationsIds = DB::table('custom_vacations')
                ->select('id')
                ->wherein('date',$request->dates)
                ->get()->pluck('id');

            $records = [];
            foreach ($request->users as $userId) {
                foreach ($vacationsIds as $vacationsId) {
                    if(DB::table('users_custom_vacations'))
                    $records[] = [
                        'user_id' => $userId,
                        'vacation_id' => $vacationsId
                    ];
                }
            }

            DB::table('users_custom_vacations')->insert($records);
        } else {
            $dates = [];
            foreach ($request->dates as $date) {
                if(DB::table('custom_vacations')->where('date',$date)->get()->count()){
                    if(!DB::table('custom_vacations')->where('date',$date)->first()->global){
                        DB::table('custom_vacations')->where('date',$date)->update(['global' => 1]);
                        $vacationId = DB::table('custom_vacations')->where('date',$date)->first()->id;
                        DB::table('users_custom_vacations')->where('vacation_id',$vacationId)->delete();
                    }
                    continue;
                }
                $dates[] = $date;
            }

            $customVacations = [];
            foreach ($dates as $date){
                $customVacations[] = [
                    'date' => $date,
                    'global' => 1
                ];
            }

            DB::table('custom_vacations')->insert($customVacations);
        }

        return response()->json([
            'custom_vacations' => DB::table('custom_vacations') ->orderBy('date')->get()
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request):JsonResponse
    {
        $v = Validator::make($request->only('ids'),[
            'ids' => 'array',
            'ids.*' => 'integer|min:1|exists:custom_vacations,id'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'invalid_custom_vacations',
                'message' => 'one or more custom vacations does not exist'
            ],422);
        }

        DB::table('custom_vacations')
            ->whereIn('id', $request->ids)
            ->delete();

        return response()->json([
            'custom_vacations' => DB::table('custom_vacations')->orderBy('date')->get(),
        ]);
    }
}
