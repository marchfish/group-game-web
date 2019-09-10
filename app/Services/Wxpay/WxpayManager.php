<?php

namespace App\Services\Wxpay;

use App\Support\Facades\HttpClient;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class WxpayManager
{
    private $config;

    public function __construct()
    {
        $this->config = Config::get('wxpay');
    }

    /**
     * App 支付.
     *
     * @see  https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_1
     *
     * @param string $client - zpp,pp
     * @param array  $params
     *                       [
     *                       'body'         => '跑费',
     *                       'out_trade_no' => gene_no(),
     *                       'total_fee'    => '1',
     *                       ]
     */
    public function app(string $client, array $params)
    {
        try {
            $query = [
                'appid'            => $this->config[$client]['appid'],
                'mch_id'           => $this->config[$client]['mch_id'],
                'nonce_str'        => Str::random(16),
                'body'             => $params['body'],
                'out_trade_no'     => $params['out_trade_no'],
                'total_fee'        => $params['total_fee'],
                'spbill_create_ip' => $_SERVER['SERVER_ADDR'],
                'notify_url'       => $this->config[$client]['notify_url'],
                'trade_type'       => 'APP',
            ];

            $query['sign'] = Helper::sign($query, $this->config[$client]['key']);

            $res = Helper::parseXml(HttpClient::request('post', 'https://api.mch.weixin.qq.com/pay/unifiedorder', [
                'body' => Helper::buildXml($query),
            ])->getBody()->getContents());

            if ($res['return_code'] != 'SUCCESS' || $res['result_code'] != 'SUCCESS') {
                throw new InvalidArgumentException('微信App支付失败', 400);
            }

            $result = [
                'appid'     => $this->config[$client]['appid'],
                'partnerid' => $this->config[$client]['mch_id'],
                'prepayid'  => $res['prepay_id'],
                'package'   => 'Sign=WXPay',
                'noncestr'  => Str::random(16),
                'timestamp' => (string) Carbon::now()->getTimestamp(),
            ];

            $result['sign'] = Helper::sign($result, $this->config[$client]['key']);

            return $result;
        } catch (InvalidArgumentException $e) {
            Log::error($e->getMessage(), [
                'url'  => 'https://api.mch.weixin.qq.com/pay/unifiedorder',
                'body' => $query,
                'res'  => $res,
            ]);

            throw $e;
        }
    }

    public function refund(string $client, array $params)
    {
        try {
            $query = [
                'appid'         => $this->config[$client]['appid'],
                'mch_id'        => $this->config[$client]['mch_id'],
                'nonce_str'     => Str::random(16),
                'out_trade_no'  => $params['out_trade_no'],
                'out_refund_no' => $params['out_refund_no'],
                'total_fee'     => $params['total_fee'],
                'refund_fee'    => $params['refund_fee'],
                'notify_url'    => $this->config[$client]['notify_refund_url'],
            ];

            $query['sign'] = Helper::sign($query, $this->config[$client]['key']);

            $res = Helper::parseXml(HttpClient::request('post', 'https://api.mch.weixin.qq.com/secapi/pay/refund', [
                'body'    => Helper::buildXml($query),
                'cert'    => $this->config[$client]['cert'],
                'ssl_key' => $this->config[$client]['ssl_key'],
            ])->getBody()->getContents());

            if ($res['return_code'] != 'SUCCESS' || $res['result_code'] != 'SUCCESS') {
                throw new InvalidArgumentException('微信退款失败', 400);
            }

            return $res;
        } catch (InvalidArgumentException $e) {
            Log::error($e->getMessage(), [
                'url'  => 'https://api.mch.weixin.qq.com/secapi/pay/refund',
                'body' => $query,
                'res'  => $res,
            ]);

            throw $e;
        }
    }

    public function transfer($params)
    {
        $query = [
            'mch_appid'        => $this->config['gzh']['appid'],
            'mchid'            => $this->config['zpp']['mchid'],
            'nonce_str'        => Str::random(16),
            'partner_trade_no' => $params['partner_trade_no'],
            'openid'           => $params['openid'],
            'check_name'       => 'NO_CHECK',
            'amount'           => $params['amount'],
            'desc'             => $params['desc'],
            'spbill_create_ip' => $_SERVER['SERVER_ADDR'],
        ];

        // 收款用户真实姓名。 如果check_name设置为FORCE_CHECK，则必填用户真实姓名
        if (isset($params['re_user_name'])) {
            $query['check_name']   = 'FORCE_CHECK';
            $query['re_user_name'] = $params['re_user_name'];
        }

        $query['sign'] = Helper::sign($query, $this->config['zpp']['key']);

        $res = Helper::parseXml($this->client->request('post', 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers', [
            'body'    => Helper::buildXml($query),
            'cert'    => $this->config['zpp']['cert'],
            'ssl_key' => $this->config['zpp']['ssl_key'],
        ])->getBody()->getContents());

        return $res;
    }

    public function verify(string $client, string $notify_content): array
    {
        $query = Helper::parseXml($notify_content);

        if (!isset($query['sign'])) {
            throw new InvalidArgumentException('sign is required.', 400);
        }

        if (!Helper::verifySign($query, $this->config[$client]['key'])) {
            throw new InvalidArgumentException('verify failed.', 400);
        }

        return $query;
    }

    public function verifyRefund(string $client, string $notify_content): array
    {
        $query = Helper::parseXml($notify_content);

        if ($this->config[$client]['appid'] != $query['appid']) {
            throw new InvalidArgumentException('appid not match');
        }

        $xml = openssl_decrypt(base64_decode($query['req_info']), 'aes-256-ecb', md5($this->config[$client]['key']), OPENSSL_RAW_DATA);

        if (!$xml) {
            throw new InvalidArgumentException('req_info decrypt failed');
        }

        return Helper::parseXml($xml);
    }

    public function success(): string
    {
        return Helper::buildXml([
            'return_code' => 'SUCCESS',
            'return_msg'  => 'OK',
        ]);
    }
}
