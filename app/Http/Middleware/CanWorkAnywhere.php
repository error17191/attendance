<?php

namespace App\Http\Middleware;

use App\User;
use App\UserMachine;
use Closure;
use Illuminate\Support\Facades\Auth;

class CanWorkAnywhere
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /*check if workanywhere false then check if request has machine id or not
        if has then check if attached to this user or not
        */;
        if(Auth::user()->work_anywhere){
            return $next($request);
        }

        if($request->machine_id && UserMachine::where('user_id', Auth::id())->where('machine_id', $request->machine_id)->first()){
            return $next($request);
        }

        abort(401);
    }
}
