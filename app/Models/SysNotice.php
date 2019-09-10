<?php

namespace App\Models;

use App\Events\PPUserCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class SysNotice
{
    public static function statusToStr($status)
    {
        switch ($status) {
            case 0:
                $status = '不推送';

                break;
            case 200:
                $status = '正常';

                break;
            default:
                $status = '未知状态';

                break;
        }

        return $status;
    }
}
