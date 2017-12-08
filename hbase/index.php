<?php
    require 'lib/index.php';

    $client = new \HBase\Client('127.0.0.1', 9090);

    print_r($client->search('news', 1, 2));