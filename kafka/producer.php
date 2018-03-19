<?php
    $data = getopt('', array(
        'id:'
    ));

    $kafka = new \RdKafka\Producer();
    $kafka->setLogLevel(LOG_DEBUG);
    $kafka->addBrokers('127.0.0.1:9092');

    $topic = $kafka->newTopic('tender');
    /**
     * 分区参数
     * RD_KAFKA_PARTITION_UA
     */
    $topic->produce(RD_KAFKA_PARTITION_UA, 0, $data['id']);
