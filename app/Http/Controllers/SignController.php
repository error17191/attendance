<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function signIn()
    {
        if (auth()->user()->hasSignedInToday()) {
            abort(400);
        }

        $sign = auth()->user()->signIn();

        return response()->json([
            'sign' => $sign
        ]);
    }

    public function signOut()
    {
        if (! auth()->hasSignedInToday() || auth()->user()->hasSignedOutToday()) {
            abort(400);
        }

        $sign = auth()->user()->signOut();

        return response()->json([
            'sign' => $sign
        ]);
    }

    public function pause()
    {
        if (! auth()->user()->isWorking()) {
            abort(400);
        }

        $sign = auth()->user()->pause();

        return response()->json([
            'sign' => $sign
        ]);
    }

    public function resume()
    {
        if (! auth()->user()->isPaused()) {
            abort(400);
        }

        $sign = auth()->user()->resume();

        return response()->json([
            'sign' => $sign
        ]);
    }
}
