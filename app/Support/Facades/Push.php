<?php

namespace App\Support\Facades;

use App\Services\Push\PushManager;
use Illuminate\Support\Facades\Facade;

class Push extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PushManager::class;
    }
}
