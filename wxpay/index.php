<?php
    header('Content-Type:text/html; charset=utf-8');
    include_once("WXPay.class.php");
    $out_trade_no = 'dpw' . date('YmdHis') . rand(1000, 9999);
    $pay = new WXPay(
        array(
            'appid' => 'wxae622eeaaad0b8b7',
            'mch_id' => '1250292701',
            'trade_type' => 'NATIVE',
            'notify_url' => 'http://127.0.0.1/notify.php'    //异步通知地址
        ),
        array(
            'api' => 'https://api.mch.weixin.qq.com/pay/unifiedorder',    //统一下单
            'timeout' => 5,
            'key' => '588e069e5da7c2b62d772a2d5da94e28'
        )
    );
    $data = $pay->unifiedOrderPay(array(
        'out_trade_no' => $out_trade_no,    //订单号
        'body' => 'iPad',    //商品名称
        'detail' => 'iPad 16G 中国版',    //商品描述 可选参数
        'total_fee' => 1, //价格 分
    ));
    var_dump($out_trade_no, $data);
    $query = new WXPay(
        array(
            'appid' => 'wxbd86ca47590f68b6',
            'mch_id' => '1277192101',
        ),
        array(
            'api' => 'https://api.mch.weixin.qq.com/pay/orderquery',    //查询支付情况
            'key' => 'db2ec121ee9515cdbff4d6c3a03867a1',
            'timeout' => 5
        )
    );
    $data = $query->queryUnifiedOrderPay(array(
        'out_trade_no' => 'dpw201510291159093776'
    ));
    var_dump($data);
    $pay = new WXPay(
        array(
            'appid' => 'wxbd86ca47590f68b6',
            'mch_id' => '1277192101',
        ),
        array(
            'api' => 'https://api.mch.weixin.qq.com/pay/micropay',    //刷卡支付
            'timeout' => 5,
            'key' => 'db2ec121ee9515cdbff4d6c3a03867a1',
        )
    );
    $data = $pay->microPay(array(
        'out_trade_no' => $out_trade_no,    //订单号
        'body' => 'iPad',    //商品名称
        'detail' => 'iPad 16G 中国版',    //商品描述 可选参数
        'total_fee' => 1, //价格 分
        'auth_code' => '130307746214286050',    //授权码
    ));
    var_dump($out_trade_no, $data);
    $query = new WXPay(
        array(
            'appid' => 'wxbd86ca47590f68b6',
            'mch_id' => '1277192101',
        ),
        array(
            'api' => 'https://api.mch.weixin.qq.com/pay/orderquery',    //查询支付情况
            'key' => 'db2ec121ee9515cdbff4d6c3a03867a1',
            'timeout' => 5
        )
    );
    $data = $query->queryMicroPay(array(
        'out_trade_no' => 'dpw201510291159093776'
    ));
    var_dump($data);