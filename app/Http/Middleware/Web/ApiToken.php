<?php

namespace App\Http\Middleware\Web;

use App\Support\Facades\AppSession;
use Illuminate\Support\Facades\Session;
use Closure;

class ApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->input('token');

        Session::replace(AppSession::start($token)->all());

        if (!Session::has('user')) {
            abort(401);
        }

        $response = $next($request);

        return $response;
    }
}
