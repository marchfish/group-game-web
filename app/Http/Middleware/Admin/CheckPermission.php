<?php

namespace App\Http\Middleware\Admin;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Closure;

class CheckPermission
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
        $action  = Route::current()->getActionName();
        $action  = substr($action, strrpos($action, '\\') + 1);
        $actions = explode('@', $action);

        if (Session::has('admin.account.controllers.' . $actions[0])) {
            return $next($request);
        } else {
            abort(403);
        }
    }
}
