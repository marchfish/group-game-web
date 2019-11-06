<?php

namespace App\Http\Middleware\Api;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Closure;

class CheckVip
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

        $user_role_id = $user_role->id;

        $row = DB::query()
            ->select([
                'uv.*',
            ])
            ->from('user_vip AS uv')
            ->where('uv.user_role_id', '=', $user_role_id)
            ->get()
            ->first()
        ;

        if (!$row || time() > strtotime($row->expired_at)) {
            return Response::json([
                'code'    => 400,
                'message' => '您的会员已到期，请购买后继续...',
            ]);
        }else {
            return $next($request);
        }
    }
}
