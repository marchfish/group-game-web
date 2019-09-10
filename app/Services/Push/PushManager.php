<?php

namespace App\Services\Push;

use App\Support\Facades\HttpClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PushManager
{
    private $config;

    public function __construct()
    {
        $this->config = Config::get('push');
    }

    /**
     * @param array $params
     *                      [
     *                      'client' => 'zpp'
     *                      'user_ids' => [1, 2, 3],
     *                      'title' => '标题'
     *                      'content' => '内容',
     *                      ]
     */
    public function notify(array $params)
    {
        $this->checkParams($params);
        $params['type'] = 1;
        $token_lists    = $this->getLoginedTokenList($params['client'], $params['user_ids']);

        $this->sendIosXgNotify($token_lists['ios:xg'], $params);
        $this->sendAndroidXgNotify($token_lists['android:xg'], $params);
        $this->sendAndroidXgMessage($token_lists['android:xg'], $params);
        $this->save($params);
    }

    /**
     * @param array $params
     *                      [
     *                      'client' => 'zpp'
     *                      'user_ids' => [1, 2, 3],
     *                      'title' => '标题'
     *                      'content' => '内容',
     *                      ]
     */
    public function message(array $params)
    {
        $this->checkParams($params);
        $params['type'] = 2;
        $token_lists    = $this->getLoginedTokenList($params['client'], $params['user_ids']);

        $this->sendIosXgMessage($token_lists['ios:xg'], $params);
        $this->sendAndroidXgMessage($token_lists['android:xg'], $params);
        $this->save($params);
    }

    public function sendIosXgNotify(array $token_list, array $params)
    {
        if (empty($token_list)) {
            return;
        }

        $query = [
            'environment'   => App::environment('dev') ? 'dev' : 'product',
            'platform'      => 'ios',
            'audience_type' => 'token_list',
            'token_list'    => $token_list,
            'message_type'  => 'notify',
            'message'       => [
                'title'   => $params['title'],
                'content' => $params['content'],
                'ios'     => [
                    'aps' => [
                        'alert' => [
                            'subtitle' => '',
                        ],
                        'sound' => $params['sound'],
                    ],
                    // 自定义参数
                    'custom' => $params['extra'],
                ],
            ],
        ];

        $this->sendXg($params['client'], 'ios:xg', $query);
    }

    public function sendIosXgMessage(array $token_list, array $params)
    {
        if (empty($token_list)) {
            return;
        }

        $query = [
            'environment'   => App::environment('dev') ? 'dev' : 'product',
            'platform'      => 'ios',
            'audience_type' => 'token_list',
            'token_list'    => $token_list,
            'message_type'  => 'message',
            'message'       => [
                'title'   => $params['title'],
                'content' => $params['content'],
                'ios'     => [
                    'aps' => [
                        'content-available' => '1',
                    ],
                    // 自定义参数
                    'custom' => $params['extra'],
                ],
            ],
        ];

        $this->sendXg($params['client'], 'ios:xg', $query);
    }

    public function sendAndroidXgNotify(array $token_list, array $params)
    {
        if (empty($token_list)) {
            return;
        }

        foreach ($params['extra'] as $k => $v) {
            $params['extra'][$k] = (string) $v;
        }

        $query = [
            'multi_pkg'     => true,
            'platform'      => 'android',
            'audience_type' => 'token_list',
            'token_list'    => $token_list,
            'message_type'  => 'notify',
            'message'       => [
                'title'   => $params['title'],
                'content' => $params['content'],
                'android' => [
                    // 自定义参数
                    'custom_content' => $params['extra'],
                ],
            ],
        ];

        $this->sendXg($params['client'], 'android:xg', $query);
    }

    public function sendAndroidXgMessage(array $token_list, array $params)
    {
        if (empty($token_list)) {
            return;
        }

        foreach ($params['extra'] as $k => $v) {
            $params['extra'][$k] = (string) $v;
        }

        $params['extra']['title']   = $params['title'];
        $params['extra']['content'] = $params['content'];

        $query = [
            'multi_pkg'     => true,
            'platform'      => 'android',
            'audience_type' => 'token_list',
            'token_list'    => $token_list,
            'message_type'  => 'message',
            'message'       => [
                'title' => '',
                // 华为手机透传不能自定义参数, 全部放到 content 里面
                'content' => json_encode($params['extra'], JSON_UNESCAPED_UNICODE),
                // 'android' => [
                //     // 自定义参数
                //     'custom_content' => $params['extra'],
                // ],
            ],
        ];

        $this->sendXg($params['client'], 'android:xg', $query);
    }

    public function getLoginedTokenList(string $client, array $user_ids)
    {
        $push_token  = $client . '_push_token';
        $client_type = $client . '_client_type';

        $rows = DB::query()
            ->select([
                $push_token,
                $client_type,
            ])
            ->from('user_extend')
            ->whereIn('user_id', $user_ids)
            ->where('is_' . $client . '_logined', '=', 1)
            ->get()
        ;

        $token_lists = [
            'ios:xg'     => [],
            'android:xg' => [],
        ];

        foreach ($rows as $row) {
            if (strlen($row->{$push_token}) > 20) {
                $token_lists[$row->{$client_type}][] = $row->{$push_token};
            }
        }

        return $token_lists;
    }

    private function sendXg(string $client, string $client_type, array $query)
    {
        try {
            $res = HttpClient::request('post', 'https://openapi.xg.qq.com/v3/push/app', [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($this->config[$client][$client_type]['appid'] . ':' . $this->config[$client][$client_type]['secret_key']),
                ],
                'body' => json_encode($query),
            ])->getBody()->getContents();

            return $res;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::errer('push-api-err:' . $e->getMessage());
        }
    }

    private function checkParams(&$params)
    {
        foreach ($params['user_ids'] as &$user_id) {
            $user_id = (int) $user_id;
        }

        if (isset($params['extra']) && is_array($params['extra'])) {
            $params['extra']['app_name'] = 'zpp';
        } else {
            $params['extra'] = [
                'app_name' => 'zpp',
            ];
        }

        if (isset($params['extra']['type']) && $params['extra']['type'] == 'order-new') {
            $params['sound'] = 'neworder.caf';
        } else {
            $params['sound'] = 'default';
        }
    }

    private function save(array $params)
    {
        DB::table('sys_push')->insert([
            'type'    => $params['type'],
            'client'  => $params['client'] == 'zpp' ? 1 : 2,
            'payload' => json_encode($params, JSON_UNESCAPED_UNICODE),
            'status'  => 200,
        ]);
    }
}
