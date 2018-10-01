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
        'name', 'email', 'password','username','mobile'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function isUsingFlag()
    {
        return $this->flag == 'on';
    }

    public function workTimes()
    {
        return $this->hasMany(WorkTime::class);
    }

    public function isWorking()
    {
        return $this->status == 'on';
    }

    public function isStopped()
    {
        return $this->status == 'off';
    }

    public function browsers()
    {
        return $this->hasMany(UserBrowser::class);
    }
}
