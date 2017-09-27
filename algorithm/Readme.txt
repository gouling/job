App set 业务参数
$data = array(
    //官标匹配/用户提现/内部转让
    'action' => 'borrow.set/change.get/change.special',
    //债权来源 债权来源用户编号 债权来源平台编号
    'source' => array(
        'user_id' => 1,
        'platform_id' => 1
    ),
    //债权目的 债权承接平台编号
    'target' => array(
        'platform_id' => 1
    ),
    /*
     * 官标参数 债权编号 债权金额(无let参数)
     * 转让参数 订单号 转让金额 是否全部转让
     */
    'data' => array(
        'id' => 10001,
        'cash' => 100000,
        'let' => true,
    )
);

App __accept 业务共用承接债权
$data = array(
    'action' => 'borrow.set/change.get/change.special',
    //债权来源用户编号 债权来源平台编号
    'source' => array(
        'user_id' => 1,
        'platform_id' => 1
    ),
    //债权承接平台编号
    'target' => array(
        'platform_id' => 1
    ),
    //债权数据 债权编号 债权金额 持有人数
    'data' => array(
        'id' => '201702250877',
        'cash' => 100000,
        'people' => 0
    )
);

App save 保存匹配结果(官标匹配时无参数[merit require]且param->set->user_id->index(N次承接金额为数值，只有提现转让时为数组为承接的公允价值本金与利息))
$data=Array
(
    //官标匹配/用户提现/内部转让
    [action] => borrow.set/change.get/change.special
    [source] => Array
        (
            [user_id] => 1
            [platform_id] => 1
        )

    [target] => Array
        (
            [platform_id] => 1
        )

    [data] => Array
        (
            [id] => 5
            [cash] => 1009.67
            [people] => 0
        )
    [merit] => Array
        (
            [principal] => 1000
            [interest] => 9.67
        )

    [require] => Array
        (
            [principal] => 1000
            [interest] => 9.67
        )
    [option] => Array
        (
            [id] => 20
            [scope] => Array
                (
                    [min] => 500
                    [max] => 5000
                )

            [minimum] => 100
            [maximum] => 1000
            [people] => 2000
            [most] => 10
            [category] => change.get
            [least] => 50
            [identity] => 10000.10
        )

    [param] => Array
        (
            [cash] => 0
            [people] => 10
            [index] => 0
            [set] => Array
                (
                    [135436] => Array
                        (
                            [1] => Array
                                (
                                    [principal] => 99.07
                                    [interest] => 0.93
                                )
                        )
                    [87515] => Array
                        (
                            [1] => Array
                                (
                                    [principal] => 99.04
                                    [interest] => 0.96
                                )
                        )
                    [207132] => Array
                        (
                            [1] => Array
                                (
                                    [principal] => 99.04
                                    [interest] => 0.96
                                )
                        )
                    [221133] => Array
                        (
                            [1] => Array
                                (
                                    [principal] => 99.04
                                    [interest] => 0.96
                                )
                        )
                    [221307] => Array
                        (
                            [1] => Array
                                (
                                    [principal] => 99.04
                                    [interest] => 0.96
                                )

                        )
                    [222591] => Array
                        (
                            [1] => Array
                                (
                                    [principal] => 99.04
                                    [interest] => 0.96
                                )

                        )
                    [222801] => Array
                        (
                            [1] => Array
                                (
                                    [principal] => 99.04
                                    [interest] => 0.96
                                )

                        )
                    [223342] => Array
                        (
                            [1] => Array
                                (
                                    [principal] => 99.04
                                    [interest] => 0.96
                                )

                        )
                    [224375] => Array
                        (
                            [1] => Array
                                (
                                    [principal] => 99.04
                                    [interest] => 0.96
                                )

                        )
                    [5502] => Array
                        (
                            [0] => Array
                                (
                                    [principal] => 108.61
                                    [interest] => 1.06
                                )
                        )
                )
        )
    [modify] => Array
        (
            [135436] => Array
                (
                    [1] => Array
                        (
                            [source] => Array
                                (
                                    [principal] => 99.04
                                    [interest] => 0.96
                                )
                            [target] => Array
                                (
                                    [principal] => 99.07
                                    [interest] => 0.93
                                )
                        )
                )
        )
)

CAlgorithm getMerit 获取债权公允价值
$data = array(
    //债权满标复审时情况 复审时间 还款方式 持有人数 总期数 已还期数 年利率
    'original' => array(
        'date' => strtotime('2017-01-31'),
        'type' => CDict::REPAYMENT_AVERAGE_CAPITAL_PLUS_INTEREST,
        'people' => 3,
        'period' => 3,
        'repaid' => 0,
        'apr' => 0.12,
    ),
    //转让债权时债权情况 个人持有债权待收本金 转让时间
    'leavings' => array(
        'principal' => 1000,
        'date' => strtotime('2017-03-31'),
    ),
    //转让债权时债权上次承接情况 承接本金 承接利息 承接时间
    'accept' => array(
        'principal' => 1000,
        'interest' => 0,
        'date' => strtotime('2017-01-01 08:00:00'),
    ),
    //转让债权时债权最近一次还款情况 还款时间 还款利息(个人持有债权本金收到的利息)
    'last' => array(
        'date' => strtotime('2017-01-01 08:00:00'),
        'interest' => 10,
    ),
);

CAlgorithm accept 承接债权
$data = array(
    'action' => 'borrow.set/change.get/change.special',
    //债权来源 债权来源用户编号 债权来源平台编号
    'source' => array(
        'user_id' => 1,
        'platform_id' => 1
    ),
    //债权目的 债权承接平台编号
    'target' => array(
        'platform_id' => 1
    ),
    //债权数据 债权编号 债权金额
    'data' => array(
        'id' => '201702250877',
        'cash' => 100000,
    ),
    //债权数据 债权可承接初始金额 债权承接人数 债权承接次数 由函数内部 地址引用 赋值
    'param' => array(
        'cash' => 100000,
        'people' => 0,
        'index' => 1
     ),
);
$user = array(
    //用户编号 帐户余额
    'user_id' => 1,
    'balance' => 1000
    //帐户余额 可投金额 进入队列金额/投标后余额
    'param' = array(
        'balance' => 1000,
        'enableUseBalance' => 1000,
        'enableBalance' => 1000,
        'useBalance' => 0,
    ),
);
