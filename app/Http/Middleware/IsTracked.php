<?php

namespace App\Http\Middleware;

use App\User;
use App\UserMachine;
use Closure;
use Illuminate\Support\Facades\Auth;

class IsTracked
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
        $params=$request->route()->parameters();
        if (User::find(Auth::id())->where('work_anywhere',0)->first()) {
            if (array_key_exists('machine_id',$params)) {
                if (UserMachine::where('user_id', Auth::id())->where('machine_id', $params['machine_id'])->first()) {
                    return $next($request);
                } else {
                    abort(401);
                }
            } else {
                abort(401);
            }
        }
        else {
            return $next($request);
        }
        return $next($request);
    }
}
