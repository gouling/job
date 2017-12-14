<?php
    require __DIR__ . '/vendor/autoload.php';

    class CApp {
        static $App, $Setting, $Pid;
        public $log, $cache, $components;

        static public function create($setting) {
            if (is_null(self::$App)) {
                return self::$App = new CApp($setting);
            } else {
                return self::$App;
            }
        }

        public function __construct($setting) {
            self::$Setting = $setting;
            self::$Pid = posix_getpid();

            if(self::$Setting['stable'] == true) {
                error_reporting(0);
            }
        }

        public function initialize() {
            $this->components = [];

            try {
                $this->log = new \log\CLog();
                $this->cache = new \cache\CRedis();
            } catch (\Exception $e) {
                $this->log->work(vsprintf("系统组件初始化已停止，消息：%s", [
                    $e->getMessage(),
                ]), $e->getCode());
            }

            return self::$App;
        }

        public function work($component, $server, $data = []) {
            try {
                $this->log->work(vsprintf("%s，已运行，Pid：%s", [
                    self::$Setting[$component]['data']['name'],
                    self::$Pid,
                ]));

                $this->components[$component] = new self::$Setting[$component]['class']();
                $this->components[$component]->$server($data);

                $this->log->work(vsprintf("%s，已完成，Pid：%s", [
                    self::$Setting[$component]['data']['name'],
                    self::$Pid,
                ]));
            } catch (\Exception $e) {
                $this->log->work(vsprintf("%s，已停止，Pid：%s，消息：%s", [
                    self::$Setting[$component]['data']['name'],
                    self::$Pid,
                    $e->getMessage()
                ]), $e->getCode());
            }

            $this->log->split();
        }
    }