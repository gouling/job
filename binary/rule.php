<?php
    /**
     * F 自动执行方法
     * S 缓存键值
     */
    return array(
        0x02=>'setLoginStatus',
        0x04=>'setNotice',    //服务端通知下发

        0x0B=>'queryScreen',    //服务器查询影厅
        0x0D=>'getScreen',  //服务器查询影厅

        0x0E=>'querySeat',    //服务器查询影厅座位
        0x10=>'getSeat',    //服务器查询影厅座位

        0x1D=>'queryTicket',    //服务端查询售票原始数据
        0x1F=>'setUploadTicket',    //主动售票原始数据,被动服务端查询售票原始数据或者 是否继续发送

        0x14=>'setTimeoutTicket',    //退票请求或补登请求处理情况

        0x16=>'queryPlan',    //服务端查询电影院放映计划
        0x18=>'getPlan',    //服务端查询电影院放映计划

        0x19=>'queryPlanLog',   //服务端查询影院日志
        0x1B=>'getPlanLog',   //服务端查询影院日志

        0x20=>'queryReport',    //服务端查询票房统计
        0x22=>'getReport',    //服务端查询票房统计

        0x25=>'queryVersion',    //服务端查询客户端软件版本

        //服务器查询本地数据接口
        'queryScreen'=>0x0C,
        'getScreen'=>0x0C,

        'querySeat'=>0x0F,
        'getSeat'=>0x0F,

        'queryTicket'=>0x1E,
        'getUploadTicket'=>0x1E,

        'queryPlan'=>0x17,
        'getPlan'=>0x17,

        'queryPlanLog'=>0x1A,
        'getPlanLog'=>0x1A,

        'queryReport'=>0x21,
        'getReport'=>0x21
    );
