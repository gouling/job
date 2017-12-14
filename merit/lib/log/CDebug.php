<?php

    namespace log;
    class CDebug extends ILog {
        public function __construct() {
            parent::__construct(false);
        }

        public function split() {
            echo PHP_EOL;
        }

        protected function __setWriteLog($data) {
            echo $data . PHP_EOL;
        }
    }