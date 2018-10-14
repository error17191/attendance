<?php

namespace App\Utilities;

use App\Flag;
use App\User;
use App\WorkTime;

class FlagUtility
{
    public static function fixUserFlag(User $user):void
    {
        if($user->isUsingFlag() && !Flag::where('user_id',$user->id)
                                    ->where('stopped_at',null)
                                    ->first()){
            $user->flag = 'off';
            $user->save();
        }elseif(!$user->isUsingFlag() && Flag::where('user_id',$user->id)
                                         ->where('stopped_at',null)
                                         ->first()){
            $user->flag = 'on';
            $user->save();
        }
    }
}