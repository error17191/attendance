<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    public function signs()
    {
        return $this->hasMany(Sign::class);
    }

    public function workTimes()
    {
        return $this->hasMany(WorkTime::class);
    }

    public function signIn()
    {
        $this->status = 'working';
        $this->save();

        $workTime = new WorkTime();
        $workTime->started_work_at = now();
        $workTime->day = now()->toDateString();
        $this->workTimes()->save($workTime);

        return $this->sign('sign_in');
    }

    public function signOut()
    {
        $this->status = 'off';
        $this->save();

        $workTime = $this->workTimes()->where('day',now()->toDateString())->first();
        if(!$workTime){
            return;
        }
        $workTime->updateMinutes();

        return $this->sign('sign_out');
    }

    public function pause()
    {
        $this->status = 'paused';
        $this->save();

        $workTime = $this->workTimes()->where('day',now()->toDateString())->first();
        if(!$workTime){
            return;
        }
        $workTime->updateMinutes();

        return $this->sign('pause');
    }

    public function resume()
    {
        $this->status = 'working';
        $this->save();

        $workTime = $this->workTimes()->where('day',now()->toDateString())->first();
        if(!$workTime){
            return;
        }
        $workTime->startWork();

        return $this->sign('resume');
    }

    private function sign($type)
    {
        $now = now();
        $sign = new Sign();
        $sign->day = $now->toDateString();
        $sign->time = $now->toTimeString();
        $sign->type = $type;

        return $this->signs()->save($sign);
    }

    public function hasSignedInToday()
    {
        return (bool)$this->signs()
            ->where('day', now()->toDateString())
            ->where('type', 'sign_in')
            ->count();
    }

    public function hasSignedOutToday()
    {
        return (bool)$this->signs()
            ->where('day', now()->toDateString())
            ->where('type', 'sign_out')
            ->count();
    }

    public function isWorking()
    {
        return $this->status == 'working';
    }

    public function isPaused()
    {
        return $this->status == 'paused';
    }

    public function isOff()
    {
        return $this->status == 'off';
    }

}
