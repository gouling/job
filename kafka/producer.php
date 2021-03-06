<?php
    include 'CProducer.php';

    /**
     * timeout 
     * 运维配置的keepalived息息相关，为确保高可用，建义大于运维配置时间值
     * 服务正常时0秒返回true
     * 服务切换(keepalived)过程中最多2倍返回结果true
     * 服务全部挂掉后将耗时3倍返回结果为false
     */
    $tender = array(
        'kafka' => '192.168.253.170:9092',
        'timeout' => 2,
        'topic' => 'tender',
        'log' => LOG_DEBUG, //LOG_KERN
        'data' => '/tmp/tender.data'    //服务不可用时，数据保存文件
    );
    $kafka = new CProducer($tender);
    var_dump($kafka->send(array(
        'name'=>'蒋万勇'
    )));
