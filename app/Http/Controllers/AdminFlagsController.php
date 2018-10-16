<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Validator;
use App\Utilities\Flag;

class AdminFlagsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index():JsonResponse
    {
        return response()->json([
            'flags' => Flag::editable()
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request):JsonResponse
    {
        $v = Validator::make($request->only('flagName'),[
            'flagName' => 'required|string'
        ]);

        if($v->fails() || $request->flagName == 'lost_time'){
            return response()->json([
                'status' => 'invalid_flag_name',
                'message' => 'flag name is required and must be a string'
            ],422);
        }

        if(Flag::exists($request->flagName)){
            return response()->json([
                'status' => 'none_unique_name',
                'message' => 'flag name already exists'
            ],422);
        }

        $flags = app('settings')->getFlags();
        $flags[$request->flagName] = 'no time limit';
        app('settings')->setFlags($flags);

        return response()->json([
            'status' => 'flag_stored',
            'message' => 'flag added successfully'
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request):JsonResponse
    {
        $v = Validator::make($request->only('flagsNames'),[
            'flagsNames' => 'required|array|min:1',
            'flagsNames.*' => 'string'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'flag_invalid',
                'message' => 'flag name is required and must be a string'
            ],422);
        }

        $flags = app('settings')->getFlags();
        foreach ($request->flagsNames as $flag) {
            unset($flags[$flag]);
        }
        app('settings')->setFlags($flags);

        return response()->json([
            'status' => 'flags_deleted',
            'message' => 'flag deleted successfully'
        ]);
    }
}
