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

    }

    public function store(Request $request)
    {
        $date = new Carbon($request->date);

        if (!$date->gte(today())) {
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
            $records = [];
            foreach ($usersIds as $userId){
                $records[] = [
                    'user_id' => $userId,
                    'date' => $request->date
                ];
            }
            DB::table('custom_vacations')->insert($records);
        }else{
            DB::table('custom_vacations')->insert([[
                'global' => 1,
                'date' => $request->date
            ]]);
        }

        return response()->json([
            'success' => true
        ]);
    }
}
