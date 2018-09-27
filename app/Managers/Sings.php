<?php

namespace App\Managers;

use App\Sign;
use App\User;

class Sings
{
    /** @var string  */
    protected $day;

    /** @var string  */
    protected $time;

    /** @var \App\User  */
    protected $user;

    public function __construct(User $user)
    {
        $this->day = now()->toDateString();
        $this->time = now()->toTimeString();
        $this->user = $user;
    }

    public function sign(string $type,int $workTimeId)
    {
        $sign = new Sign();
        $sign->user_id = $this->user->id;
        $sign->type = $type;
        $sign->day = $this->day;
        $sign->time = $this->time;
        $sign->work_time_id = $workTimeId;
        $sign->save();
        return $sign;
    }

    public function todaySigns()
    {
        return Sign::where('user_id',$this->user->id)
            ->where('day',$this->day)->get();
    }

}