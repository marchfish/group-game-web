<?php

namespace App\Support\Facades;

use App\Services\AppSession\AppSessionManager;
use Illuminate\Support\Facades\Facade;

class AppSession extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AppSessionManager::class;
    }
}
