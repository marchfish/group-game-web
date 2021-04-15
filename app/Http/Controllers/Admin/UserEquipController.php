<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UserEquipController extends Controller
{
    public function index()
    {
        try {
            $query = Request::all();

            $model = DB::query()
                ->select([
//                    'ue.*',
                    'ur.user_id as user_id',
                    'ur.name as user_role_name',
                    'ur.level as user_role_level',
                    DB::raw('IFNULL(`iw`.`name`, "") AS `weapon_name`'),
                    DB::raw('IFNULL(`ih`.`name`, "") AS `helmet_name`'),
                    DB::raw('IFNULL(`ic`.`name`, "") AS `clothes_name`'),
                    DB::raw('IFNULL(`ie`.`name`, "") AS `earring_name`'),
                    DB::raw('IFNULL(`in`.`name`, "") AS `necklace_name`'),
                    DB::raw('IFNULL(`ib`.`name`, "") AS `bracelet_name`'),
                    DB::raw('IFNULL(`ir`.`name`, "") AS `ring_name`'),
                    DB::raw('IFNULL(`is`.`name`, "") AS `shoes_name`'),
                    DB::raw('IFNULL(`im`.`name`, "") AS `magic_weapon_name`'),
                ])
                ->from('user_equip AS ue')
                ->join('user_role AS ur', function ($join) {
                    $join
                        ->on('ur.id', '=', 'ue.user_role_id')
                    ;
                })
                ->leftJoin('item as iw', function ($join) {
                    $join
                        ->on('iw.id', '=', 'ue.weapon')
                    ;
                })
                ->leftJoin('item as ih', function ($join) {
                    $join
                        ->on('ih.id', '=', 'ue.helmet')
                    ;
                })
                ->leftJoin('item as ic', function ($join) {
                    $join
                        ->on('ic.id', '=', 'ue.clothes')
                    ;
                })
                ->leftJoin('item as ie', function ($join) {
                    $join
                        ->on('ie.id', '=', 'ue.earring')
                    ;
                })
                ->leftJoin('item as in', function ($join) {
                    $join
                        ->on('in.id', '=', 'ue.necklace')
                    ;
                })
                ->leftJoin('item as ib', function ($join) {
                    $join
                        ->on('ib.id', '=', 'ue.bracelet')
                    ;
                })
                ->leftJoin('item as ir', function ($join) {
                    $join
                        ->on('ir.id', '=', 'ue.ring')
                    ;
                })
                ->leftJoin('item as is', function ($join) {
                    $join
                        ->on('is.id', '=', 'ue.shoes')
                    ;
                })
                ->leftJoin('item as im', function ($join) {
                    $join
                        ->on('im.id', '=', 'ue.magic_weapon')
                    ;
                })
                ->orderBy('ur.level', 'desc')
            ;

            if (isset($query['name'])) {
                $model->where('ur.name', 'like', '%'. $query['name'] . '%');
            }

            if (isset($query['level'])) {
                $model->where('ur.level', 'like', '%'. $query['level'] . '%');
            }

            if (isset($query['user_id'])) {
                $model->where('ur.user_id', '=', $query['user_id']);
            }

            $paginate = $model->paginate($query['size']);

            return Response::view('admin/user-equip/index', [
                'paginate' => $paginate,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
