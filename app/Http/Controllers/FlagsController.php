<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Managers\FlagManager;

class FlagsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function startFlag(Request $request)
    {
        $manager = new FlagManager(auth()->user());
        if(!isset(app('settings')->getFlags()[$request->type])){
            abort(400);
        }
        $message = $manager->startFlag($request->type);
        return response()->json([
            'message' => $message
        ]);
    }

    public function endFlag()
    {
        $manager = new FlagManager(auth()->user());
        $message = $manager->endFlag();
        return response()->json([
            'message' => $message
        ]);
    }
}
