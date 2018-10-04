<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserMachine extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'machine_id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $casts = [
        'pending' => 'boolean'
    ];
    protected $fillable = ['machine_id', 'user_id'];

    protected static function boot()
    {
        static::creating(function ($userMachine){
            if(is_null($userMachine->created_at)){
                $userMachine->created_at = now();
            }
        });

        parent::boot();
    }
}
