<?php

namespace App\Http\Middleware\Web;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Closure;

class CheckLogin
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
        if (Session::has('user.account')) {
            return $next($request);
        } else {
            return Response::redirectTo('');
        }
    }
}
