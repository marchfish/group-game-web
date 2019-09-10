<?php

namespace App\Support\Facades;

use App\Services\Wxpay\WxpayManager;
use Illuminate\Support\Facades\Facade;

class Wxpay extends Facade
{
    protected static function getFacadeAccessor()
    {
        return WxpayManager::class;
    }
}
