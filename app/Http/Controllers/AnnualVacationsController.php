<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnnualVacationsController extends Controller
{
    public function index()
    {
        $months = collect(months())->keyBy('index');
        $annualVacations = app('settings')->getAnnualVacations();
        foreach ($annualVacations as &$vacation) {
            $vacation['month'] = $months->get($vacation['month']);
        }
        return response()->json([
            'months' => $months->values()->all(),
            'annual_vacations' => $annualVacations
        ]);
    }

    public function add(Request $request)
    {
        $months = collect(months())->keyBy('index');
        $month = $months->get($request->month);

        if (!$month || !is_int($request->day)
            || $request->day < 1 || $request->day > $month['days']) {
            abort(400);
        }

        if (app('settings')->annualVacationExists($request->only('month', 'day'))) {
            abort(400);
        }

        app('settings')->addAnnualVacation($request->only('month', 'day'));

        return response()->json([
            'annual_vacation' => [
                'day' => $request->day,
                'month' => $month
            ]
        ]);
    }

    public function delete(Request $request)
    {
        $months = collect(months())->keyBy('index');
        $month = $months->get($request->month);

        if (!$month || !is_int($request->day)
            || $request->day < 1 || $request->day > $month['days']) {
            abort(400);
        }

        app('settings')->removeAnnualVacation($request->only('month', 'day'));

        return response()->json([
            'success' => true
        ]);
    }
}
