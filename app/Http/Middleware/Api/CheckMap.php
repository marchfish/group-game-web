<?php

namespace App\Http\Middleware\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
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
        $query = Request::all();

        $user_role = $query['user_role'];

        $row = DB::query()
            ->select([
                'md.*',
            ])
            ->from('map_date AS md')
            ->where('md.map_id', '=', $user_role->map_id)
            ->orWhere('md.area', '=', $user_role->area)
            ->get()
            ->first()
        ;

        if ($row && $row->hour != '' && strpos($row->hour, date('H', time())) === false) {
            $row1 = DB::query()
                ->select([
                    'sf.*',
                ])
                ->from('sys_fame AS sf')
                ->where('sf.id', '=', $user_role->fame_id)
                ->get()
                ->first()
            ;

            DB::table('user_role')
                ->where('id', '=', $user_role->id)
                ->update([
                    'map_id' => $row1->map_id ?? 12,
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
