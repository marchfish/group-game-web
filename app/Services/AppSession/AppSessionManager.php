<?php

namespace App\Services\AppSession;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Contracts\Encryption\DecryptException;
use InvalidArgumentException;

class AppSessionManager
{
    private $config;

    private $repository;

    private $meta;

    public function __construct()
    {
        $this->config     = Config::get('app_session');
        $this->repository = new Repository();
        $this->meta       = [
            'type' => '',
            'id'   => '',
        ];
    }

    public function __call($method, $paramters)
    {
        return $this->repository->{$method}(...$paramters);
    }

    public function meta()
    {
        return $this->meta;
    }

    public function setMetaType(string $type)
    {
        if (!$this->meta['type']) {
            $this->meta['type'] = $type;
        }

        return $this;
    }

    public function apiToken()
    {
        if (!$this->meta['type']) {
            throw new InvalidArgumentException('meta.type 不能为空');
        }

        return Crypt::encrypt($this->meta);
    }

    public function start(?string $token)
    {
        try {
            if (!$token) {
                throw new InvalidArgumentException('token 不能为空');
            }

            $meta = Crypt::decrypt($token);

            if (!isset($meta['type'], $meta['id'])) {
                throw new InvalidArgumentException('meta 数据异常');
            }

            $this->repository->setItems((array) json_decode(Redis::connection('app_session')->get($this->getKeyByMeta($meta)), true));

            $this->meta = [
                'type' => $meta['type'],
                'id'   => $meta['id'],
            ];
        } catch (InvalidArgumentException | DecryptException $e) {
            $this->meta['id'] = Str::random(8);
        }

        return $this;
    }

    public function save()
    {
        try {
            if (!$this->meta['type']) {
                throw new InvalidArgumentException('meta.type 不能为空');
            }

            Redis::connection('app_session')->setex(
                $this->getKeyByMeta($this->meta),
                $this->config['lifetime'],
                json_encode($this->repository->all())
            );
        } catch (InvalidArgumentException $e) {
        }

        return $this;
    }

    private function getKeyByMeta(array $meta): string
    {
        return $this->config['prefix'] . $meta['type'] . ':' . $meta['id'];
    }
}
