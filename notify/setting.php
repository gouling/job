<?php
    return array(
        /**
         * 日志路经
         * 缓存配置
         */
        'log' => __DIR__ . DIRECTORY_SEPARATOR . 'log',
        'cache' => array(
            'host' => '192.168.253.242',
            'port' => 6380,
            'database' => 10,
            'dataPrefix' => 'NOTIFY',
            'failedPrefix' => 'FAILED',
        ),
        /**
         * 请求有效时间，超过此时间视为请求失败
         * 每线程每次并行请求数
         * 允许请求失败时的重试次数
         */
        'multi' => array(
            'timeout' => 30,
            'thread' => 1000,
            'retry' => 3,
        ),

        /**
         * 服务唯一标识
         * 服务名称
         * 请求地址
         * 回复地址 取消此键
         * 是否启用
         */
        'service' => array(
            '599ea20-de01a5-1880-1072' => array(
                'name' => '通知微宝->管理费结算信息',
                'request' => array(
                    'method' => 'get',
                    'address' => 'http://notify.com/request.php'
                ),
                'response' => array(
                    'method' => 'post',
                    'address' => 'http://notify.com/response.php',
                ),
                'status' => true,
            ),
            '599ea20-de01a5-1880-1073' => array(
                'name' => '通知微宝->管理费结算信息',
                'request' => array(
                    'method' => 'post',
                    'address' => 'http://notify.com/request.php'
                ),
                'response' => array(
                    'method' => 'get',
                    'address' => 'http://notify.com/response.php',
                ),
                'status' => false,
            ),
            '599ea20-de01a5-1880-1074' => array(
                'name' => '通知微宝->管理费结算信息',
                'request' => array(
                    'method' => 'get',
                    'address' => 'http://notify.com/request.php'
                ),
                'response' => array(
                    'method' => 'get',
                    'address' => 'http://notify.com/response.php',
                ),
                'status' => false,
            ),
        )
    );