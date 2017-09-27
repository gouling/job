<?php
    return array(
        'sock'=>'/dev/shm/redis.sock',
        'prefix'=>array(
            'key'=>array(
                'send'=>'key:function',
                'accept'=>'key:function:accept'
            ),
            'login'=>array(
                'send'=>'key:login',
                'accept'=>'key:login:accept'
            ),
            'send'=>array(
                'send'=>'key:send',
                'accept'=>'key:send:accept'
            ),
            'post'=>array(
                'send'=>'key:post',
                'accept'=>'key:post:accept'
            ),
            'data'=>'key:send:data', //hash php记录返回的每一个包，由前端调用accept后移除
            'query'=>'key:query'    //用于下发查询临时缓存分页，数据
        ),

        'sync'=>0x55AA,
        'version'=>0x01,

        'timeout'=>10

        /*'address'=>array(
            '111.205.151.10:8000'=>array(
                'username'=>'12010011',
                'password'=>'150503',
                'report'=>array(
                    'address'=>'https://111.205.151.10:7000/zgdypww/statistics/put',
                    'api'=>'',
                    'port'=>''
                ),
                'md5'=>'3F4DC537C20648967F20A38F1CD77372',
                'rule'=>range(0x00, 0x28),
                'status'=>1,
            )
        )*/
    );