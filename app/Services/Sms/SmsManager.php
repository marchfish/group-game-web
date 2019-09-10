<?php

namespace App\Services\Sms;

use App\Support\Facades\HttpClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SmsManager
{
    private $config;

    public function __construct()
    {
        $this->config = Config::get('sms');
    }

    public function sendVerifyCode(string $tel)
    {
        $now = Carbon::now();

        if (App::environment('dev')) {
            $code = '1234';
        } else {
            $code = (string) mt_rand(1000, 9999);

            $res = json_decode(HttpClient::request('post', 'https://api.netease.im/sms/sendtemplate.action', [
                'headers' => [
                    'AppKey'   => $this->config['key'],
                    'Nonce'    => $code,
                    'CurTime'  => $now->getTimestamp(),
                    'CheckSum' => sha1($this->config['secret'] . $code . $now->getTimestamp()),
                ],
                'form_params' => [
                    'templateid' => '4023278',
                    'mobiles'    => json_encode([$tel]),
                    'params'     => json_encode([
                        '您的',
                        $code,
                    ]),
                ],
            ])->getBody()->getContents(), true);

            if ($res['code'] != 200) {
                throw new InvalidArgumentException('短信验证码发送失败: [' . $res['code'] . '] ' . $res['msg'], 400);
            }
        }

        DB::table('sys_smscode')->updateOrInsert([
            'tel' => $tel,
        ], [
            'code'       => $code,
            'expired_at' => $now->addSeconds($this->config['duration'])->format('Y-m-d H:i:s'),
        ]);
    }

    public function checkVerifyCode(string $tel, string $code)
    {
        $row = DB::query()
            ->select(['*'])
            ->from('sys_smscode')
            ->where('tel', '=', $tel)
            ->limit(1)
            ->get()
            ->first()
        ;

        if (!$row || Carbon::now()->greaterThan($row->expired_at)) {
            throw new InvalidArgumentException('短信验证码已过期，请重新获取', 400);
        }

        if ($row->code != $code) {
            throw new InvalidArgumentException('短信验证码错误', 400);
        }
    }

    public function setVerifyCodeExpired(string $tel)
    {
        DB::table('sys_smscode')
            ->where('tel', '=', $tel)
            ->update([
                'expired_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ])
        ;
    }
}
