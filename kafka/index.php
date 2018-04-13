<?php
    include 'CZookeeper.php';
    
    $application = array(
        '/application' => '系统全局数据与配置信息',
        
        '/application/kafka' => 'kafka服务，消费超时，延时微秒',
        '/application/kafka/host' => '192.168.253.170:9092',
        '/application/kafka/log' => LOG_DEBUG,  //接收数据时KAFKA日志级别
        '/application/kafka/timeout' => 1000,   //接收数据超时（无数据，以微秒为单位）
        '/application/kafka/delay' => 100000,   //收到数据处理后延时，防止CPU过高，设置过高则处理速度慢，酌情配置（以微秒为单位）
        
        '/application/kafka/tender' => '债权匹配，自动承接，用户提现，内部提现',
        '/application/kafka/tender/0'=>0,
        '/application/kafka/tender/1'=>0,
        '/application/kafka/tender/2'=>0,
    );
    
    $zookeeper = new CZookeeper('192.168.253.170:2181');
    $zookeeper->create($application);
