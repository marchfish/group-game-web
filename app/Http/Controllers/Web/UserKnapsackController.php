<?php

namespace App\Http\Controllers\Web;

use App\Models\Item;
use App\Models\UserRole;
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

            $userRole = UserRole::getUserRole();

            $res = '金币：' . $userRole->coin . '<br>';

            foreach ($rows as $row) {
                $res .= $row->item_name . '：' . $row->item_num;

                if ($row->item_type == 1 || $row->item_type == 2) {
                    $res .=' ----- ' . '<input type="button" class="action" data-url="' . URL::to('item/use') . '?item_id=' . $row->item_id . '" value="使用" />';
                }elseif ($row->item_type == 10) {
                    $res .=' ----- ' . '<input type="button" class="action-post" data-url="' . URL::to('equip') . '?item_id=' . $row->item_id . '" value="装备" />';
                }elseif ($row->item_type == 30) {
                    $res .=' ----- ' . '<input type="button" class="action" data-url="' . URL::to('map/transfer') . '?item_id=' . $row->item_id . '" value="传送" />';
                }elseif ($row->item_type == 40) {
                    $res .=' ----- ' . '<input type="button" class="action" data-url="' . URL::to('skill/study') . '?item_id=' . $row->item_id . '" value="学习" />';
                }

                $res .= ' ----- ' . '<input type="button" class="action" data-url="' . URL::to('item/check') . '?item_id=' . $row->item_id . '" value="查看" /><br>';
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
