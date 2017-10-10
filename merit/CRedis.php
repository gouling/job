<?php

    /**
     * Date: 6/6/16
     * Time: 11:29 AM
     */
    class CRedis extends Redis {

        const TASK = 'INTERFACE:TASK';
        const EXCEPTION = 'INTERFACE:EXCEPTION';
        const NOTICE = 'INTERFACE:NOTICE';
        const REEXAMINEFAIL = 'INTERFACE:REEXAMINEFAIL';
        const COMMITFAIL = 'INTERFACE:COMMITFAIL';
        const CMD = 'INTERFACE:CMD';

        const TASK_ALGORITHM = 'algorithm';
        const TASK_NOTICE = 'notice';
        const CMD_STOP = 'STOP';

        private $suffix;//����׺
        protected $connect = null;

        public function __construct($suffix = 0, $timeout = 5) {
            if (!$this->connect) {
                parent::__construct();
                $config = include(ConfigDir . '/Db/redis.config.php');

                try {
                    $this->connect = $this->connect($config['host'], $config['port'], $timeout);
                    $this->select($config['db']);
                    //            if (!$suffix) throw new \Exception('suffix bad input');
                    $this->setSuffix($suffix);
                } catch (\Exception $e) {
                    sleep($timeout);
                }
            }
        }

        /**
         * ���ú�׺
         *
         * @param $suffix
         */
        public function setSuffix($suffix) {
            $this->suffix = $suffix;

            return $this;
        }

        /**
         * �ع���
         *
         * @param $prefix
         *
         * @return string
         */
        protected function rebuildKey($prefix) {
            return $this->suffix ? "{$prefix}:{$this->suffix}" : $prefix;
        }


        public function recoveryTask() {
            $data = array();
            try {
                while ($task = parent::lPop($this->rebuildKey(self::EXCEPTION))) {
                    $data[] = $task;
                    self::rPush($this->rebuildKey(self::TASK), $task);
                }
            } catch (\Exception $e) {
                $this->__construct($this->suffix);

                return false;
            }

            return $data;
        }

        public function addExceptionTask($data) {
            try {
                return self::lPush($this->rebuildKey(self::EXCEPTION), json_encode($data)) > 0;
            } catch (\Exception $e) {
                $this->__construct($this->suffix);

                return false;
            }
        }

        public function addLSingleTask($data) {
            try {
                return self::lPush($this->rebuildKey(self::TASK), json_encode($data)) > 0;
            } catch (\Exception $e) {
                $this->__construct($this->suffix);

                return false;
            }
        }

        public function addRSingleTask($data) {
            try {
                return self::rPush($this->rebuildKey(self::TASK), json_encode($data)) > 0;
            } catch (\Exception $e) {
                $this->__construct($this->suffix);

                return false;
            }
        }

        public function getSingleTask() {
            try {
                if ($data = parent::lPop($this->rebuildKey(self::TASK))) {
                    return json_decode($data, true);
                }

                return $data;
            } catch (\Exception $e) {
                $this->__construct($this->suffix);

                return false;
            }
        }

        public function setTaskCmd($name, $cmd) {
            try {
                return self::hSet($this->rebuildKey(self::CMD), $name, $cmd) > 0;
            } catch (\Exception $e) {
                $this->__construct($this->suffix);

                return false;
            }
        }

        public function getTaskCmd($name) {
            try {
                if ($data = parent::hGet($this->rebuildKey(self::CMD), $name)) {
                    parent::hDel($this->rebuildKey(self::CMD), $name);

                    return $data;
                }

                return $data;
            } catch (\Exception $e) {
                $this->__construct($this->suffix);

                return false;
            }
        }

        public function __destruct() {
            parent::__destruct();
        }
    }