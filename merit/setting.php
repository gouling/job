<?php
    return [
        'stable' => true,
        'log' => [
            'file' => __DIR__ . '/log',
            'format' => 'Ymd',
        ],

        'cache' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'db' => 0,
            'timeout' => 5
        ],

        'algorithm' => [
            'class' => '\algorithm\CAlgorithm',
            'data' => [
                'name' => '匹配',
                'size' => 1000,
                'prefix' => [
                    'task' => 'INTERFACE:TASK',
                    'fail' => 'INTERFACE:FAIL',
                    'data' => 'INTERFACE:DATA',
                ],
                'listen' => [
                    1 => 'PP',
                    2 => 'WLC',
                    3 => 'BB',
                ]
            ],
        ],
    ];