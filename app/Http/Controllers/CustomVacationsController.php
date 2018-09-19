<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomVacationsController extends Controller
{
    public function index()
    {
        $customVacations = DB::table('custom_vacations')
            ->orderBy('date')->get();
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

        if (!strtotime($request->date)) {
            abort(400);
        }

        $date = new Carbon($request->date);

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

            $id = DB::table('custom_vacations')->insertGetId([
                'date' => $date->toDateString(),
                'global' => 1
            ]);

            $records = [];
            foreach ($usersIds as $userId) {
                $records[] = [
                    'user_id' => $userId,
                    'vacation_id' => $id
                ];
            }
            DB::table('user_custom_vacations')->insert($records);

        } else {
            $id = DB::table('custom_vacations')->insertGetId([
                'date' => $date->toDateString(),
                'global' => 1
            ]);
        }

        return response()->json([
            'custom_vacation' => [
                'date' => $date->toDateString(),
                'global' => $request->global ? 1 : 0,
                'id' => $id
            ]
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

        $count = DB::table('custom_vacations')
            ->whereIn('id', $request->ids)
            ->delete();

        return response()->json([
            'count' => $count,
        ]);
    }
}
