<?php
    class CSignal {
        private $__pSignal;
        
        public function __construct() {
            $this->__createSig();
            $this->__installSig();
        }
        
        public function dispatch() {
            pcntl_signal_dispatch();
        }
        
        private function __createSig() {
            $this->__pSignal = function($signal) {
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

                exit();
            };
        }
        
        private function __installSig() {
            pcntl_signal(SIGUSR1, $this->__pSignal);
            pcntl_signal(SIGUSR2, $this->__pSignal);
            pcntl_signal(SIGINT, $this->__pSignal);
            pcntl_signal(SIGTERM, $this->__pSignal);
            pcntl_signal(SIGQUIT, $this->__pSignal);
        }
    }
