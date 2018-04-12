<?php
    include 'CZookeeper.php';
    include 'CConsumer.php';
    include 'CSignal.php';
    include 'CLog.php';
    
    $application = array(
        '/application' => '系统全局数据与配置信息',
        
        '/application/kafka' => 'kafka服务，消费超时，延时微秒',
        '/application/kafka/host' => '192.168.253.180:9092',
        '/application/kafka/timeout' => 1000,
        '/application/kafka/delay' => 100000,
        
        '/application/kafka/tender' => '债权匹配，自动承接，用户提现，内部提现',
        '/application/kafka/tender/0'=>0,
        '/application/kafka/tender/1'=>0,
        '/application/kafka/tender/2'=>0,
    );
    
    $zookeeper = new CZookeeper('192.168.253.170:2181');
    $zookeeper->create($application);
    
    $kafkaConfig = $zookeeper->get('/application/kafka');
    $topicConfig = $zookeeper->get('/application/kafka/tender');

    $consumer = new CConsumer($zookeeper, $kafkaConfig, $topicConfig);
    
    class Demo {
        public function set($data) {
            //print_r($data);
            return true;
        }
    }
    $consumer->consumer(array(new Demo(), 'set'));
