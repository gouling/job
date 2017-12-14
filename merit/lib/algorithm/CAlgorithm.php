<?php

    namespace algorithm;

    class CAlgorithm {
        private $__data, $__prefix;

        public function set($data) {
            if(!isset($data['platformId'])) {
                throw new \Exception('平台参数(platformId)不可用', 404);
            } else if(!isset(\CApp::$Setting['algorithm']['data']['listen'][$data['platformId']])) {
                throw new \Exception('平台参数(platformId)不支持', 501);
            }

            $this->__data = $data;
            $this->__setPrefix();

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

            while (true) {
                if ($task = \CApp::$App->cache->getTask($this->__prefix)) {
                    switch ($task['action']) {
                        case 'system':
                            switch ($task['data']) {
                                case 'stop':
                                    \CApp::$App->log->work("停止系统服务指令");
                                    break 3;
                                case 'recovery':
                                    \CApp::$App->log->work("重新运行异常数据");
                                    break;
                                default:
                                    \CApp::$App->log->work("不支持的系统指令");
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

                usleep(\CApp::$Setting['algorithm']['data']['pause']);
            }
        }

        private function __setPrefix() {
            $this->__prefix = \CApp::$Setting['algorithm']['data']['prefix']['task'] . ':' .
                \CApp::$Setting['algorithm']['data']['listen'][$this->__data['platformId']];

            \CApp::$App->log->work("平台：{$this->__data['platformId']}，监听：{$this->__prefix}");
        }
    }