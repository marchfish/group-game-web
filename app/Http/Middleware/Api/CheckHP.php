<?php

namespace App\Http\Middleware\Api;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Closure;

class CheckHP
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
        $query = Request::all();

        $user_role = $query['user_role'];

        if ($user_role->hp <= 0) {
            return Response::json([
                'code'    => 400,
                'message' => '请复活后再试',
            ]);
        }else {
            return $next($request);
        }
    }
}
