<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserBrowser extends Model
{
    protected $fillable = ['user_id' , 'token'];
}
