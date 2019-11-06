<?php

namespace App\Http\Controllers\Api;

use App\Models\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use InvalidArgumentException;

class UserRoleController extends Controller
{
    // 获取状态信息
    public function userRoleStatus()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $userRole = UserRole::getUserRoleToQQ($user_role->id);

            $res = '[当前状态]' . '\r\n';
            $res .= '角色名：' . $userRole->name . '\r\n';
            $res .= '血量：' . $userRole->hp . '\r\n';
            $res .= '蓝量：' . $userRole->mp . '\r\n';
            $res .= '攻击力：' . $userRole->attack . '\r\n';
            $res .= '魔力：' . $userRole->magic . '\r\n';
            $res .= '暴击：' . $userRole->crit . '\r\n';
            $res .= '闪避：' . $userRole->dodge . '\r\n';
            $res .= '防御力：' . $userRole->defense . '\r\n';
            $res .= '等级：' . $userRole->level . '\r\n';
            $res .= '经验：' . $userRole->exp . '\r\n';
            $res .= '称号：' . $userRole->fame_name . '\r\n';
            $res .= '当前位置：' . $userRole->map_name . '\r\n';
            $res .= '血量上限：' . $userRole->max_hp . '\r\n';
            $res .= '蓝量上限：' . $userRole->max_mp;

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
