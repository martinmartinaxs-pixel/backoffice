<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class RedisService
{
    protected $redis;
    protected $prefix;
    public function __construct()
    {
        $this->redis = Redis::connection();
        $this->prefix = env('REDIS_PREFIX');
    }

    public function setPgGameSetting($user, $game, $setting) {
        try {
            $key = 'pg:setting:game:'.$game.':user:'.$user;
            $this->redis->set($key, json_encode($setting), 'EX', 60*60*24*7);
        } catch (\Exception $exception) {

        }
        return null;
    }

    public function getPgGameSetting($user, $game) {
        try {
            $key = 'pg:setting:game:'.$game.':user:'.$user;
            $setting = $this->redis->get($key);
            if ($setting) {
                $setting = json_decode($setting);
                $this->setPgGameSetting($user, $game, $setting);
                return $setting;
            }
        } catch (\Exception $exception) {

        }
        return null;
    }

    public function setSeamlessSetting($otk, $setting) {
        try {
            $key = 'pg:seamless:otk:'.$otk;
            $this->redis->set($key, json_encode($setting), 'EX', 60*60*24*7);
        } catch (\Exception $exception) {

        }
        return null;
    }

    public function getSeamlessSetting($otk) {
        try {
            $key = 'pg:seamless:otk:'.$otk;
            $setting = $this->redis->get($key);
            if ($setting) {
                $setting = json_decode($setting);
                $this->setSeamlessSetting($otk, $setting);
                return $setting;
            }
        } catch (\Exception $exception) {

        }
        return null;
    }

    public function setUserData($token, $data) {
        try {
            $key = 'pg:user:data:'.$token;
            $this->redis->set($key, json_encode($data), 'EX', 60*60);
        } catch (\Exception $exception) {

        }
        return null;
    }

    public function getUserData($token) {
        try {
            $key = 'pg:user:data:'.$token;
            $setting = $this->redis->get($key);
            if ($setting) {
                $setting = json_decode($setting);
                $this->setUserData($token, $setting);
                return $setting;
            }
        } catch (\Exception $exception) {

        }
        return null;
    }

    public function setPlayerNameData($token, $data) {
        try {
            $key = 'pg:player:data:'.$token;
            $this->redis->set($key, json_encode($data), 'EX', 60*60);
        } catch (\Exception $exception) {

        }
        return null;
    }

    public function getPlayerNameData($token) {
        try {
            $key = 'pg:player:data:'.$token;
            $setting = $this->redis->get($key);
            if ($setting) {
                $setting = json_decode($setting);
                $this->setPlayerNameData($token, $setting);
                return $setting;
            }
        } catch (\Exception $exception) {

        }
        return null;
    }
}
