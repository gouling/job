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
        'token' => 'Q7sEpLRQxVA47P27yB_Zo2-CA0VYLHpJHmOBvO3QCrDUTXkwGiunOxG4sIobqr-Z74zoFPn-2dnkCetaWO6H_WJFV-20uecm7fFilWlGrkxVa2pxjBHS0_s1iE7buyWglCH_J3RsTV9SW5cx6KU7oRzThcWb8J51WNoZZpsMCphYWptqXrvcxKeTT3PkUtEi31yXBY4XWmiP88eG1HRr_Q',
    ];
    
    $sync = new \Synchronizes\Sync($opts);
    $sync->setDatabase($database);
    
    $sync->auth();
    //$sync->cancel();
