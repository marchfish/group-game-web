<?php

namespace App\Support\Facades;

use App\Services\Sms\SmsManager;
use Illuminate\Support\Facades\Facade;

class Sms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SmsManager::class;
    }
}
