<?php

namespace App;

use function foo\func;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','username','mobile','tracked','work_anywhere'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    /**
     * @return bool
     */
    public function isUsingFlag()
    {
        return (bool) Flag::where('user_id', $this->id)
            ->whereNull('stopped_at')
            ->count();
    }

    public function workTimes()
    {
        return $this->hasMany(WorkTime::class);
    }

    /**
     * @return bool
     */
    public function isWorking()
    {
        return (bool)WorkTime::where('user_id', $this->id)
            ->whereNull('stopped_work_at')
            ->count();
    }

    public function isStopped()
    {
        return ! $this->isWorking();
    }

    public function browsers()
    {
        return $this->hasMany(UserBrowser::class);
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

}
