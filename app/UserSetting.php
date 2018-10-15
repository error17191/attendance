<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $table = "user_settings";
    protected $fillable=['user_id','tracked','work_anywhere'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function setUserSettings($data){
       if(self::find($data['user_id'])->first()){

           self::where('user_id',$data['user_id'])->update(['user_id'=>$data['user_id'],
               'tracked'=>$data['tracked'],
               'work_anywhere'=>$data['work_anywhere']]);
       }
       else{
           self::create($data);
       }
    }
    public static function getUserSettings(){
       return self::all();
    }
}
