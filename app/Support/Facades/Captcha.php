<?php

namespace App\Support\Facades;

use App\Services\Captcha\CaptchaManager;
use Illuminate\Support\Facades\Facade;

class Captcha extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CaptchaManager::class;
    }
}
