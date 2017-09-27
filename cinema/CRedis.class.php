<?php

    namespace Api\Information;

    class CRedis {
        private $__setting;
        static $redis;


        public function __construct() {
            $this->__setting = include('setting.php');

            if (!is_object(self::$redis)) {
                self::$redis = new \Redis();
                if (C('MOVIE_INTERFACE.DEV_MODE') == true) {
                    self::$redis->pconnect(C('REDIS_HOST'), C('REDIS_PORT'));
                } else {
                    self::$redis->pconnect($this->__setting['sock']);
                }
            }
        }

        public function initialize() {
            self::$redis->del($this->__setting['prefix']['login']['send']);
            self::$redis->del($this->__setting['prefix']['login']['accept']);
            //self::$redis->del($this->__setting['prefix']['key']['send']);
            //self::$redis->del($this->__setting['prefix']['key']['accept']);
            //self::$redis->del($this->__setting['prefix']['post']['send']);
            //self::$redis->del($this->__setting['prefix']['post']['accept']);
            //self::$redis->del($this->__setting['prefix']['data']);
            //self::$redis->del($this->__setting['prefix']['query']);
        }

        public function UKey($identity, $binary) {
            $this->send($this->__setting['prefix']['key']['send'], $identity, $binary);
            if ($data = $this->accept($this->__setting['prefix']['key']['accept'], $identity)) {
                return $data;
            } else {
                $this->hDel($this->__setting['prefix']['key']['send'], $identity);

                return false;
            }
        }

        public function send($key, $identity, $binary) {
            $this->hSet($key, $identity, $binary);
        }

        public function accept($key, $identity) {
            $refer = false;
            $timeout = $this->__setting['timeout'] * 30000;

            for ($second = 0; $second < $timeout; $second++) {
                if ($refer = $this->hGet($key, $identity)) {
                    break;
                } else {
                    usleep(10);
                }
            }

            return $refer;
        }

        public function getBinaryList($prefix, $data) {
            if ($refer = $this->hGetAll($prefix)) {
                $this->Expire($data);
                foreach ($refer as $key => $val) {
                    $this->hSet($data, $key, $val);
                    $this->hDel($prefix, $key); //不直接移除是因为C移除后无法写入
                }

                return $refer;
            }

            return false;
        }

        public function hSet($key, $field, $val) {
            self::$redis->hSet($key, $field, $val);
        }

        public function hGet($key, $field, $is_del = true) {
            $data = false;
            if (self::$redis->hExists($key, $field)) {
                $data = self::$redis->hGet($key, $field);
                if ($is_del) {
                    $this->hDel($key, $field);
                }
            }

            return $data;
        }

        public function Expire($prefix, $time = 600) {
            self::$redis->expire($prefix, $time);
        }

        public function hGetAll($prefix) {
            return self::$redis->hGetAll($prefix);
        }

        public function hDel($key, $field) {
            self::$redis->hDel($key, $field);
        }
    }