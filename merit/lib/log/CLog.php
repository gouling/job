<?php

    namespace log;
    class CLog {
        private $__log;

        public function __construct() {
            if(\CApp::$Setting['stable'] == true) {
                $this->__log = new CStable();
            } else {
                $this->__log = new CDebug();
            }
        }

        public function work($data, $code = 200) {
            $this->__log->work($data, $code);
        }

        public function split() {
            $this->__log->split();
        }
    }