<?php
    /**
        create 'users','login','info'
        create 'news','title','content','readTotal'

        put 'users',1,'login:username','gouling'
        put 'users',1,'login:password','gouling'
        put 'users',1,'info:realname','芶凌'
        put 'users',1,'info:sex','男'
        put 'users',1,'info:telephone','17612800917'
        put 'users',1,'info:address','四川省成都市青羊区城区四川广电国际大厦-东华正街42号25F'

        put 'users',2,'login:username','root'
        put 'users',2,'login:password','root'
        put 'users',2,'info:realname','Linux管理员'
        put 'users',2,'info:sex','男'
        put 'users',2,'info:telephone','17612800917'
        put 'users',2,'info:address','四川省成都市青羊区城区四川广电国际大厦-东华正街42号25F'

        put 'users',3,'login:username','admin'
        put 'users',3,'login:password','admin'
        put 'users',3,'info:realname','Windows管理员'
        put 'users',3,'info:sex','女'
        put 'users',3,'info:telephone','18615723140'
        put 'users',3,'info:address','四川省成都市锦江区锦江大道889号合能锦城3栋1单元1106号'
     */

    require 'lib/index.php';

    $client = new \HBase\Client('127.0.0.1', 9090);

    print_r($client->search('users'));