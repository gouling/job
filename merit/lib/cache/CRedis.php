<?php

    namespace cache;

    class CRedis extends \Redis {
        const POSITION_HEAD = 'HEAD';
        const POSITION_TAIL = 'TAIL';

        const SYSTEM_STOP = 3;
        const SYSTEM_RECOVERY = 10;
        const SYSTEM_RELOAD = 12;

        const SYSTEM_TASK = [
            self::SYSTEM_STOP => '停止服务',
            self::SYSTEM_RECOVERY => '恢复异常',
            self::SYSTEM_RELOAD => '重载配置',
        ];

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

        public function addTask($prefix, $data, $position = self::POSITION_TAIL) {
            switch ($position) {
                case self::POSITION_HEAD:
                    return parent::lPush($prefix, json_encode($data)) > 0;
                case self::POSITION_TAIL:
                    return parent::rPush($prefix, json_encode($data)) > 0;
            }
        }

        public function getTask($prefix, $position = self::POSITION_HEAD) {
            switch ($position) {
                case self::POSITION_HEAD:
                    return json_decode(parent::lPop($prefix), true);
                case self::POSITION_TAIL:
                    return json_decode(parent::rPop($prefix), true);
            }
        }

        public function addSystemTask($prefix, $signal) {
            $this->addTask($prefix, [
                'action' => 'system',
                'data' => $signal
            ], self::POSITION_HEAD);
        }
    }