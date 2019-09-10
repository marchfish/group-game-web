<?php

namespace App\Services\Wxpay;

class Helper
{
    public static function parseXml(string $xml): array
    {
        $simpleXml = simplexml_load_string($xml);

        $query = [];

        if (!$simpleXml) {
            return $query;
        }

        foreach ($simpleXml as $k => $v) {
            $query[$k] = $v->__toString();
        }

        return $query;
    }

    public static function buildXml(array $query): string
    {
        $xml = '<xml>';

        foreach ($query as $k => $v) {
            $xml .= '<' . $k . '>' . $v . '</' . $k . '>';
        }

        $xml .= '</xml>';

        return $xml;
    }

    /**
     * @see https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=20_1
     *
     * @param array  $query
     * @param string $key
     */
    public static function sign(array $query, string $key): string
    {
        unset($query['sign']);
        ksort($query, SORT_STRING);

        $temp = [];

        foreach ($query as $k => $v) {
            if ($v) {
                $temp[] = $k . '=' . $v;
            }
        }

        $s = join('&', $temp);

        return strtoupper(md5($s . '&key=' . $key));
    }

    public static function verifySign(array $query, string $key): bool
    {
        return self::sign($query, $key) == $query['sign'];
    }
}
