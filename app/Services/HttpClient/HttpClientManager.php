<?php

namespace App\Services\HttpClient;

use GuzzleHttp\Client;

class HttpClientManager
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function __call($method, $parameters)
    {
        return $this->client->{$method}(...$parameters);
    }
}
