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
        'suite_id' => 'wxb388a3469a14426b',
        'corp_id' => 'wwfd9b94c8c22a0bdf',
        'token' => 'b-gbykg8srE66Ta7CaXMreggxnBtXdHNKPos1nMAvshPgLqnR6Yso2odK_gFiUpj1BfmuidNfgXkfhxNawmHoqMv8I45pCvS84IvoyBG4uCqVDc94XrzBqXyG_Pq2LCGNVqk3ExXJGi5OfXVEKxxTPvMS6s8YzFL3fmh-kn1-Ng8AIdD87vG84VZBAB0BaYyc_AXXcykX_RJ2XGfRBm2kA',
    ];
    
    $sync = new \Synchronizes\Sync($opts);
    $sync->setDatabase($database);
    
    $sync->create();
    $sync->updateParentId();
    $sync->updateLevel();
