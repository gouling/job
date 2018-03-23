<?php
    $data = getopt('', array(
        'id:'
    ));

    $kafka = new \RdKafka\Producer();
    $kafka->setLogLevel(LOG_DEBUG);
    $kafka->addBrokers('192.168.252.58:9092,192.168.252.58:9093');

    $topic = $kafka->newTopic('tender');
    /**
     * 分区标识
     * 消息标识 当前始终为0
     * 消息内容
     */
    $topic->produce(0, 0, $data['id']);
