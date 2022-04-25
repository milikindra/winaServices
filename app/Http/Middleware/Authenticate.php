<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $api_token = $request->input('api_token');
        // Log::debug($api_token);
        $user = User::where('api_token', $api_token)->first();
        if (!isset($user)) {
            return response()->json([
                'result' => False,
                'message' => 'Invalid API Token'
            ]);
        }

        // if ($this->auth->guard($guard)->guest()) {
        //     return response('Unauthorized.', 401);
        // }

        return $next($request);
    }
}
