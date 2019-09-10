<?php

namespace App\Support\Facades;

use App\Services\Wechat\WechatManager;
use Illuminate\Support\Facades\Facade;

class Wechat extends Facade
{
    protected static function getFacadeAccessor()
    {
        return WechatManager::class;
    }
}
