<?php

namespace App\Models;

class Npc
{
    public static function getNameByType($type)
    {
        switch ($type)
        {
            case 0:
                return '任务';
                break;
            case 10:
                return '商店';
                break;
            case 20:
                return '合成店';
                break;
            case 30:
                return '提炼';
                break;
            case 40:
                return '更换';
                break;
            default:
                return '其它';
                break;
        }
    }
}
