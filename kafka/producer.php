<?php
    $data = getopt('', array(
        'id:'
    ));

    $kafka = new \RdKafka\Producer();
    $kafka->setLogLevel(LOG_DEBUG);
    $kafka->addBrokers('127.0.0.1:9092');

    $topic = $kafka->newTopic('tender');
    $topic->produce(RD_KAFKA_PARTITION_UA, 0, $data['id']);