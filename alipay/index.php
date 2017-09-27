<?php
    header('Content-Type:text/html; charset=utf-8');
    include_once('APPay.php');
    $out_trade_no = 'dpw' . date('YmdHis') . rand(1000, 9999);
    $pay = new APPay(array(
        'private_key' => file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'key' . DIRECTORY_SEPARATOR . 'rsa_private_key.pem'),
        'public_key' => file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'key' . DIRECTORY_SEPARATOR . 'rsa_public_key.pem'),
        'app_id' => '2015071000162781',
        'timeout' => 5
    ));
    $data = $pay->pay(array(
        'out_trade_no' => $out_trade_no,
        'total_amount' => 0.01,    //订单金额，元
        'subject' => 'iPad',    //订单标题
        'body' => 'iPad 16G 中国版',    //订单描述
        'auth_code' => '284279130595680070',    //扫描手机条码值
    ));
    var_dump($data);
    $data = $pay->query(array(
        'out_trade_no' => $out_trade_no,
    ));
    var_dump($data);



    