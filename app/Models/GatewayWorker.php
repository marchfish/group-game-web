<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;
use App\Models\Enemy;
use App\Models\Item;
use App\Models\UserKnapsack;
use GatewayClient\Gateway;

class GatewayWorker
{
    public static function bindUid($client_id, $user_id)
    {
//        Gateway::bindUid($client_id, $user_id);
//
//        $data = [
//            'type'=>'test',
//            'message'=>'测试呀！'
//        ];

//        Gateway::sendToAll(json_encode($data));
    }
}
