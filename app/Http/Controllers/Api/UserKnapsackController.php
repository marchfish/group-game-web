<?php

namespace App\Http\Controllers\Api;

use App\Models\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UserKnapsackController extends Controller
{
    // 显示
    public function show()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $rows = DB::query()
                ->select([
                    'uk.*',
                    'i.name AS item_name',
                    'i.type AS item_type',
                ])
                ->from('user_knapsack AS uk')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'uk.item_id')
                    ;
                })
                ->where('uk.user_role_id', '=', $user_role_id)
                ->where('uk.item_num', '>', 0)
                ->paginate($query['size'])
            ;
            $res = '['. $user_role->name .'] 共：' . $rows->lastPage() . '页' . '(' . $rows->currentPage() . ')'. '\r\n';

            if ($query['page'] == 1) {
                $res .= '金币：' . $user_role->coin . '\r\n';
            }

            foreach ($rows as $row) {
                $res .= $row->item_name . '：' . $row->item_num . '\r\n';
            }

            $res .= '翻页：背包 数字';

            return Response::json([
                'code'    => 200,
                'message' => $res,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
