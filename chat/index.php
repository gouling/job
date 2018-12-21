<?php
    require(__DIR__ . '/vendor/autoload.php');
    
    $database = new \Databases\MySQL([
        'dsn' => 'mysql:host=127.0.0.1;dbname=sync',
        'username' => 'root',
        'password' => 'root',
        'options' => array(
            PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\''
        )
    ]);
    $opts = [
        'id' => 'wwfd9b94c8c22a0bdf',
        'token' => 'gmchIxRHixFYorN3ZdS-x336unAkgcbCPSkzKGmV8q4sNqbsNmlsVTDCh6bL_3xpWNI6nGYsXA41mhWEdXLZN0RWSgZfJCjR3LZPxKNw4bu6lAj7o8Qjg2y9JirBBDvf122rKBYQFbEunb9zPWUZ-vLVs1P2Ku_5Yq-oWKD7N7KocEwuIp4gG2iye9bxqwfLTk5-Z39T9rqck68qW2eVbw',
    ];
    
    $sync = new \Synchronizes\Sync($opts);
    $sync->setDatabase($database);
    
    $sync->auth();
