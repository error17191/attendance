<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RegularTimeController extends Controller
{
    public function index()
    {
        $regularTime = app('settings')->getRegularTime();
        $regularHours = app('settings')->getRegularHours();
        $notifications = app('settings')->getNotifications();
        return response()->json([
            compact('regularTime','regularHours','notifications')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());
        app('settings')->setRegularTime($request->regularTime);
        app('settings')->setRegularHours($request->regularHours);
        app('settings')->setNotifications($request->notifyMe);
        return response()->json([
            'message' => 'saved'
        ]);
    }

    private function rules():array
    {
        return [
            'regularTime' => 'required|array|min:2|max:2',
            'regularTime.from' => 'required|integer|min:0|max:47',
            'regularTime.to' => 'required|integer|gt:regularTime.from|max:47',
            'regularHours' => 'required|numeric|min:0|max:24',
            'notifyMe' => 'required|array|min:4|max:4',
            'notifyMe.late_attendance' => 'required|boolean',
            'notifyMe.late_attendance_time' => 'nullable|integer|min:0|max:47',
            'notifyMe.early_checkout' => 'required|boolean',
            'notifyMe.early_checkout_time' => 'nullable|integer|gt:notifyMe.late_attendance_time|max:47'
        ];
    }
}
