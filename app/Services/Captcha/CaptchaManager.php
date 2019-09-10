<?php

namespace App\Services\Captcha;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use InvalidArgumentException;

class CaptchaManager
{
    private $captcha;

    public function __construct()
    {
        $this->captcha = new CaptchaBuilder((new PhraseBuilder())->build(4));
    }

    public function __call($method, $parameters)
    {
        return $this->captcha->{$method}(...$parameters);
    }
}
