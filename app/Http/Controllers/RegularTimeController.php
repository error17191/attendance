<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RegularTimeController extends Controller
{
    public function index()
    {
        $regularTime = app('settings')->getRegularTime();
        $notifications = app('settings')->getNotifications();
        $lostTime = get_flag_time_limit_seconds('lost_time');
        return response()->json([
            compact('regularTime','notifications','lostTime')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());
        app('settings')->setRegularTime($request->regularTime);
        app('settings')->setNotifications($request->notifyMe);
        $flags = app('settings')->getFlags();
        $flags['lost_time'] = $request->lostTime * 60;
        app('settings')->setFlags($flags);
        return response()->json([
            'message' => 'saved'
        ]);
    }

    private function rules():array
    {
        return [
            'regularTime' => 'required|array|min:3|max:3',
            'regularTime.from' => 'required|numeric|min:0|max:23.5',
            'regularTime.to' => 'required|numeric|max:23.5',
            'regularTime.regularHours' => 'required|numeric|min:0|max:23.5',
            'notifyMe' => 'required|array|min:4|max:4',
            'notifyMe.late_attendance' => 'required|boolean',
            'notifyMe.late_attendance_time' => 'nullable|numeric|min:0|max:23.5',
            'notifyMe.early_checkout' => 'required|boolean',
            'notifyMe.early_checkout_time' => 'nullable|numeric',
            'lostTime' => 'required|integer'
        ];
    }
}
