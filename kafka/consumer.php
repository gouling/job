<?php
    include 'CConsumer.php';

    class Demo {
        public function set($data) {
            print_r($data);
            return true;
        }
    }
    
    $kafka = new CConsumer(array(
        'zk' => '192.168.253.170:2181',
        'kafka' => '/application/kafka',
        'topic' => 'tender'
    ));
    $kafka->accept(array(new Demo(), 'set'));
