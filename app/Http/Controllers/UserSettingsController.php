<?php

namespace App\Http\Controllers;

use App\UserSetting;
use Illuminate\Http\Request;

class UserSettingsController extends Controller
{

    public function __construct()
    {
        return $this->middleware('auth');
    }

    public function getUserSettings()
    {
        $user_settings = UserSetting::getUserSettings();
        return response()->json(['user_settings' => $user_settings]);
    }

    public function setUserSettings(Request $request)
    {
        $user_settings = new UserSetting();
        $user_settings->setUserSettings($request->all());
        return response()->json(['user_settings' => $user_settings]);
    }

    public function checkIfUserCanBeTracked($user_id)
    {
        $tracked = UserSetting::where('user_id', $user_id)->select('tracked')->first();
        return response()->json(['tracked' => $tracked]);
    }
    public function checkIfUserCanWorkAnywhere($user_id)
    {
        $work_anywhere = UserSetting::where('user_id', $user_id)->select('work_anywhere')->first();
        return response()->json(['work_anywhere' => $work_anywhere]);
    }
}
