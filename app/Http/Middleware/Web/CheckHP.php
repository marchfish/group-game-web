<?php

namespace App\Http\Middleware\Web;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
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
        $user_role_id = Session::get('user.account.user_role_id');

        $row = DB::query()
            ->select([
                '*',
            ])
            ->from('user_role AS ur')
            ->where('id', '=', $user_role_id)
            ->where('hp', '>', 0)
            ->limit(1)
            ->get()
            ->first()
        ;

        if (!$row) {
            return Response::json([
                'code'    => 400,
                'message' => '请复活后再试',
            ]);
        }else {
            return $next($request);
        }
    }
}
