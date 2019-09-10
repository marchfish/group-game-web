<?php

namespace App\Support\Facades;

use App\Services\Message\MessageManager;
use Illuminate\Support\Facades\Facade;

class Message extends Facade
{
    protected static function getFacadeAccessor()
    {
        return MessageManager::class;
    }
}
