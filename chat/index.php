<?php
    include_once __DIR__ . '/vendor/autoload.php';

    $chat = new \Chat\CChat();
    $token = 'adqzJI1gGDqDv-9QZY77nFezpJhcq1NwLsU6M5_iQ7Yts_gSoBWypS5wUQAjPeAYwnPl9hX1j-9ZPHTEEDDiHuKJ5_B7TnHTI-UdZOzInabtQAWMeHRcyzZgaF6SKYw51JES3pUZOIpRZpaJgpDJr1LmaQ2x3HaBz8ozZOCSOFD88cSfbiymXyHxvjgWORxMFO6jV7DpA2QY6RJKxZN54A';

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
            'user_id' => 'liurongceshi',
        ],
        [
            'access_token' => $token,
            'user_id' => 'zhangqin',
        ],
    ]);
