<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WeekendsController extends Controller
{
    public function index()
    {
        $weekends = app('settings')->getWeekends();
        $weekDays = week_days();
        return response()->json([
            'weekdays' => $weekDays,
            'weekends' => $weekends
        ]);
    }

    public function update(Request $request)
    {
        if (array_diff($request->weekends, range(0, 6)) != []) {
            abort(400);
        }
        app('settings')->setWeekends($request->weekends);

        return response()->json([
            'success' => true
        ]);
    }
}
