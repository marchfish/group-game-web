<?php

namespace App\Services\Wechat;

use App\Support\Facades\HttpClient;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;

class WechatManager
{
    private $config;

    public function __construct()
    {
        $this->config = Config::get('wechat');
    }

    public function menu()
    {
        return json_decode(HttpClient::request('get', 'https://api.weixin.qq.com/cgi-bin/menu/get', [
            'query' => [
                'access_token' => $this->accessToken(),
            ],
        ])->getBody()->getContents(), true);
    }

    public function menuCreate(array $body)
    {
        return json_decode(HttpClient::request('post', 'https://api.weixin.qq.com/cgi-bin/menu/create', [
            'query' => [
                'access_token' => $this->accessToken(),
            ],
            'body' => json_encode($body, JSON_UNESCAPED_UNICODE),
        ])->getBody()->getContents(), true);
    }

    public function webAuthUrl(array $query)
    {
        $query = [
            'appid'         => $this->config['gzh_appid'],
            'redirect_uri'  => $query['redirect_uri'],
            'response_type' => 'code',
            'scope'         => $query['scope'],
            'state'         => $query['state'] ?? '',
        ];

        return 'https://open.weixin.qq.com/connect/oauth2/authorize?' . build_qs($query) . '#wechat_redirect';
    }

    public function webAccessToken(string $code)
    {
        return json_decode(HttpClient::request('get', 'https://api.weixin.qq.com/sns/oauth2/access_token', [
            'query' => [
                'appid'      => $this->config['gzh_appid'],
                'secret'     => $this->config['gzh_appsecret'],
                'code'       => $code,
                'grant_type' => 'authorization_code',
            ],
        ])->getBody()->getContents(), true);
    }

    public function webUserinfo(array $query)
    {
        return json_decode(HttpClient::request('get', 'https://api.weixin.qq.com/sns/userinfo', [
            'query' => [
                'access_token' => $query['access_token'],
                'openid'       => $query['openid'],
                'lang'         => 'zh_CN',
            ],
        ])->getBody()->getContents(), true);
    }

    public function accessToken()
    {
        if (Storage::exists($this->config['gzh_access_token_filename'])) {
            $res = json_decode(Storage::get($this->config['gzh_access_token_filename']), true);

            if (!isset($res['expired_at']) || Carbon::now()->subSeconds(60)->greaterThan($res['expired_at'])) {
                $res = $this->requestAccessTokenAndSave();
            }
        } else {
            $res = $this->requestAccessTokenAndSave();
        }

        return $res['access_token'];
    }

    public function jsConfig(string $url = '')
    {
        $query = [
            'noncestr'     => Str::random(16),
            'jsapi_ticket' => $this->jsApiTicket(),
            'timestamp'    => Carbon::now()->getTimestamp(),
            'url'          => $url ?: URL::to($_SERVER['REQUEST_URI']),
        ];

        ksort($query, SORT_STRING);

        $temp = [];

        foreach ($query as $k => $v) {
            if ($v) {
                $temp[] = $k . '=' . $v;
            }
        }

        $s = join('&', $temp);

        return [
            'appid'     => $this->config['gzh_appid'],
            'noncestr'  => $query['noncestr'],
            'timestamp' => $query['timestamp'],
            'signature' => sha1($s),
        ];
    }

    private function jsApiTicket()
    {
        if (Storage::exists($this->config['gzh_jsapi_ticket_filename'])) {
            $res = json_decode(Storage::get($this->config['gzh_jsapi_ticket_filename']), true);

            if (!isset($res['expired_at']) || Carbon::now()->subSeconds(60)->greaterThan($res['expired_at'])) {
                $res = $this->requestJsApiTicket();
            }
        } else {
            $res = $this->requestJsApiTicket();
        }

        return $res['ticket'];
    }

    private function requestAccessTokenAndSave()
    {
        $res = json_decode(HttpClient::request('get', 'https://api.weixin.qq.com/cgi-bin/token', [
            'query' => [
                'appid'      => $this->config['gzh_appid'],
                'secret'     => $this->config['gzh_appsecret'],
                'grant_type' => 'client_credential',
            ],
        ])->getBody()->getContents(), true);

        $res['expired_at'] = Carbon::now()->addSeconds($res['expires_in'])->format('Y-m-d H:i:s');

        Storage::put($this->config['gzh_access_token_filename'], json_encode($res, JSON_PRETTY_PRINT));

        return $res;
    }

    private function requestJsApiTicket()
    {
        $res = json_decode(HttpClient::request('get', 'https://api.weixin.qq.com/cgi-bin/ticket/getticket', [
            'query' => [
                'access_token' => $this->accessToken(),
                'type'         => 'jsapi',
            ],
        ])->getBody()->getContents(), true);

        $res['expired_at'] = Carbon::now()->addSeconds($res['expires_in'])->format('Y-m-d H:i:s');

        Storage::put($this->config['gzh_jsapi_ticket_filename'], json_encode($res, JSON_PRETTY_PRINT));

        return $res;
    }

    public function success()
    {
        return 'success';
    }
}
