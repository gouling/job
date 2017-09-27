<?php

    /**
     * Class APPay 支付宝支付[不含退款]，此接口已内置不对称RSA签名验证
     * 业务调用任何接口返回数据后应先验证array('err_code_id'=>是否设置, 'err_code_des'=>已设置err_code_id错误信息)
     * pay 扫码支付
     * query 查询支付情况
     * http://app.alipay.com/market/document.htm?name=tiaomazhifu
     */
    class APPay {
        private $__api = 'https://openapi.alipay.com/gateway.do';
        private $__pay = 'alipay.trade.pay';
        private $__query = 'alipay.trade.query';
        private $__data, $__http, $__http_data, $__http_opt;
        private $__public_key, $__private_key;

        public function __construct($data) {
            $this->__data = array(
                'app_id' => $data['app_id'],
                'charset' => 'utf-8',
                'version' => '1.0',
                'sign_type' => 'RSA',
                'timestamp' => date('Y-m-d H:i:s')
            );
            $this->__public_key = openssl_get_publickey($data['public_key']);
            $this->__private_key = openssl_get_privatekey($data['private_key']);
            $this->__http_opt = array(
                CURLOPT_URL => $this->__api,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => 0,
                CURLOPT_CONNECTTIMEOUT => $data['timeout'],
                CURLOPT_TIMEOUT => $data['timeout'],
                CURLOPT_POST => 1,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            );
        }

        public function pay($data) {
            $this->__data['method'] = $this->__pay;
            $this->__data['biz_content'] = json_encode(array_merge($data, array('scene' => 'bar_code')));

            return $this->__getData($this->__pay);
        }

        public function query($data) {
            $this->__data['method'] = $this->__query;
            $this->__data['biz_content'] = json_encode($data);

            return $this->__getData($this->__query);
        }

        private function __getData($response) {
            $response = str_replace('.', '_', $response) . '_response';
            $this->__setPrepareSignParams();
            $this->__setPrivateKeySign();
            $this->__postData();

            return $this->__jsonToArray($response);
        }

        private function __postData() {
            $this->__http = curl_init();
            $this->__http_opt[10015] = $this->__data;
            foreach ($this->__http_opt as $key => $val) {
                curl_setopt($this->__http, $key, $val);
            }
            $this->__http_data = curl_exec($this->__http);
            curl_close($this->__http);
        }

        private function __setPrepareSignParams() {
            ksort($this->__data);
            if (isset($this->__data['sign'])) {
                unset($this->__data['sign']);
            }
            foreach ($this->__data as $key => $val) {
                $this->__data['sign'][] = "{$key}=$val";
            }
        }

        private function __setPrivateKeySign() {
            $this->__data['sign'] = implode('&', $this->__data['sign']);
            if (openssl_sign($this->__data['sign'], $signature, $this->__private_key)) {
                $this->__data['sign'] = base64_encode($signature);
            } else {
                throw new Exception('Unable to encrypt data. Perhaps it is bigger than the key size?');
            }
        }

        private function __jsonToArray($response) {
            $this->__http_data = iconv('gb2312', 'utf-8', $this->__http_data);
            if (!is_null($this->__http_data = json_decode($this->__http_data, true)) && is_array($this->__http_data)) {
                /**
                 * 10000 成功
                 * 10003 处理中 返回此状态码需要查询是否成功
                 * 其它情况则交易失败
                 */
                if ($this->__http_data[$response]['code'] == '10000' || $this->__http_data[$response]['code'] == '10003') {
                    if (!$this->__checkSign($response)) {
                        return array(
                            'err_code_id' => '401',
                            'err_code_des' => '未授权客户机访问数据[验证数据签名异常]'
                        );
                    }
                    if ($this->__http_data[$response]['code'] == '10000') {
                        return $this->__http_data;
                    }
                    if ($this->__http_data[$response]['code'] == '10003') {
                        return array(
                            'err_code_id' => $this->__http_data[$response]['code'],
                            'err_code_des' => "{$this->__http_data[$response]['msg']}"
                        );
                    }
                } else {
                    return array(
                        'err_code_id' => $this->__http_data[$response]['code'],
                        'err_code_des' => "{$this->__http_data[$response]['sub_code']}:{$this->__http_data[$response]['sub_msg']}"
                    );
                }
            } else {
                return array(
                    'err_code_id' => '400',
                    'err_code_des' => '请求中有语法问题，或不能满足请求'
                );
            }
        }

        private function __checkSign($response) {
            $signature = base64_decode($this->__http_data['sign']);
            $data = json_encode($this->__http_data[$response]);

            return ( bool )openssl_verify($data, $signature, $this->__public_key);
        }

        public function __destruct() {
            openssl_free_key($this->__public_key);
            openssl_free_key($this->__private_key);
        }
    }