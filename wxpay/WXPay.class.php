<?php

    /**
     * Class WXPay 微信支付[不含退款,因需要ssl文件]，此接口已内置规则验证
     * 业务调用任何接口返回数据后应先验证array('err_code_id'=>是否设置, 'err_code_des'=>已设置err_code_id错误信息)
     *
     * microPay 刷卡支付
     * queryMicroPay 查询支付情况
     *
     * 以下接口不建议使用
     * unifiedOrderPay 统一下单 此接口返回二维码地址
     * queryUnifiedOrderPay 统一下单 查询支付情况
     * notifyUnifiedOrderPay 统一下单 异步通知调用 未测试 需公网域名ip 此接口无返回
     *
     * queryMicroPay===queryUnifiedOrderPay
     */
    class WXPay {
        private $__key, $__data, $__http, $__http_opt;

        /**
         * 初始化相关组件
         * @param array $data
         * @param array $setting
         */
        public function __construct($data = array(), $setting = array()) {
            $this->__http_opt = array(
                CURLOPT_URL => $setting['api'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => 0,
                CURLOPT_CONNECTTIMEOUT => $setting['timeout'],
                CURLOPT_TIMEOUT => $setting['timeout'],
                CURLOPT_POST => 1,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            );
            $this->__key = $setting['key'];
            $this->__data = $data;
        }

        /**
         * 统一下单
         * @param $data
         * @return array
         */
        public function unifiedOrderPay($data) {
            $this->__data = array_merge($this->__data, $data);
            $this->__setAttach();
            $this->__setCreateIp();
            $this->__setNonceStr();
            $this->__setSign();
            $this->__setOrderXml();
            $this->__postData();

            return $this->__xmlToArray(__FUNCTION__);
        }

        /**
         * 统一下单 查询支付情况
         * @param $data
         * @return array
         */
        public function queryUnifiedOrderPay($data) {
            return $this->queryMicroPay($data);
        }

        /**
         * 统一下单 异步通知调用 未测试 需公网域名ip
         */
        public function notifyUnifiedOrderPay() {
            $this->__data['xml'] = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : false;
            $this->__xmlToArray(__FUNCTION__);
        }

        /**
         * 刷卡支付
         * @param $data
         * @return array
         */
        public function microPay($data) {
            $this->__data = array_merge($this->__data, $data);
            $this->__setAttach();
            $this->__setCreateIp();
            $this->__setNonceStr();
            $this->__setSign();
            $this->__setOrderXml();
            $this->__postData();

            return $this->__xmlToArray(__FUNCTION__);
        }

        /**
         * 查询支付情况
         * @param $data
         * @return array
         */
        public function queryMicroPay($data) {
            $this->__data = array_merge($this->__data, $data);
            $this->__setNonceStr();
            $this->__setSign();
            $this->__setOrderXml();
            $this->__postData();

            return $this->__xmlToArray(__FUNCTION__);
        }

        /**
         * 提交到支付平台进行支付
         */
        private function __postData() {
            $this->__http = curl_init();
            $this->__http_opt[10015] = $this->__data['xml'];
            foreach ($this->__http_opt as $key => $val) {
                curl_setopt($this->__http, $key, $val);
            }
            $this->__data['xml'] = curl_exec($this->__http);
            curl_close($this->__http);
        }

        /**
         * 数据转换成微信xml格式
         */
        private function __setOrderXml() {
            $xmlData = '';
            foreach ($this->__data as $key => $val) {
                $xmlData .= "<{$key}><![CDATA[{$val}]]></{$key}>";
            }
            $this->__data['xml'] = "<xml>{$xmlData}</xml>";
        }

        /**
         * 随机字符串 nonce_str
         */
        private function __setNonceStr() {
            $this->__data['nonce_str'] = md5(json_encode($this->__data));
        }

        /**
         * 签名
         */
        private function __setSign() {
            ksort($this->__data);
            foreach ($this->__data as $key => $val) {
                $this->__data['sign'][] = "{$key}=$val";
            }
            $this->__data['sign'] = strtoupper(md5(implode('&', $this->__data['sign']) . "&key={$this->__key}"));
        }

        /**
         * 附加认证 双重md5+base64 防止网络中间层截取模拟数据
         */
        private function __setAttach() {
            $this->__data['attach'] = base64_encode(md5(md5($this->__data['out_trade_no'])));
        }

        /**
         * 调用微信支付接口的 spbill_create_ip
         */
        private function __setCreateIp() {
            $this->__data['spbill_create_ip'] = '127.0.0.1';
        }

        /**
         * @param $action 请求接口调用函数
         * @return array|mixed
         */
        private function __xmlToArray($action) {
            if (strncasecmp($this->__data['xml'], '<xml>', 5) != 0) {
                return array(
                    'err_code_id' => '400',
                    'err_code_des' => '请求中有语法问题，或不能满足请求',
                );
            }
            $data = json_decode(json_encode(simplexml_load_string($this->__data['xml'], 'SimpleXMLElement', LIBXML_NOCDATA)), true);
            if (strcasecmp($data['return_code'], 'SUCCESS') != 0) {
                $data = array(
                    'err_code_id' => $data['return_code'],
                    'err_code_des' => $data['return_msg'],
                );
            } else if (strcasecmp($data['result_code'], 'SUCCESS') != 0) {
                $data = array(
                    'err_code_id' => $data['err_code'],
                    'err_code_des' => $data['err_code_des'],
                );
            }
            if (!isset($data['err_code_id'])) {
                switch ($action) {
                    case 'microPay':    //扫码支付
                        if (!$this->__checkAttach($data['attach'])) {
                            $data = array(
                                'err_code_id' => '401',
                                'err_code_des' => '未授权客户机访问数据',
                            );
                        }
                        break;
                    case 'queryMicroPay':    //扫码支付
                        if (strcasecmp($data['trade_state'], 'SUCCESS') != 0) {
                            $data = array(
                                'err_code_id' => $data['trade_state'],
                                'err_code_des' => $data['trade_state_desc'],
                            );
                        }
                        break;
                    case 'unifiedOrderPay':    //统一下单
                        break;
                    case 'notifyUnifiedOrderPay':    //统一下单异步通知 调用业务并向支付中心响应处理结果
                        if (strcasecmp(base64_decode($data['attach']), base64_encode(md5(md5($data['out_trade_no'])))) == 0) {
                            if (true) {
                                echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
                            } else {
                                echo "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[数据业务错误]]></return_msg></xml>";
                            }
                        } else {
                            echo "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[数据校验错误]]></return_msg></xml>";
                        }
                        break;
                }
            }

            return $data;
        }

        /**
         * 附加认证 防止网络中间层截取模拟数据
         * @param $attach
         * @return int
         */
        private function __checkAttach($attach) {
            return strcasecmp($this->__data['attach'], $attach) == 0;
        }
    }