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
        return response()->json(['user_settings'=>$user_settings]);
    }

    public function setUserSettings(Request $request)
    {
        $user_settings = new UserSetting();
        $user_settings->setUserSettings($request->all());
        return response()->json(['user_settings'=>$user_settings]);
    }
}
