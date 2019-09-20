<?php

namespace App\Http\Controllers\Web;

use App\Models\Item;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class UserKnapsackController extends Controller
{
    // 显示
    public function show()
    {
        try {
            $user_role_id = Session::get('user.account.user_role_id');

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
                ->get()
            ;

            $res = '';

            foreach ($rows as $row) {
                $res .= $row->item_name . '：' . $row->item_num;

                if ($row->item_type == 1) {
                    $res .=' ----- ' . '<input type="button" class="action" data-url="' . URL::to('user-knapsack') . '" value="使用" />';
                }elseif ($row->item_type == 10) {
                    $res .=' ----- ' . '<input type="button" class="action" data-url="' . URL::to('user-knapsack') . '" value="装备" />';
                }

                $res .= '<br>';
            }

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
