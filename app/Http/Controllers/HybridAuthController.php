<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class HybridAuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $v = Validator::make($request->all(),[
            'email' => 'required|email|exists:users,email',
            'password' => 'required'
        ],[
            'email.required' => 'missing',
            'email.email' => 'invalid',
            'email.exists' => 'not_found',
            'password.required' => 'missing'
        ]);

        if($v->fails()){
            return response()->json([
                'status' => 'validation_errors',
                'errors' => $v->errors()->toArray()
            ],422);
        }

        if (auth()->guard('web')->attempt($credentials)) {
            session()->regenerateToken();
        }

        if ($token = $this->guard()->attempt($credentials)) {
            return $this->respondWithToken($token);
        }

        return response()->json(['status' => 'invalid_login'], 422);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->guard('web')->logout();
        $this->guard()->logout();

        return response()->json([
            'message' => 'Successfully logged out',
            'url' => '/login'
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token'  => $token,
            'token_type'    => 'bearer',
            'expires_in'    => $this->guard()->factory()->getTTL() * 60,
            'user'          => $this->guard()->user(),
            'url'           => '/home',
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard('api');
    }
}
