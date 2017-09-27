<?php

    namespace Api\Information;

    class CData extends CBinary {
        private $__payload = false;
        private $__rule = false;
        private $__sign = false;
        private $__setting = false;
        private $__redis = false;

        public function __construct() {
            $this->__setting = include('setting.php');
            $this->__payload = include('payload.php');
            $this->__rule = include('rule.php');
            $this->__sign = array(
                0x1C
            );
            $this->__redis = new CRedis();
        }

        public function getIdentity() {
            return uniqid();
        }

        public function getSetting() {
            return $this->__setting;
        }

        public function getRule() {
            return $this->__rule;
        }

        public function getRedis() {
            return $this->__redis;
        }

        /**
         * @param $identity
         * @param $payload_id
         * @param $payload_data
         * @param bool $key 用于特殊情况处理，默认队列prefix send send
         */
        public function send($identity, $payload_id, $payload_data = false, $key = false) {
            if ($address = \Api\Model\InformationModel::getInterface()) {
                if (!in_array($payload_id, $address['rule'])) {
                    CLog::debug(sprintf("auth send {$address['host']} 0x%x deny", $payload_id));

                    return;
                }
            }

            if ($key == false) {
                $key = $this->__setting['prefix']['send']['send'];
            }
            //CLog::debug(sprintf("send {$key} {$identity} 0x%x", $payload_id));
            if ($binary = $this->__getArrayBinary($payload_id, $payload_data)) {
                $this->__redis->send($key, $identity, $binary);
            } else {
                CLog::debug(sprintf("send {$key} {$identity} 0x%x encrypt deny", $payload_id));
            }
        }

        public function post($identity, $data) {
            if ($address = \Api\Model\InformationModel::getInterface()) {
                if (!in_array(0, $address['rule'])) {
                    CLog::debug("auth post {$address['host']} {$identity} deny");

                    return false;
                }

                if ($binary = $this->getXmlBinary($address['report'], $data)) {
                    $this->__redis->send($this->__setting['prefix']['post']['send'], $identity, $binary);
                    //CLog::debug("send {$this->__setting['prefix']['post']['send']} {$identity}");
                    if ($xml = $this->accept($identity, $this->__setting['prefix']['post']['accept'])) {
                        //CLog::debug("read {$this->__setting['prefix']['post']['accept']} {$identity}");
                        return $this->getBinaryXml($xml);
                    }
                }

                return false;
            } else {
                CLog::debug('post read config deny');

                return false;
            }
        }

        /**
         * @param $identity
         * @param bool $key false则取缓存数据
         * @return bool|mixed
         */
        public function accept($identity, $key = false) {
            if ($key == false) {
                $key = $this->__setting['prefix']['data'];
            }

            if ($data = $this->__redis->accept($key, $identity)) {
                switch ($key) {
                    case $this->__setting['prefix']['post']['accept']:
                        return $data;
                    default:
                        if ($data = $this->__getBinaryArray($data)) {
                            return $data;
                        }
                }
            } else {
                CLog::debug("accept {$key} {$identity} deny");
            }

            return false;
        }

        public function read($binary, $key) {
            if ($data = $this->__getBinaryArray($binary)) {
                if ($address = \Api\Model\InformationModel::getInterface()) {
                    if (!in_array($data['payload_id'], $address['rule'])) {
                        CLog::debug(sprintf("auth read {$address['host']} 0x%x deny", $data['payload_id']));

                        return;
                    }
                }

                //CLog::debug(sprintf("read {$this->__setting['prefix']['send']['accept']} {$key} 0x%x", $data['payload_id']));

                if (isset($this->__rule[$data['payload_id']])) {
                    call_user_func_array(array(
                        D('Api/Information'),
                        $this->__rule[$data['payload_id']]
                    ), array(
                            array(
                                'key' => $key,
                                'data' => $data
                            )
                        ));
                }
            }
        }

        public function getBinaryList() {
            return $this->__redis->getBinaryList($this->__setting['prefix']['send']['accept'], $this->__setting['prefix']['data']);
        }

        /**
         * @param $payload_id
         * @param $payload_data
         * @return bool|string 长度+包内容
         */
        private function __getArrayBinary($payload_id, $payload_data) {
            $packet_length = 0;

            $refer = array(
                'sync_tag' => $this->encrypt16UInt($this->__setting['sync'], $packet_length),
                //同步标记
                'version' => $this->encrypt8UInt($this->__setting['version'], $packet_length),
                //版本
                'packet_length' => $this->encrypt16UInt(0, $packet_length),
                //报文总长度仅占位
                'payload_id' => $this->encrypt8UInt($payload_id, $packet_length)
                //协议体标识
            );
            $payload_data = $this->encryptData($this->__payload, $payload_id, $payload_data, $packet_length);

            //需要签名的数据,签名数据长度未包含
            if (in_array($payload_id, $this->__sign)) {
                $packet_length += 128;
                if ($sign = CUKey::call(CUKey::KEY_SIGN, md5($payload_data, true))) {
                    $payload_data .= $sign;
                } else {
                    return false;
                }
            }

            $refer['payload_data'] = $payload_data;   //协议体数据
            $refer['packet_length'] = $this->encrypt16UInt($packet_length + 2, $packet_length);   //重写报文总长度(+2 循环冗余校验码长度)

            //循环冗余校验码
            if ($crc = CUKey::call(CUKey::KEY_CRC, implode('', $refer))) {
                $refer['crc'] = $this->encrypt16UInt($crc, $packet_length);
            } else {
                return false;
            }

            $binary = implode('', $refer);
            $length = pack('i', strlen($binary));

            return $length . $binary;
        }

        private function __getBinaryArray($binary) {
            //数据包
            $data = array();

            //循环冗余校验码
            if ($crc = CUKey::call(CUKey::KEY_CRC, substr($binary, 0, -2))) {
                //校验数据包并解出信息
                if ($this->checkBinary($this->__setting, $binary, $crc, $data)) {
                    $this->decryptData($this->__payload, $binary, $data);
                } else {
                    return false;
                }
            }

            return $data;
        }
    }


