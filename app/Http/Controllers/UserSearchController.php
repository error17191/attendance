<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserSearchController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request):JsonResponse
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
