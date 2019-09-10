<?php

namespace App\Support\Facades;

use App\Services\HttpClient\HttpClientManager;
use Illuminate\Support\Facades\Facade;

class HttpClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return HttpClientManager::class;
    }
}
