<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

class AdminFlagsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $flags = app('settings')->getFlags();
        unset($flags['lost_time']);
        $data = [];
        foreach ($flags as $key => $value) {
            $data[] = [
                'name' => $key,
                'limit' => $value,
                'highlighted' => false
            ];
        }
        return response()->json([
            'flags' => $data
        ]);
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->only('flag'),[
            'flag' => 'required|string'
        ]);

        if($v->fails() || $request->flag == 'lost_time'){
            abort(400,'the given flag is not valid');
        }

        $flags = app('settings')->getFlags();
        if(isset($flags[$request->flag])){
            abort(400,'this flag already exists');
        }
        $flags[$request->flag] = 'no time limit';

        app('settings')->setFlags($flags);
    }

    public function destroy(Request $request)
    {
        $v = Validator::make($request->only('flags'),[
            'flags' => 'required|array|min:1',
            'flags.*' => 'string'
        ]);

        if($v->fails()){
            abort(400,'flags is not valid');
        }

        $oldFlags = app('settings')->getFlags();

        foreach ($request->flags as $flag) {
            unset($oldFlags[$flag]);
        }

        app('settings')->setFlags($oldFlags);
    }
}
