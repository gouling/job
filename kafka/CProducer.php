<?php
    include 'CHealth.php';
     
    class CProducer extends CHealth{
        private $__kafka, $__topic;
        private $__data;
        
        public function __construct($data) {
            $this->__data = $data;
            $this->__kafka = new \RdKafka\Producer();
            $this->__kafka->setLogLevel($this->__data['log']);
            $this->__kafka->addBrokers($this->__data['kafka']);
            $this->__topic = $this->__kafka->newTopic($this->__data['topic']);
            
            parent::__construct($this->__data['timeout']);
        }
        
        public function send($data, $partition = RD_KAFKA_PARTITION_UA) {
            //检查当前服务是否可用，不可用时延时再检查，依然不可用时必定服务全挂掉。此检查在批量操作时有性能影响。
            if($this->isRunning($this->__data['kafka']) == false) {
                sleep($this->__data['timeout']);
                if($this->isRunning($this->__data['kafka']) == false) {
                    //此时为运维高可用配置已失效，所有服务挂掉，数据包丢失，需要特殊处理。
                    return false;
                }
            }
            
            /**
             * 分区标识 RD_KAFKA_PARTITION_UA
             * 消息标识 当前始终为0
             * 消息内容
             */
            $this->__topic->produce($partition, 0, $data);
            //等待数据生产响应，此检查在批量操作时有性能影响。正常情况下无需等待可以提高效率
            while ($this->__kafka->getOutQLen() > 0) {
                $this->__kafka->poll(1);
            }
            
            return true;
        }
        
        public function __destruct() {
            $this->__kafka = $this->__topic = null;
        }
    }
