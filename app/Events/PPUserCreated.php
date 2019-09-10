<?php

namespace App\Events;

class PPUserCreated
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function data()
    {
        return $this->data;
    }
}
