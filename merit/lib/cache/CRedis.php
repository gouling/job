<?php

    namespace cache;

    class CRedis extends \Redis {
        const SYSTEM_STOP = 3;
        const SYSTEM_RECOVERY = 10;
        const SYSTEM_RELOAD = 12;
        const SYSTEM_TASK = [
            self::SYSTEM_STOP => '停止服务',
            self::SYSTEM_RECOVERY => '恢复异常',
            self::SYSTEM_RELOAD => '重载配置',
        ];

        const PREFIX_DATA = ':DATA';
        const PREFIX_FAIL = ':FAIL';

        public function __construct() {
            parent::__construct();

            if (parent::connect(\CApp::$Setting['cache']['host'], \CApp::$Setting['cache']['port'],
                \CApp::$Setting['cache']['timeout'])) {
                parent::select(\CApp::$Setting['cache']['db']);
                \CApp::$App->log->work(vsprintf('缓存：已就续，服务器：%s，端口：%s', [
                    \CApp::$Setting['cache']['host'],
                    \CApp::$Setting['cache']['port']
                ]));
            } else {
                throw new \Exception(vsprintf('缓存：不可用，服务器：%s，端口：%s', [
                    \CApp::$Setting['cache']['host'],
                    \CApp::$Setting['cache']['port']
                ]), 502);
            }
        }

        public function getTask($prefix) {
            if ($data = parent::hGetAll($prefix . self::PREFIX_DATA)) {
                foreach ($data as $k => $v) {
                    $data[$k] = json_decode($v, true);
                }
            }

            return $data;
        }

        public function finishTask($prefix, $field) {
            return parent::hDel($prefix . self::PREFIX_DATA, $field) > 0;
        }

        public function failTask($prefix, $field) {
            $dataKey = $prefix . self::PREFIX_DATA;
            $failKey = $prefix . self::PREFIX_FAIL;

            if ($data = parent::hGet($dataKey, $field)) {
                parent::multi();
                parent::hDel($dataKey, $field);
                parent::hSet($failKey, $field, $data);
                parent::exec();
                return true;
            }

            return false;
        }

        public function addTask($prefix, $data) {
            return parent::hSet($prefix . self::PREFIX_DATA, $this->__getHashField(), json_encode($data)) > 0;
        }

        public function addSystemTask($prefix, $signal) {
            $this->addTask($prefix, [
                'action' => 'system',
                'data' => $signal
            ]);
        }

        public function recoveryTask($prefix) {
            $dataKey = $prefix . self::PREFIX_DATA;
            $failKey = $prefix . self::PREFIX_FAIL;

            if ($data = parent::hGetAll($failKey)) {
                parent::multi();
                foreach ($data as $k => $v) {
                    parent::hDel($failKey, $k);
                    parent::hSet($dataKey, $k, $v);
                }
                parent::exec();
                return true;
            }

            return false;
        }

        private function __getHashField($length = 16) {
            if (function_exists('random_bytes')) {
                $bytes = random_bytes(ceil($length / 2));
            } else if (function_exists('openssl_random_pseudo_bytes')) {
                $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
            } else {
                $bytes = str_shuffle(uniqid('', true));
            }
            $bytes = substr(bin2hex($bytes), 0, $length);

            return substr(chunk_split($bytes, 4, '-'), 0, -1);
        }
    }