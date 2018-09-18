<?php

namespace App\Http\Controllers;

use App\UserBrowser;
use Illuminate\Http\Request;

class BrowserTokenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        if (auth()->user()->browsers()->where('token', $request->token)->count() > 0) {
            return response()->json([
                'status' => 'exists'
            ]);
        }

        auth()->user()->browsers()->save(UserBrowser::make([
            'token' => $request->token
        ]));

        return response()->json([
            'status' => 'saved'
        ]);
    }
}
