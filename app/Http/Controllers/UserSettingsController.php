<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserSettingsController extends Controller
{

    public function __construct()
    {
        return $this->middleware('auth');
    }

    public function checkIfUserCanBeTracked($user_id)
    {
        $tracked = User::find($user_id)->select('tracked')->first();
        return response()->json(['tracked' => $tracked]);
    }

    public function checkIfUserCanWorkAnywhere($user_id)
    {
        $work_anywhere = User::find($user_id)->select('work_anywhere')->first();
        return response()->json(['work_anywhere' => $work_anywhere]);
    }
}
