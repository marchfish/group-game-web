<?php

namespace App\Support\Facades;

use Illuminate\Support\Facades\Facade;
use App\Services\Mail\EmailManager;

class Email extends Facade
{
    protected static function getFacadeAccessor()
    {
        return EmailManager::class;
    }
}
