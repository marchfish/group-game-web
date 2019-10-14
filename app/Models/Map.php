<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class Map
{
    public static function getLocationToMessage($row)
    {
        $data = '[当前位置]<br><span class="wr-color-999">' . $row->name . '</span><br>';

        if ($row->npc_name && $row->npc_name != '') {
            $data .= 'npc=' . $row->npc_name;

            if ($row->mission_id > 0) {
                $data .= '<input type="button" class="action" data-url="' . URL::to('mission') . "?mission_id=" . $row->mission_id . '" value="任务" />';
            }

            $data .= '<br>';
        }

        if ($row->enemy_name && $row->enemy_name != '') {
            $data .= '<span class="wr-color-E53E27">怪物=' . $row->enemy_name . '</span><br> ';
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
}
