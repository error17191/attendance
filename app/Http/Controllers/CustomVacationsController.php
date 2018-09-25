<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomVacationsController extends Controller
{
    public function index(Request $request)
    {
        if($request->target == 'specific'){
            $users['employees'] = json_decode($request->employees,true);
            $v = Validator::make($users,[
                'employees' => 'array',
                'employees.*' => 'integer|min:1'
            ]);
            if($v->fails()){
                abort(400);
            }
            $customVacations = DB::table('users')
                ->leftJoin('users_custom_vacations','users.id','users_custom_vacations.user_id')
                ->leftJoin('custom_vacations','users_custom_vacations.vacation_id','custom_vacations.id')
                ->select('custom_vacations.*')
                ->whereIn('users.id',$users['employees'])
                ->where('custom_vacations.global',0)
                ->orderBy('custom_vacations.date')
                ->get();
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

    public function store(Request $request)
    {
//        $date = new Carbon($request->date);
//
//        if (!$date->lt(today())) {
//            abort(400);
//        }

        $v = Validator::make($request->only('dates'),[
            'dates' => 'required|array',
            'dates.*' => 'date|date_format:Y-m-d'
        ]);

        if($v->fails()){
            abort(400);
        }

        if (!$request->global) {
            $v = Validator::make($request->only('users'), [
                'users' => 'array',
                'users.*' => 'integer|min:1'
            ]);

            if ($v->fails()) {
                abort(400);
            }
            $usersIds = User::query()->select('id')->get()->pluck('id')->all();
            if (array_diff($request->users, $usersIds) != []) {
                abort(400);
            }

            $customVacations = [];

            foreach ($request->dates as $date){
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
            foreach ($usersIds as $userId) {
                foreach ($vacationsIds as $vacationsId) {
                    $records[] = [
                        'user_id' => $userId,
                        'vacation_id' => $vacationsId
                    ];
                }
            }
            DB::table('users_custom_vacations')->insert($records);

        } else {
            $customVacations = [];
            foreach ($request->dates as $date){
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

    public function delete(Request $request)
    {
        $v = Validator::make($request->only('ids'),[
            'ids' => 'array',
            'ids.*' => 'integer|min:1'
        ]);

        if($v->fails()){
            abort(400);
        }

        DB::table('custom_vacations')
            ->whereIn('id', $request->ids)
            ->delete();

        return response()->json([
            'custom_vacations' => DB::table('custom_vacations')->orderBy('date')->get(),
        ]);
    }
}
