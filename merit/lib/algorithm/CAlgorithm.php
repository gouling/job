<?php

    namespace algorithm;

    class CAlgorithm {
        private $__data, $__prefix;

        public function set($data) {
            $this->__chkArgs($data);
            $this->__setPrefix();

            $this->__signal();
            $this->__listen();
        }

        private function __chkArgs($data) {
            if(!isset($data['platformId'])) {
                throw new \Exception('平台参数(platformId)不可用', 404);
            } else if(!isset(\CApp::$Setting['algorithm']['data']['listen'][$data['platformId']])) {
                throw new \Exception('平台参数(platformId)不支持', 501);
            }

            $this->__data = $data;
        }

        private function __listen() {
            while (true) {
                pcntl_signal_dispatch();

                if ($task = \CApp::$App->cache->getTask($this->__prefix)) {
                    switch ($task['action']) {
                        case 'system':
                            if(isset(\cache\CRedis::SYSTEM_TASK[$task['data']])) {
                                \CApp::$App->log->work('指令：'.\cache\CRedis::SYSTEM_TASK[$task['data']]);
                                switch ($task['data']) {
                                    case \cache\CRedis::SYSTEM_STOP:
                                        break 3;
                                    case \cache\CRedis::SYSTEM_RECOVERY:
                                        break;
                                    case \cache\CRedis::SYSTEM_RELOAD:
                                        break;
                                }
                            } else {
                                \CApp::$App->log->work('指令：目前不支持此指令控制');
                            }
                            break;
                        case 'borrow.set':
                            \CApp::$App->log->work("官方债权匹配数据".PHP_EOL.print_r($task, true));
                            break;
                        case 'user.get':
                            \CApp::$App->log->work("用户提现转让数据".PHP_EOL.print_r($task, true));
                            break;
                        default:
                            \CApp::$App->log->work("不支持的任务数据");
                    }
                }

                usleep(100000);
            }
        }

        private function __signal() {
            $pSignal = function($signal) {
                switch ($signal) {
                    case SIGUSR1:
                        //KILL -10
                        break;
                    case SIGUSR2:
                        //KILL -12
                        break;
                    case SIGINT:
                        //CTRL+C
                    case SIGTERM:
                        //KILL -15
                    case SIGQUIT:
                        //KILL -3
                    default:
                        $signal = 3;
                        break;
                }

                if(isset(\cache\CRedis::SYSTEM_TASK[$signal])) {
                    \CApp::$App->cache->addSystemTask($this->__prefix, $signal);
                    \CApp::$App->log->work('收到：' . \cache\CRedis::SYSTEM_TASK[$signal] . '指令');
                } else {
                    \CApp::$App->log->work('收到：目前不支持此指令控制');
                }
            };

            pcntl_signal(SIGUSR1, $pSignal);
            pcntl_signal(SIGUSR2, $pSignal);
            pcntl_signal(SIGINT, $pSignal);
            pcntl_signal(SIGTERM, $pSignal);
            pcntl_signal(SIGQUIT, $pSignal);

            $signal = '指令：';
            foreach (\cache\CRedis::SYSTEM_TASK as $sig=>$desc) {
                $signal = $signal ."{$desc}={$sig}，";
            }
            \CApp::$App->log->work(substr($signal, 0, -3));
        }

        private function __setPrefix() {
            $this->__prefix = \CApp::$Setting['algorithm']['data']['prefix']['task'] . ':' .
                \CApp::$Setting['algorithm']['data']['listen'][$this->__data['platformId']];

            \CApp::$App->log->work("平台：{$this->__data['platformId']}，监听：{$this->__prefix}");
        }

        private function __addTestData() {
            \CApp::$App->cache->addTask($this->__prefix, [
                'action' => 'borrow.set',
                'source' => [
                    'platformId' => 1,
                ],
                'target' => [
                    'platformId' => 3,
                ],
                'data' => [
                    'id' => uniqid(),
                    'cash' => 1000,
                ]
            ]);

            \CApp::$App->cache->addTask($this->__prefix, [
                'action' => 'user.get',
                'source' => [
                    'userId' => 1,
                    'platformId' => 1,
                ],
                'target' => [
                    'platformId' => 3,
                ],
                'data' => [
                    'id' => uniqid(),
                    'cash' => 1000,
                ]
            ]);
        }
    }