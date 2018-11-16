<?php
    include_once __DIR__ . '/vendor/autoload.php';
    
    $chat = new \Chat\CChat();
    $token = file_get_contents('token.txt');
    
    $department = $chat->getDepartmentList([
        1 => [
            'access_token' => $token,
        ]
    ]);
    
    $get_user_req = [];
    foreach ($department[1] as $k=>$v) {
        $get_user_req[$v['id']] = [
            'access_token' => $token,
            'department_id' => $v['id'],
        ];
    }
    
    $department_user = $chat->getUserListByDepartmentId($get_user_req);

    $user = $chat->getUserInfoById([
        [
            'access_token' => $token,
            'user_id' => 'wuzhengyu',
        ],
        [
            'access_token' => $token,
            'user_id' => 'liyi',
        ],
    ]);