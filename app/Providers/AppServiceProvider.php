<?php

namespace App\Providers;

use App\Managers\Settings;
use Carbon\Carbon;
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
        if(config('app.test_time')){
            Carbon::setTestNow(new Carbon('2018-09-12 19:00'));
        }
        $this->app->singleton('settings',function (){
            return new Settings();
        });
        Carbon::setWeekendDays(app('settings')->getWeekends());
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
