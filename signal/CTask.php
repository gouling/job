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
            
            $this->__listen();
        }
        
        private function __listen() {
            while(true) {
                $this->__sig->dispatch();

                try {
                    if(!is_array($data = $this->__data->get())) {
                        $this->__log->info('异常包' . PHP_EOL . print_r($data, true));
                        continue;
                    }
                    
                    $this->__log->info('任务包' . PHP_EOL . print_r($data, true));
                    $state = $this->__data->set();
                    $this->__log->info($state == true ? '已完成' : '已失败');
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
 
                usleep(100000);
            }
        }
        
        public function __destruct() {
            $this->__log->info("{$this->__pid}，已停止。");
        }
    }
