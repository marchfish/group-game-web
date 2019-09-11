<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Map
{
    public static function getLocationToMessage($row)
    {
        $data = '[当前位置]<br>' . $row->name . '<br>';

        if ($row->npc_name && $row->npc_name != '') {
            $data .= 'npc=' . $row->npc_name . '<br>';
        }

        if ($row->enemy_name && $row->enemy_name != '') {
            $data .= '怪物=' . $row->enemy_name . '<br> ';
        }

        if ($row->forward_name && $row->forward_name != '') {
            $data .= '前=' . $row->forward_name . '<br> ';
        }

        if ($row->behind_name && $row->behind_name != '') {
            $data .= '后=' . $row->behind_name . '<br> ';
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

        return $data;
    }
}
