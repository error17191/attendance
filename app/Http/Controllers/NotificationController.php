<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index()
    {
       $messages=DB::table('notifications')->where('notifiable_id',Auth::id())->select('data')->get();
       return response()->json(['messages'=>$messages]);
    }
}
