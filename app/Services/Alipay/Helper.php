<?php

namespace App\Services\Alipay;

class Helper
{
    public static function sign(array $query, string $keyPath): string
    {
        openssl_sign(self::queryToString($query), $sign, file_get_contents($keyPath), OPENSSL_ALGO_SHA256);

        return base64_encode($sign);
    }

    public static function verifySign(array $query, string $keyPath): bool
    {
        return openssl_verify(self::queryToString($query, true), base64_decode($query['sign']), file_get_contents($keyPath), OPENSSL_ALGO_SHA256) === 1;
    }

    public static function queryToString(array $query, bool $is_verify = false): string
    {
        unset($query['sign']);

        if ($is_verify) {
            unset($query['sign_type']);
        }
        ksort($query, SORT_STRING);

        $temp = [];

        foreach ($query as $k => $v) {
            if ($v) {
                $temp[] = $k . '=' . $v;
            }
        }

        return implode('&', $temp);
    }
}
