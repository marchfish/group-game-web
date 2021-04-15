<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\npc;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MapController extends Controller
{
    public function index()
    {
        try {
            $query = Request::all();

            $model = DB::query()
                ->select([
                    'm.id AS id',
                    'm.name AS name',
                    'm.description AS description',
                    'm.is_activity AS is_activity',
                    DB::raw('IFNULL(`n`.`name`, "") AS `npc_name`'),
                    DB::raw('IFNULL(`e`.`name`, "") AS `enemy_name`'),
                    DB::raw('IFNULL(`mforward`.`name`, "") AS `forward_name`'),
                    DB::raw('IFNULL(`mbehind`.`name`, "") AS `behind_name`'),
                    DB::raw('IFNULL(`mup`.`name`, "") AS `up_name`'),
                    DB::raw('IFNULL(`mdown`.`name`, "") AS `down_name`'),
                    DB::raw('IFNULL(`mleft`.`name`, "") AS `left_name`'),
                    DB::raw('IFNULL(`mright`.`name`, "") AS `right_name`'),
                    DB::raw('IFNULL(`ma`.`name`, "") AS `area_name`'),
                ])
                ->from('map AS m')
                ->leftJoin('npc AS n', function ($join) {
                    $join
                        ->on('n.id', '=', 'm.npc_id')
                    ;
                })
                ->leftJoin('enemy AS e', function ($join) {
                    $join
                        ->on('e.id', '=', 'm.enemy_id')
                    ;
                })
                ->leftJoin('map AS mforward', function ($join) {
                    $join
                        ->on('mforward.id', '=', 'm.forward')
                    ;
                })
                ->leftJoin('map AS mbehind', function ($join) {
                    $join
                        ->on('mbehind.id', '=', 'm.behind')
                    ;
                })
                ->leftJoin('map AS mup', function ($join) {
                    $join
                        ->on('mup.id', '=', 'm.up')
                    ;
                })
                ->leftJoin('map AS mdown', function ($join) {
                    $join
                        ->on('mdown.id', '=', 'm.down')
                    ;
                })
                ->leftJoin('map AS mleft', function ($join) {
                    $join
                        ->on('mleft.id', '=', 'm.left')
                    ;
                })
                ->leftJoin('map AS mright', function ($join) {
                    $join
                        ->on('mright.id', '=', 'm.right')
                    ;
                })
                ->leftJoin('map_area AS ma', function ($join) {
                    $join
                        ->on('ma.id', '=', 'm.area')
                    ;
                })
                ->orderBy('m.id', 'desc')
            ;

            if (isset($query['name'])) {
                $model->where('m.name', 'like', '%'. $query['name'] . '%');
            }

            if (isset($query['area'])) {
                $model->where('ma.name', 'like', '%'. $query['area'] . '%');
            }

            $paginate = $model->paginate($query['size']);

            return Response::view('admin/map/index', [
                'paginate' => $paginate,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 显示创建
    public function new()
    {
        try {
            $npc_type = DB::query()
                ->select([
                    'type as id',
                ])
                ->from('npc')
                ->groupBy('type')
                ->get()
                ->each(function ($type){
                    $type->name = npc::getNameByType($type->id);
                })
            ;

            $enemy_level = DB::query()
                ->select([
                    'level as id',
                    'level as name',
                ])
                ->from('enemy')
                ->groupBy('level')
                ->get()
            ;

            $map_area = DB::query()
                ->select([
                    'id',
                    'name',
                ])
                ->from('map_area')
                ->get()
            ;

            return Response::view('admin/map/new', [
                'npc_type'    => $npc_type,
                'enemy_level' => $enemy_level,
                'map_area'    => $map_area,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 获取地图
    public function map()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'search' => ['required'],
            ], [
                'search.required' => '区域不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $maps = DB::query()
                ->select([
                    'id',
                    'name',
                ])
                ->from('map')
                ->where('area', '=', $query['search'])
                ->get()
            ;

            return Response::json([
                'code' => 200,
                'data' => $maps,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 获取npc
    public function npc()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'search' => ['required'],
            ], [
                'search.required' => '属性不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $npcs = DB::query()
                ->select([
                    'id',
                    'name',
                ])
                ->from('npc')
                ->where('type', '=', $query['search'])
                ->get()
            ;

            return Response::json([
                'code' => 200,
                'data' => $npcs,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 获取怪物
    public function enemy()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'search' => ['required'],
            ], [
                'search.required' => '等级不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $enemy = DB::query()
                ->select([
                    'id',
                    'name',
                ])
                ->from('enemy')
                ->where('level', '=', $query['search'])
                ->get()
            ;

            return Response::json([
                'code' => 200,
                'data' => $enemy,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 创建
    public function create()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'name'    => ['required'],
                'area_id' => ['required'],
            ], [
                'name.required'    => '名称不能为空',
                'area_id.required' => '区域不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $row = DB::query()
                ->select(['id'])
                ->from('map')
                ->where('name', '=', $query['name'])
                ->where('area', '=', $query['area_id'])
                ->limit(1)
                ->get()
                ->first()
            ;

            if ($row) {
                throw new InvalidArgumentException('地图已存在', 400);
            }

            DB::table('map')
                ->insert([
                    'name'        => $query['name'],
                    'npc_id'      => $query['npc_id'] ?? 0,
                    'enemy_id'    => $query['enemy_id'] ?? 0,
                    'description' => $query['description'] ?? '',
                    'up'          => $query['up'] ?? 0,
                    'down'        => $query['down'] ?? 0,
                    'left'        => $query['left'] ?? 0,
                    'right'       => $query['right'] ?? 0,
                    'forward'     => $query['forward'] ?? 0,
                    'behind'      => $query['behind'] ?? 0,
                    'area'        => $query['area_id'],
                    'is_activity' => $query['is_activity'] ?? 0,
                ])
            ;

            return Response::json([
                'code'    => 200,
                'message' => '成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 显示编辑
    public function edit()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'map_id' => ['nullable', 'integer'],
            ], [
                'map_id.required' => 'id 不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $map = DB::query()
                ->select([
                    'm.*',
                    DB::raw('IFNULL(`n`.`type`, "") AS `npc_type`'),
                    DB::raw('IFNULL(`e`.`level`, "") AS `enemy_level`'),
                    DB::raw('IFNULL(`mforward`.`area`, "") AS `forward_area`'),
                    DB::raw('IFNULL(`mbehind`.`area`, "") AS `behind_area`'),
                    DB::raw('IFNULL(`mup`.`area`, "") AS `up_area`'),
                    DB::raw('IFNULL(`mdown`.`area`, "") AS `down_area`'),
                    DB::raw('IFNULL(`mleft`.`area`, "") AS `left_area`'),
                    DB::raw('IFNULL(`mright`.`area`, "") AS `right_area`'),
                ])
                ->from('map as m')
                ->leftJoin('npc as n', function ($join) {
                    $join
                        ->on('n.id', '=', 'm.npc_id')
                    ;
                })
                ->leftJoin('enemy AS e', function ($join) {
                    $join
                        ->on('e.id', '=', 'm.enemy_id')
                    ;
                })
                ->leftJoin('map AS mforward', function ($join) {
                    $join
                        ->on('mforward.id', '=', 'm.forward')
                    ;
                })
                ->leftJoin('map AS mbehind', function ($join) {
                    $join
                        ->on('mbehind.id', '=', 'm.behind')
                    ;
                })
                ->leftJoin('map AS mup', function ($join) {
                    $join
                        ->on('mup.id', '=', 'm.up')
                    ;
                })
                ->leftJoin('map AS mdown', function ($join) {
                    $join
                        ->on('mdown.id', '=', 'm.down')
                    ;
                })
                ->leftJoin('map AS mleft', function ($join) {
                    $join
                        ->on('mleft.id', '=', 'm.left')
                    ;
                })
                ->leftJoin('map AS mright', function ($join) {
                    $join
                        ->on('mright.id', '=', 'm.right')
                    ;
                })
                ->where('m.id', '=', $query['map_id'])
                ->get()
                ->first()
            ;

            if (!$map) {
                throw new InvalidArgumentException('没有该地图', 400);
            }

            $npc_type = DB::query()
                ->select([
                    'type as id',
                ])
                ->from('npc')
                ->groupBy('type')
                ->get()
                ->each(function ($type){
                    $type->name = npc::getNameByType($type->id);
                })
            ;

            $enemy_level = DB::query()
                ->select([
                    'level as id',
                    'level as name',
                ])
                ->from('enemy')
                ->groupBy('level')
                ->get()
            ;

            $map_area = DB::query()
                ->select([
                    'id',
                    'name',
                ])
                ->from('map_area')
                ->get()
            ;

            return Response::view('admin/map/edit', [
                'row' => $map,
                'npc_type'    => $npc_type,
                'enemy_level' => $enemy_level,
                'map_area'    => $map_area,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 更新
    public function update()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'map_id'  => ['required'],
                'name'    => ['required'],
                'area_id' => ['required'],
            ], [
                'map_id.required'  => 'id 不能为空',
                'name.required'    => '名称不能为空',
                'area_id.required' => '区域不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $row = DB::query()
                ->select([
                    'id',
                    'name'
                ])
                ->from('map')
                ->where('id', '=', $query['map_id'])
                ->limit(1)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('地图不存在', 400);
            }

            if ($row->name != $query['name']) {
                $map = DB::query()
                    ->select(['id'])
                    ->from('map')
                    ->where('name', '=', $query['name'])
                    ->where('area', '=', $query['area_id'])
                    ->limit(1)
                    ->get()
                    ->first()
                ;

                if ($map) {
                    throw new InvalidArgumentException('地图已存在', 400);
                }
            }

            DB::table('map')
                ->where('id', '=', $query['map_id'])
                ->update([
                    'name'        => $query['name'],
                    'npc_id'      => $query['npc_id'] ?? 0,
                    'enemy_id'    => $query['enemy_id'] ?? 0,
                    'description' => $query['description'] ?? '',
                    'up'          => $query['up'] ?? 0,
                    'down'        => $query['down'] ?? 0,
                    'left'        => $query['left'] ?? 0,
                    'right'       => $query['right'] ?? 0,
                    'forward'     => $query['forward'] ?? 0,
                    'behind'      => $query['behind'] ?? 0,
                    'area'        => $query['area_id'],
                    'is_activity' => $query['is_activity'] ?? 0,
                ])
            ;

            return Response::json([
                'code'    => 200,
                'message' => '成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 删除
    public function delete()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'map_id' => ['nullable', 'integer'],
            ], [
                'map_id.required' => 'id 不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            DB::table('map')
                ->where('id', '=', $query['map_id'])
                ->delete()
            ;

            return Response::json([
                'code'    => 200,
                'message' => '成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
