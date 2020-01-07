<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pets;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class UserPetsController extends Controller
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
                    'up.*',
                ])
                ->from('user_pets AS up')
                ->where('user_role_id', '=', $user_role_id)
                ->get()
            ;

            $res = '['. $user_role->name .']的宠物\r\n';

            foreach ($rows as $row) {
                $res .= '【'. $row->name . '】：' . $row->level . '级，经验：' . $row->exp . '（最高:' . $row->max_level . '级）-- ' . ($row->is_fight ? '出战' : '休息') . '\r\n';
            }

            $res .= '出战：宠物名称\r\n';
            $res .= '喂养：装备名称(默认只喂养出战宠物,装备后面可以加上指定喂养宠物)';

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

    // 出战
    public function fight()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'pets_name' => ['required'],
            ], [
                'pets_name.required' => '宠物名称不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $userPets = DB::query()
                ->select([
                    'up.*',
                ])
                ->from('user_pets AS up')
                ->where('up.user_role_id', '=', $user_role_id)
                ->where('up.name', '=', $query['pets_name'])
                ->get()
                ->first()
            ;

            if (!$userPets) {
                throw new InvalidArgumentException('您没有该宠物！', 400);
            }

            if ($userPets->is_fight) {
                throw new InvalidArgumentException('该宠物已出战！', 400);
            }

            DB::beginTransaction();

            DB::table('user_pets')
                ->where('user_role_id', '=', $user_role_id)
                ->update([
                    'is_fight' => 0,
                ])
            ;

            DB::table('user_pets')
                ->where('id', '=', $userPets->id)
                ->where('user_role_id', '=', $user_role_id)
                ->update([
                    'is_fight' => 1,
                ])
            ;

            DB::commit();

            return Response::json([
                'code'    => 200,
                'message' => $userPets->name . ' 出战成功！',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 喂养
    public function feed()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'pets_name' => ['nullable'],
                'item_name' => ['required'],
                'num'       => ['nullable', 'integer', 'min:1'],
            ], [
                'pets_name.required' => '宠物名称不能为空',
                'item_name.required' => '物品名称不能为空',
                'num.required'       => '数量必须为数字',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $model = DB::query()
                ->select([
                    'up.*',
                ])
                ->from('user_pets AS up')
                ->where('up.user_role_id', '=', $user_role_id)
            ;

            if (isset($query['pets_name'])) {
                $model->where('up.name', '=', $query['pets_name']);
            }else {
                $model->where('up.is_fight', '=', 1);
            }

            $userPets = $model->get()->first();

            if (!$userPets) {
                throw new InvalidArgumentException('您没有找到要喂养的宠物，请先设置出战或指定宠物喂养！', 400);
            }

            if ($userPets->level >= $userPets->max_level) {
                throw new InvalidArgumentException('您的宠物达到了最高等级，无需喂养！', 400);
            }

            $item_num = 1;

            if (isset($query['num'])) {
                $item_num = $query['num'];
            }

            // 判断是否存在物品
            $item = DB::query()
                ->select([
                    'i.*',
                    'uk.item_num AS item_num',
                ])
                ->from('user_knapsack AS uk')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'uk.item_id')
                    ;
                })
                ->where('uk.user_role_id', '=', $user_role_id)
                ->where('i.name', '=', $query['item_name'])
                ->where('uk.item_num', '>=', $item_num)
                ->get()
                ->first()
            ;

            if (!$item) {
                throw new InvalidArgumentException('物品的数量不足！', 400);
            }

            if ($item->type != 10) {
                throw new InvalidArgumentException('请喂养装备！', 400);
            }

            if ($item->level - $userPets->level < -10 || $item->level - $userPets->level > 10 ) {
                throw new InvalidArgumentException('只能喂食宠物等级相差10级范围内的装备！', 400);
            }

            if (!isset($query['num'])) {
                $item_num = $item->item_num;
            }

            $exp = $item->level * $item_num;

            DB::beginTransaction();

            DB::table('user_knapsack')
                ->where('user_role_id', '=', $user_role_id)
                ->where('item_id', '=', $item->id)
                ->update([
                    'item_num' => DB::raw('`item_num` - ' . $item_num),
                ])
            ;

            DB::table('user_pets')
                ->where('id', '=', $userPets->id)
                ->where('user_role_id', '=', $user_role_id)
                ->update([
                    'exp' => DB::raw('`exp` + ' . $exp),
                ])
            ;

            DB::commit();

            $res = $userPets->name . ' 喂养成功，获得经验：' . $exp;

            $is_upgrade = Pets::is_upgrade($userPets->id, $user_role_id);

            if ($is_upgrade) {
                $res .= $is_upgrade;
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

    // 融合
    public function fuse()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'feed_pets_name' => ['required'],
                'fuse_pets_name' => ['required'],
            ], [
                'feed_pets_name.required' => '需喂养的宠物名称不能为空',
                'fuse_pets_name.required' => '用于喂养的宠物名称不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $feedPets = DB::query()
                ->select([
                    'up.*',
                ])
                ->from('user_pets AS up')
                ->where('up.user_role_id', '=', $user_role_id)
                ->where('up.name', '=', $query['feed_pets_name'])
                ->get()
                ->first();
            ;

            if (!$feedPets) {
                throw new InvalidArgumentException('您并没有该宠物：' . $query['feed_pets_name'] . '!', 400);
            }

            $fusePets = DB::query()
                ->select([
                    'up.*',
                ])
                ->from('user_pets AS up')
                ->where('up.user_role_id', '=', $user_role_id)
                ->where('up.name', '=', $query['fuse_pets_name'])
                ->get()
                ->first();
            ;

            if (!$fusePets) {
                throw new InvalidArgumentException('您并没有该宠物：' . $query['fuse_pets_name'] . '!', 400);
            }

            if ($fusePets->level < 2) {
                throw new InvalidArgumentException('您的宠物：' . $query['fuse_pets_name'] . '等级不足2级，无法用于融合!', 400);
            }

            if ($feedPets->level > $fusePets->level) {
                throw new InvalidArgumentException('用于融合的宠物等级低于喂养的宠物无法进行融合！', 400);
            }

            DB::beginTransaction();

            DB::table('user_pets')
                ->where('id', '=', $feedPets->id)
                ->update([
                   'exp' => DB::raw('`exp` + ' . (int)round($fusePets->exp * 0.8)),
                ])
            ;

            DB::table('user_pets')
                ->where('id', '=', $fusePets->id)
                ->delete()
            ;

            DB::commit();

            $res = '融合成功！';

            $is_upgrade = Pets::is_upgrade($feedPets->id, $user_role_id);

            if ($is_upgrade) {
                $res .= $is_upgrade;
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

    // 放生
    public function remove()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'pets_name' => ['required'],
            ], [
                'pets_name.required' => '宠物名称不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $userPets = DB::query()
                ->select([
                    'up.*',
                ])
                ->from('user_pets AS up')
                ->where('up.user_role_id', '=', $user_role_id)
                ->where('up.name', '=', $query['pets_name'])
                ->get()
                ->first()
            ;

            if (!$userPets) {
                throw new InvalidArgumentException('您没有该宠物！', 400);
            }

            DB::table('user_pets')
                ->where('id', '=', $userPets->id)
                ->where('user_role_id', '=', $user_role_id)
                ->delete()
            ;

            $res = $userPets->name . ' 已成功放生！';

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
