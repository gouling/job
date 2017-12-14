<?php

    namespace log;
    class CStable extends ILog {
        public function __construct() {
            parent::__construct(true);
        }

        public function split() {
            file_put_contents($this->__getFileName(), PHP_EOL, FILE_APPEND);
        }

        protected function __setWriteLog($data) {
            file_put_contents($this->__getFileName(), $data . PHP_EOL, FILE_APPEND);
        }
    }