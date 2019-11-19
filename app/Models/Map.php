<?php

namespace App\Models;

use Illuminate\Support\Facades\URL;

class Map
{
    public static function getLocationToMessage($row)
    {
        $data = '[当前位置]<br><span class="wr-color-999">' . $row->name . '</span><br>';

        if ($row->npc_name && $row->npc_name != '') {
            $data .= 'npc=' . $row->npc_name;

            if ($row->mission_id > 0) {
                $data .= ' <input type="button" class="action" data-url="' . URL::to('mission') . "?mission_id=" . $row->mission_id . '" value="任务" />';
            }

            if ($row->npc_type == 10){
                $data .= ' <input type="button" class="action" data-url="' . URL::to('shop') . "?npc_id=" . '0' . '" value="商店" />';
            }

            if ($row->npc_type == 20){
                $data .= ' <input type="button" class="action" data-url="' . URL::to('synthesis') . "?npc_id=" . '0' . '" value="合成" />';
            }

            if ($row->npc_type == 30){
                $data .= ' <input type="button" class="action" data-url="' . URL::to('refine') . "?npc_id=" . '0' . '" value="提炼" />';
            }

            $data .= '<br>';
        }

        if ($row->enemy_name && $row->enemy_name != '') {
            if ($row->enemy_hour != '' && strpos($row->enemy_hour, date('H', time())) === false) {

            }else {
                if ($row->enemy_refresh_at != '' &&  time() < strtotime($row->enemy_refresh_at)) {
                    $data .= '<span class="wr-color-E53E27">怪物刷新时间：' . date('H:i:s', strtotime($row->enemy_refresh_at)) . '</span><br> ';
                }else {
                    if($row->enemy_img && $row->enemy_img != '') {
                        $data .= '<img style="width:40px" src="'. upload_url($row->enemy_img) .'" alt="" >';
                    }

                    $data .= '<span class="wr-color-E53E27">怪物=' . $row->enemy_name . '：' . $row->enemy_level . '级</span><br> ';
                }
            }
        }

        if ($row->description && $row->description != '') {
            $data .= '描述=' . $row->description . '<br> ';
        }

        if ($row->up_name && $row->up_name != '') {
            $data .= '上=' . $row->up_name . '<br> ';
        }

        if ($row->down_name && $row->down_name != '') {
            $data .= '下=' . $row->down_name . '<br> ';
        }

        if ($row->left_name && $row->left_name != '') {
            $data .= '左=' . $row->left_name . '<br> ';
        }

        if ($row->right_name && $row->right_name != '') {
            $data .= '右=' . $row->right_name . '<br> ';
        }

        if ($row->forward_name && $row->forward_name != '') {
            $data .= '前=' . $row->forward_name . '<br> ';
        }

        if ($row->behind_name && $row->behind_name != '') {
            $data .= '后=' . $row->behind_name . '<br> ';
        }

        return $data;
    }

    public static function getLocationToQQ($row)
    {
        $data = '[当前位置]\r\n' . $row->name . '\r\n';

        if ($row->npc_name && $row->npc_name != '') {
            $data .= 'npc=' . $row->npc_name;

            if ($row->mission_id > 0) {
                $data .= ' -- (任务)';
            }

            if ($row->npc_type == 10){
                $data .= ' -- (商店)';
            }

            if ($row->npc_type == 20){
                $data .= ' -- (合成)';
            }

            if ($row->npc_type == 30){
                $data .= ' -- (提炼)';
            }

            $data .= '\r\n';
        }

        if ($row->enemy_name && $row->enemy_name != '') {
            if ($row->enemy_hour != '' && strpos($row->enemy_hour, date('H', time())) === false) {

            }else {
                if ($row->enemy_refresh_at != '' &&  time() < strtotime($row->enemy_refresh_at)) {
                    $data .= '怪物刷新时间：' . date('H:i:s', strtotime($row->enemy_refresh_at)) . '\r\n';
                }else {
                    $data .= '怪物=' . $row->enemy_name . '：' . $row->enemy_level . '级\r\n';
                }
            }
        }

        if ($row->description && $row->description != '') {
            $data .= '描述=' . $row->description . '\r\n';
        }

        if ($row->up_name && $row->up_name != '') {
            $data .= '上=' . $row->up_name . '\r\n';
        }

        if ($row->down_name && $row->down_name != '') {
            $data .= '下=' . $row->down_name . '\r\n';
        }

        if ($row->left_name && $row->left_name != '') {
            $data .= '左=' . $row->left_name . '\r\n';
        }

        if ($row->right_name && $row->right_name != '') {
            $data .= '右=' . $row->right_name . '\r\n';
        }

        if ($row->forward_name && $row->forward_name != '') {
            $data .= '前=' . $row->forward_name . '\r\n';
        }

        if ($row->behind_name && $row->behind_name != '') {
            $data .= '后=' . $row->behind_name . '\r\n';
        }

        $data .= '注：请根据方位进行移动';

        return $data;
    }
}
