<?php

namespace App\Http\Middleware\Api;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Closure;

class CheckTokenAndQQ
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
        $qq = $request->input('qq');
        $app_token = env('APP_TOKEN');

        if (!$token || $token != $app_token || !$qq) {
            return Response::json([
                'code'    => 400,
                'message' => '验证失败',
            ]);
        }

        $user_role = DB::query()
            ->select([
                'ur.*',
                'm.area AS area',
            ])
            ->from('user_role AS ur')
            ->join('user AS u', function ($join) {
                $join
                    ->on('u.id', '=', 'ur.user_id')
                ;
            })
            ->join('map AS m', function ($join) {
                $join
                    ->on('m.id', '=', 'ur.map_id')
                ;
            })
            ->where('u.qq', '=', $qq)
            ->limit(1)
            ->get()
            ->first()
        ;

        if (!$user_role) {
            return Response::json([
                'code'    => 400,
                'message' => '您还没有注册用户或绑定QQ',
            ]);
        }

        $request->merge([
            'user_role' => $user_role,
        ]);

        return $next($request);
    }
}
