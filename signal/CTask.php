<?php  
    class CTask {
        private $__pid, $__sig, $__log;
        
        public function __construct() {
            $this->__pid = function_exists('posix_getpid') ? posix_getpid() : 'windows';
            $this->__sig = new CSignal();
            $this->__log = new CLog();
            $this->__log->info("{$this->__pid}，已运行。");
            
            $this->__listen();
        }
        
        private function __listen() {
            while(true) {
                $this->__sig->dispatch();
                usleep(100000);
            }
        }
        
        public function __destruct() {
            $this->__log->info("{$this->__pid}，已停止。");
        }
    }
