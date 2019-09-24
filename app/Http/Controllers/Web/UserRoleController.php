<?php

namespace App\Http\Controllers\Web;

use App\Models\Map;
use App\Models\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class UserRoleController extends Controller
{
    // 获取状态信息
    public function userRoleStatus()
    {
        try {
            $userRole = UserRole::getUserRole();

            $res = '[当前状态]' . '<br>';
            $res .= '角色名：' . $userRole->name .'<br>';
            $res .= '血量：' . $userRole->hp .'<br>';
            $res .= '蓝量：' . $userRole->mp .'<br>';
            $res .= '攻击力：' . $userRole->attack .'<br>';
            $res .= '魔力：' . $userRole->magic .'<br>';
            $res .= '暴击：' . $userRole->crit .'<br>';
            $res .= '闪避：' . $userRole->dodge .'<br>';
            $res .= '防御力：' . $userRole->defense .'<br>';
            $res .= '等级：' . $userRole->level .'<br>';
            $res .= '经验：' . $userRole->exp .'<br>';
            $res .= '称号：' . $userRole->fame_name .'<br>';
            $res .= '当前位置：' . $userRole->map_name .'<br>';
            $res .= '血量上限：' . $userRole->max_hp .'<br>';
            $res .= '蓝量上限：' . $userRole->max_mp .'<br>';

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
