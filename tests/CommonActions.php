<?php

namespace Tests;


use Illuminate\Support\Facades\Artisan;

trait CommonActions
{
    public function seedData()
    {
        Artisan::call('seed:settings');
        app('settings')->refreshData();
        Artisan::call('seed:users');
    }
    public function logInUser($id)
    {
        auth()->guard('web')->loginUsingId($id);
        $token = auth()->guard('api')->fromUser(\App\User::find($id));
        auth()->guard('api')->setToken($token);
        auth()->guard('api')->authenticate();
    }
}
