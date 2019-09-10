<?php

namespace App\Services\Alipay;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use InvalidArgumentException;

class AlipayManager
{
    private $config;

    public function __construct()
    {
        $this->config = Config::get('alipay');
    }

    /**
     * App 支付.
     *
     * @see  https://docs.open.alipay.com/api_1/alipay.trade.app.pay
     *
     * @param array $params
     *                      [
     *                      'subject'      => '跑费',
     *                      'out_trade_no' => gene_no(),
     *                      'total_amount' => '1',
     *                      ]
     */
    public function app(array $params): string
    {
        $params['total_amount'] = bcdiv($params['total_amount'], 100, 2);

        $query = [
            'app_id'      => $this->config['app_id'],
            'method'      => 'alipay.trade.app.pay',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'timestamp'   => Carbon::now()->format('Y-m-d H:i:s'),
            'version'     => '1.0',
            'notify_url'  => $this->config['notify_url'],
            'biz_content' => json_encode($params),
            // 'biz_content' => json_encode(array_merge($params, [
            //     'product_code' => 'QUICK_MSECURITY_PAY',
            // ])),
        ];

        $query['sign'] = Helper::sign($query, $this->config['private_key']);

        return http_build_query($query);
    }

    public function verify(array $query): array
    {
        if (!isset($query['sign'])) {
            throw new InvalidArgumentException('sign is required.', 400);
        }

        if (!Helper::verifySign($query, $this->config['public_key'])) {
            throw new InvalidArgumentException('verify failed.', 400);
        }

        return $query;
    }

    public function success(): string
    {
        return 'success';
    }
}
