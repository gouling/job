<?php
    class CConsumer {
        private $__pid, $__sig, $__log;
        private $__zookeeper, $__kafkaConfig, $__topicConfig;
        private $__kafka, $__queue, $__topic;
        private $__partition;
        
        public function __construct($zookeeper, $kafkaConfig, $topicConfig) {
            $this->__pid = function_exists('posix_getpid') ? posix_getpid() : 'windows';
            $this->__sig = new CSignal();
            $this->__log = new CLog();
            
            $this->__zookeeper = $zookeeper;
            $this->__kafkaConfig = $kafkaConfig;
            $this->__topicConfig = $topicConfig;
            
            $this->__initialize($topicConfig);
            $this->__log->info("{$this->__pid}，initialized。");
        }
        
        private function __initialize($topicConfig) {
            $this->__kafka =  new \RdKafka\Consumer();
            $this->__kafka->setLogLevel(LOG_DEBUG);
            $this->__kafka->addBrokers($this->__kafkaConfig['host']);
            
            $this->__queue = $this->__kafka->newQueue();
            $this->__topic = explode('/', $this->__topicConfig['node']);
            $this->__topic = $this->__kafka->newTopic(end($this->__topic));
            
            unset($topicConfig['node'], $topicConfig['doc']);
            foreach($topicConfig as $partition=>$offset) {
                $this->__partition[$partition] = $this->__topicConfig['node'] . '/' . $partition;
                /**
                 * 分区标识
                 * 消息标识 0=开始位置，-1=结束位置 需要记录最后处理的消息标识，启动时此标识+1开始取消息
                 * 消息队列
                 */
                $this->__topic->consumeQueueStart($partition, $offset, $this->__queue);
            }
        }
        
        public function consumer($operation) {
            $this->__log->info("{$this->__pid}，Running。");
            
            while (true) {
                $this->__sig->dispatch();
                $data = $this->__queue->consume($this->__kafkaConfig['timeout']);
                if (is_object($data)) {
                    if($data->err == 0) {
                        try {
                            $refer = call_user_func_array($operation, array($data)) === true ? 'Succeed' : 'Failed';
                        } catch (\Exception $e) {
                            $refer = $e->message;
                        }
                        
                        $this->__log->info("P{$data->partition}O{$data->offset}T{$data->timestamp}，{$data->payload}，{$refer}。");
                    }
                    $this->__zookeeper->set($this->__partition[$data->partition], $data->offset);
                }

                usleep($this->__kafkaConfig['delay']);
            }
        }
        
        public function __destruct() {
            $this->__log->info("{$this->__pid}，Stopped。");
        }
    }
