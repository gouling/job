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
        'token' => 'kgN5V9Qdm5Ib9q5SmjEEIR5ClmR7YtPyoJwrimSXG-yv9ycsC3mvb3juDmXbmEWe8TqVL0beAI2sJqiXjHSeLhiVCeUGU0QNEWMwxQ76AWpm2_cKc0OzXrYF8rT0XjuT7jePvi7WjUHpTyqA6f2LSYiK0fHl47YLqcgVDvJlEwKKxtWwCljJfemFN0hZQj3_csnjuuO3R5NkuaCUCzxM4g',
    ];
    
    $sync = new \Synchronizes\Sync($opts);
    $sync->setDatabase($database);
    
    $sync->auth();
