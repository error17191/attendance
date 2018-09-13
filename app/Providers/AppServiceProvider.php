<?php

namespace App\Providers;

use App\Managers\Prefs;
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
        $this->app->singleton('prefs',function (){
            return new Prefs();
        });
        Carbon::setWeekendDays([5,6]);
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
