<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function update(Request $request)
    {
        if(!$request->old_password){
            return response()->json([
                'status' => 'old_password_missing',
            ],422);
        }

        if(!$request->password){
            return response([
                'status' => 'password_missing'
            ],422);
        }

        if(!$request->password_confirmation){
            return response([
                'status' => 'password_confirmation_missing'
            ],422);
        }

        if($request->password !== $request->password_confirmation){
            return response([
                'status' => 'passwords_mismatch'
            ],422);
        }

        if(mb_strlen($request->password) < 6){
            return response([
                'status' => 'password_too_short'
            ],422);
        }

        if(! Hash::check($request->old_password, auth()->user()->password)){
            return response([
                'status' => 'incorrect_old_password'
            ],422);
        }

        auth()->user()->password = bcrypt($request->password);
        auth()->user()->save();

        return response()->json([
            'status' => 'success'
        ]);
    }
}
