<?php  
    class CTask {
        private $__data;
        private $__pid, $__sig, $__log;
        
        public function __construct($data) {
            $this->__data = $data;
            
            $this->__pid = function_exists('posix_getpid') ? posix_getpid() : 'windows';
            $this->__sig = new CSignal();
            $this->__log = new CLog();
            $this->__log->info("{$this->__pid}，已运行。");
            
            try {
                $this->__listen();
            } catch (\Exception $e) {
                $this->__log->info(<<<LOG

------------------------
文件: {$e->getFile()}
行数: {$e->getLine()}
代码: {$e->getCode()}
异常: {$e->getMessage()}
------------------------
LOG
                );
            } finally {
                
            }
        }
        
        private function __listen() {
            while(true) {
                $this->__sig->dispatch();

                if(!is_array($get = $this->__data->get())) {
                    $this->__log->info('异常包' . PHP_EOL . print_r($get, true));
                    continue;
                }
                
                $this->__log->info('数据包' . PHP_EOL . print_r($get, true));
                $set = $this->__data->set($get);
                $this->__log->info('处理包' . PHP_EOL . print_r($set, true));
                
                usleep(100000);
            }
        }
        
        public function __destruct() {
            $this->__log->info("{$this->__pid}，已停止。");
        }
    }
