<?php

namespace App\Services\AppSession;

use Illuminate\Support\Arr;

class Repository
{
    private $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function has(string $key)
    {
        return Arr::has($this->items, $key);
    }

    public function get(string $key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    public function put(string $key, $value = null)
    {
        return Arr::set($this->items, $key, $value);
    }

    public function remove(string $key)
    {
        return Arr::pull($this->items, $key);
    }

    public function all()
    {
        return $this->items;
    }

    public function setItems(array $items)
    {
        $this->items = $items;

        return $this;
    }

    public function flush()
    {
        $this->items = [];

        return $this;
    }
}
