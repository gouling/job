<?php
    include_once __DIR__ . '/vendor/autoload.php';
    
    $chat = new \Chat\CChat();
    $token = 'gt3TQjP_fCwsHMpCpUAabjD1Vu-GjOzgCPr1Col9TTyJX3PqLrdbjBRsfgjEUeIXYtCRgGT7G-aDAxCC05EN6emp6qpjvVeaqrrANFXMHPVSg340XDCYwwYzcwFqD2F04uxurye0tDgLLzd4mqE9z3GKVlU6AaHZaU1u8Yk80J1xewVYbFJVlLTkXH9LjwMwPeen-0KcxGW804F1dCcRmQ';
    $department = $chat->getDepartmentList([
        1 => [
            'access_token' => $token,
        ]
    ]);

    $department_user = $chat->getUserListByDepartmentId([
        15 => [
            'access_token' => $token,
            'department_id' => 15,
        ],
        16 => [
            'access_token' => $token,
            'department_id' => 16,
        ],
        17 => [
            'access_token' => $token,
            'department_id' => 17,
        ],
        18 => [
            'access_token' => $token,
            'department_id' => 18,
        ],
    ]);
    
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
    
    print_r($department_user);
    print_r($user);