<?php
    return [
        'stable' => false,
        'log' => [
            'file' => __DIR__ . '/log',
            'format' => 'Ymd',
        ],

        'cache' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'db' => 0,
            'timeout' => 5,
            'key' => 'task:'
        ],

        'algorithm' => [
            'class' => '\algorithm\CAlgorithm',
            'data' => [
                'name' => '匹配',
                'listen' => [
                    1 => 'pp',
                    2 => 'wlc',
                    3 => 'bb',
                ]
            ],
        ],
    ];