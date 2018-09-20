<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function index(Request $request)
    {
        if(! $request->q){
            return response()->json([
                'users' => []
            ]);
        }
        $users = User::where('name', 'like', $request->q . '%')
            ->orWhere('username', 'like', $request->q . '%')
            ->orWhere('email', 'like', $request->q . '%')
            ->orWhere('mobile', 'like', $request->q . '%')
            ->take(10)->get();

        return response()->json([
            'users' => $users
        ]);

    }
}
