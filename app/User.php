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

    public function todaySigns()
    {
        return $this->signs()->where('day',now()->toDateString());
    }

    public function workTimes()
    {
        return $this->hasMany(WorkTime::class);
    }

    public function todayTime()
    {
        return $this->workTimes()->where('day', now()->toDateString())->first();
    }

    public function startWork()
    {
        $now = now();

        $workTime = $this->todayTime();
        if(! $workTime){
            $workTime = new WorkTime();
            $workTime->user_id = $this->id;
            $workTime->day = $now->toDateString();
        }
        $workTime->started_work_at = $now;
        $workTime->save();

        $sign = new Sign();
        $sign->type = 'start';
        $sign->day = $now->toDateString();
        $sign->time = $now->toTimeString();
        $this->signs()->save($sign);

        $this->status = 'on';
        $this->save();

        return [
            'sign' => $sign,
            'workTime' => $workTime
        ];
    }

    public function stopWork()
    {
        $now = now();

        $workTime = $this->todayTime();
        if(! $workTime){
            return;
        }
        $workTime->updateSeconds();
        $workTime->save();

        $sign = new Sign();
        $sign->type = 'stop';
        $sign->day = $now->toDateString();
        $sign->time = $now->toTimeString();
        $this->signs()->save($sign);

        $this->status = 'off';
        $this->save();

        return [
            'sign' => $sign,
            'workTime' => $workTime
        ];
    }

    public function isWorking()
    {
        return $this->status == 'on';
    }

    public function isStopped()
    {
        return $this->status == 'off';
    }

}
