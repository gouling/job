<?php
    $kafka = new \RdKafka\Consumer();
    $kafka->setLogLevel(LOG_DEBUG);
    $kafka->addBrokers('127.0.0.1:9092');

    $queue = $kafka->newQueue();
    $topic = $kafka->newTopic('tender');
    /**
     * RD_KAFKA_OFFSET_BEGINNING
     * RD_KAFKA_OFFSET_END
     * RD_KAFKA_OFFSET_STORED
     */
    $topic->consumeQueueStart(0, RD_KAFKA_OFFSET_END, $queue);

    while (true) {
        $data = $queue->consume(1000);  //timeout
        if (is_object($data) && $data->err == 0) {
            print_r("{$data->timestamp}:{$data->payload}".PHP_EOL);
        }

        usleep(1000);
    }