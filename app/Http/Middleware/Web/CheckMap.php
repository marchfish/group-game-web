<?php

namespace App\Http\Middleware\Web;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Closure;

class CheckMap
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
                'm.id AS id',
                'm.area AS area',
                'ur.fame_id AS fame_id',
            ])
            ->from('map AS m')
            ->join('user_role AS ur', function ($join) {
                $join
                    ->on('ur.map_id', '=', 'm.id')
                ;
            })
            ->where('ur.id', '=', $user_role_id)
            ->get()
            ->first()
        ;

        $row1 = DB::query()
            ->select([
                'md.*',
            ])
            ->from('map_date AS md')
            ->where('md.map_id', '=', $row->id)
            ->orWhere('md.area', '=', $row->area)
            ->get()
            ->first()
        ;

        if ($row1 && $row1->hour != '' && strpos($row1->hour, date('H', time())) === false) {
            $row2 = DB::query()
                ->select([
                    'sf.*',
                ])
                ->from('sys_fame AS sf')
                ->where('sf.id', '=', $row->fame_id)
                ->get()
                ->first()
            ;

            DB::table('user_role')
                ->where('id', '=', $user_role_id)
                ->update([
                    'map_id' => $row2->map_id ?? 12,
                ])
            ;

            return Response::json([
                'code'    => 400,
                'message' => '您所在的地图未到开启时间，已被传出！',
            ]);
        }else {
            return $next($request);
        }
    }
}
