<?php
    include 'CConsumer.php';

    class Demo {
        public function set($data) {
            print_r($data);
            return true;
        }
    }
    
    $tender = array(
        'kafka' => '192.168.253.170:9092',
        'topic' => 'tender',
        'zk' => '192.168.253.170:2181',
        'root' => '/application/kafka',
    );
    $kafka = new CConsumer($tender);
    $kafka->accept(array(new Demo(), 'set'));
