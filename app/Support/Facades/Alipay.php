<?php

namespace App\Support\Facades;

use App\Services\Alipay\AlipayManager;
use Illuminate\Support\Facades\Facade;

class Alipay extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AlipayManager::class;
    }
}
