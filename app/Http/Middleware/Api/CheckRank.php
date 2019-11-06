<?php

namespace App\Http\Middleware\Api;

use App\Models\Rank;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Closure;

class CheckRank
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
        $row = DB::query()
            ->select([
                'sd.*',
            ])
            ->from('sys_date AS sd')
            ->get()
            ->first()
        ;

        $now_at = date('Y-m-d H:i:s',time());

        $difference = time_difference($row->rank_expired_at, $now_at, 'second');

        if ($difference >= 0) {

            Rank::start();

            return Response::json([
                'code'    => 400,
                'message' => '新赛季开始，暂无排行!',
            ]);
        }else {
            return $next($request);
        }
    }
}
