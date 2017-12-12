<?php
    require 'lib/index.php';

    $client = new \HBase\Client('127.0.0.1', 9090);
    print_r($client->search([
        'table' => 'users',
        'page' => 1,
        'pageSize' => 2
    ]));