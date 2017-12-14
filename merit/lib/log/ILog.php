<?php

    namespace log;
    abstract class ILog {
        protected $__prefix;

        public function __construct($stable = false) {
            $this->__prefix = \CApp::$Pid;

            if ($stable == true && file_exists(\CApp::$Setting['log']['file']) == false) {
                mkdir(\CApp::$Setting['log']['file'], 0777, true);
            }

            $this->work(vsprintf('日志：已就绪，路经：%s', [
                \CApp::$Setting['log']['file']
            ]));
        }

        private function __getTimestamp() {
            return date('H:i:s');
        }

        private function __getWriteLogData($data, $code) {
            return "{$this->__getTimestamp()} {$code} {$data}";
        }

        protected function __getFileName() {
            return \CApp::$Setting['log']['file'] . DIRECTORY_SEPARATOR . $this->__prefix . '.' . date
                (\CApp::$Setting['log']['format']) .
                '.txt';
        }

        public function work($data, $code = 200) {
            $this->__setWriteLog($this->__getWriteLogData($data, $code));
        }

        abstract public function split();

        abstract protected function __setWriteLog($task);
    }