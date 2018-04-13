<?php
    include 'CProducer.php';
    
    /**
     * timeout 
     * 运维配置的keepalived息息相关，为确保高可用，建义大于运维配置时间值
     * 服务正常时0秒返回true
     * 服务切换(keepalived)过程中最多2倍返回结果true
     * 服务全部挂掉后将耗时3倍返回结果为false
     */
    $kafka = new CProducer(array(
        'kafka' => '192.168.253.170:9093',
        'log' => LOG_DEBUG,
        'timeout' => 5,
        'topic' => 'tender'
    ));
    var_dump($kafka->send('蒋万勇'));
