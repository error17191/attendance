<?php

namespace App\Providers;

use App\Managers\Settings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if(!app()->runningInConsole() && config('app.enable_fake_login')){
            $userId = config('app.fake_logged_user');
            auth()->guard('web')->loginUsingId($userId);
            $token = auth()->guard('api')->fromUser(\App\User::find($userId));
            auth()->guard('api')->setToken($token);
            auth()->guard('api')->authenticate();
        }

        if(config('app.enable_test_time')){
            Carbon::setTestNow(new Carbon(config('app.test_time')));
        }

        $this->app->singleton('settings',function (){
            return new Settings();
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
