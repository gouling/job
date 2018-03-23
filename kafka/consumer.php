<?php
    $kafka = new \RdKafka\Consumer();
    $kafka->setLogLevel(LOG_DEBUG);
    $kafka->addBrokers('192.168.252.58:9092,192.168.252.58:9093');

    $queue = $kafka->newQueue();
    $topic = $kafka->newTopic('tender');
    /**
     * 分区标识
     * 消息标识 0=开始位置，-1=结束位置 需要记录最后处理的消息标识，启动时此标识+1开始取消息
     * 消息队列
     */
    $topic->consumeQueueStart(0, 0, $queue);
    $topic->consumeQueueStart(1, 0, $queue);
    
    while (true) {
        $data = $queue->consume(1000);  //timeout
        if (is_object($data) && $data->err == 0) {
            print_r("{$data->partition}:{$data->offset}->{$data->timestamp}:{$data->payload}".PHP_EOL);
        }

        usleep(100000);
    }
