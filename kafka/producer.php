<?php
    $data = array_merge(
        array(
            'partition' => RD_KAFKA_PARTITION_UA,
            'data' => 'this is default message'
        ),
        getopt('', array(
            'partition:',
            'data:'
        ))
    );

    pcntl_sigprocmask(SIG_BLOCK, array(SIGIO));
    $conf = new \RdKafka\Conf();
    $conf->set('internal.termination.signal', SIGIO);
    $conf->set('socket.blocking.max.ms', 60);
    
    $kafka = new \RdKafka\Producer($conf);
    $kafka->setLogLevel(LOG_DEBUG);
    $kafka->addBrokers('192.168.253.170:9092');

    $topic = $kafka->newTopic('tender');
    /**
     * 分区标识 RD_KAFKA_PARTITION_UA
     * 消息标识 当前始终为0
     * 消息内容
     */
    $topic->produce($data['partition'], 0, $data['data']);
