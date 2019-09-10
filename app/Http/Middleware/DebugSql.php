<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\DB;
use Closure;

class DebugSql
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
        DB::enableQueryLog();

        $response = $next($request);

        dd($request->all(), DB::getQueryLog());
    }
}
