<?php
    namespace Api\HelpTicket;

    class Data {
        private $_payload = false;
        private $_rule = false;
        private $_ip = false;

        public function __construct($ip) {
            $this->_payload = include(APP_PATH . '/Api/HelpTicket/data.payload.php');
            $this->_rule = include(APP_PATH . '/Api/HelpTicket/data.rule.php');

            $this->_ip = $ip;
        }

        public function readData($binary) {
            if ($data = $this->__getArrayData($binary)) {
                if (($refer = call_user_func_array(array(
                    D('Api/HelpTicket'),
                    'getRequestData'
                ), array(
                    'actionName' => $this->_rule[$data['payload_id']]['F'],
                    'ip' => $this->_ip,
                    'data' => $data
                )))
                ) {
                    if ($binary = $this->__getBinaryData($this->_rule[$data['payload_id']]['B'], $refer)) {
                        return $binary;
                    }
                }
            }

            return false;
        }

        private function __getBinaryData($payload_id, $data) {
            $packet_length = 0;

            $refer = array(
                'sync_tag' => $this->__encrypt16UInt(C('TICKET_INTERFACE.SYNC'), $packet_length),
                //同步标记
                'version' => $this->__encrypt8UInt(C('TICKET_INTERFACE.VER'), $packet_length),
                //版本
                'packet_length' => $this->__encrypt16UInt(0, $packet_length),
                //报文总长度仅占位
                'payload_id' => $this->__encrypt8UInt($payload_id, $packet_length),
                //协议体标识
                'payload_data' => $this->__encryptData($payload_id, $data, $packet_length),
                //协议体数据
                'packet_length' => $this->__encrypt16UInt($packet_length + 2, $packet_length),
                //重写报文总长度(+2 循环冗余校验码长度)
            );
            $refer['crc'] = $this->__encrypt16UInt($this->__crc(implode('', $refer)), $packet_length);    //循环冗余校验码

            return implode('', $refer);
        }

        private function __getArrayData($binary) {
            //数据包
            $data = array();

            //校验数据包并解出信息
            if ($this->__checkBinary($binary, $data)) {
                $this->__decryptData($binary, $data);
            }

            return $data;
        }

        private function __checkBinary($binary, &$data) {
            $data['sync_tag'] = array(
                $this->__decryptHex(substr($binary, 0, 1)),
                $this->__decryptHex(substr($binary, 1, 1))
            );
            sort($data['sync_tag']);
            $data['sync_tag'] = implode('', array_map('strtoupper', $data['sync_tag']));
            $data['version'] = $this->__decryptHex(substr($binary, 2, 1));
            $data['packet_length'] = $this->__decrypt16UInt(substr($binary, 3, 2));
            $data['payload_id'] = $this->__decrypt8UInt(substr($binary, 5, 1));
            $data['crc'] = $this->__decrypt16UInt(substr($binary, -2));

            //校验同步标记
            if (strcasecmp($data['sync_tag'], sprintf('%X', C('TICKET_INTERFACE.SYNC'))) !== 0) {
                return false;
            }

            //校验版本
            if (strcasecmp(sprintf('%X', $data['version']), sprintf('%X', C('TICKET_INTERFACE.VER'))) !== 0) {
                return false;
            }

            //校验长度
            if (strlen($binary) !== $data['packet_length']) {
                return false;
            }

            //循环冗余校验
            if ($data['crc'] !== $this->__crc(substr($binary, 0, -2))) {
                return false;
            }

            return true;
        }

        private function __decryptData($binary, &$data) {
            $rule = $this->_payload[$data['payload_id']];   //取解包规则

            $position = 6;    //数据开始位置
            $data['payload_data'] = array();  //数据存放位置

            foreach ($rule as $key => $val) {
                //单个数据的处理
                if (!is_array($val)) {
                    $val = explode('|', $val);

                    //常规处理
                    if (count($val) == 2) {
                        list($rule, $length) = array_map('strtolower', $val);
                        $data['payload_data'][$key] = $this->__decryptDataExt($rule, $length, $binary, $position);
                    }

                    //此情况特殊处理,第三字段表示这个字段的长度字段
                    if (count($val) == 3) {
                        $data['payload_data'][$key] = $this->__decryptDataExt($val[0], $data['payload_data'][$val[2]], $binary, $position);
                    }

                    continue;
                }

                //循环体的处理
                if (is_array($val)) {
                    $length_key = $val['length'];
                    unset($val['length']);

                    for ($row = 0; $row < $data['payload_data'][$length_key]; $row++) {
                        foreach ($val as $row_key => $row_val) {
                            list($rule, $length) = array_map('strtolower', explode('|', $row_val));
                            $data['payload_data'][$key][$row][$row_key] = $this->__decryptDataExt($rule, $length, $binary, $position);
                        }
                    }
                }
            }
        }

        private function __decryptDataExt($rule, $length, $binary, &$position) {
            switch ($rule) {
                case 'int':
                    switch ($length) {
                        case '8':
                            $refer = $this->__decrypt8UInt(substr($binary, $position, 1));
                            $position += 1;

                            return $refer;
                        case '16':
                            $refer = $this->__decrypt16UInt(substr($binary, $position, 2));
                            $position += 2;

                            return $refer;
                        case '32':
                            $refer = $this->__decrypt32UInt(substr($binary, $position, 4));
                            $position += 4;

                            return $refer;
                    }
                case 'char':
                    $refer = $this->__decryptChar(substr($binary, $position, $length));
                    $position += $length;

                    return $refer;
                case 'float':
                    $refer = $this->__decryptFloat(substr($binary, $position, 4));
                    $position += 4;

                    return $refer;
                case 'datetime':
                    $refer = $this->__decryptDateTime(substr($binary, $position, 20));
                    $position += 20;

                    return $refer;
                case 'date':
                    $refer = $this->__decryptDate(substr($binary, $position, 11));
                    $position += 11;

                    return $refer;
                case 'time':
                    $refer = $this->__decryptTime(substr($binary, $position, 9));
                    $position += 9;

                    return $refer;
            }
        }

        private function __decryptHex($val) {
            return unpack('H2', $val)[1];
        }

        private function __decrypt8UInt($val) {
            return unpack('C', $val)[1];
        }

        private function __decrypt16UInt($val) {
            return unpack('v', $val)[1];
        }

        private function __decrypt32UInt($val) {
            return unpack('L', $val)[1];
        }

        private function __decryptFloat($val) {
            return unpack('f', $val)[1];
        }

        private function __decryptDateTime($val) {
            return strtotime($this->__decryptChar($val));
        }

        private function __decryptDate($val) {
            return strtotime($this->__decryptChar($val));
        }

        private function __decryptTime($val) {
            return strtotime($this->__decryptChar($val));
        }

        private function __decryptChar($val) {
            $val = iconv('gb18030', 'utf-8', $val);
            $val = unpack('C*', $val);
            $val = array_filter($val);

            return implode('', array_map('chr', $val));
        }

        private function __encryptData($payload_id, $data, &$packet_length) {
            foreach ($data as $key => $val) {
                $rule = $this->_payload[$payload_id][$key];

                //值是数组，查找对应的规则
                if (is_array($val)) {
                    if (isset($rule[$key]['length'])) {
                        unset($rule[$key]['length']);
                    }

                    //初始化规则
                    $list_rule = array();
                    foreach ($rule as $rule_key => $rule_val) {
                        $list_rule[$rule_key] = array_map('strtolower', explode('|', $rule_val));
                    }

                    //列表每一个值
                    $refer[$key] = '';
                    foreach ($val as $list) {
                        foreach ($list as $list_key => $list_val) {
                            $refer[$key] .= $this->__encryptDataExt($list_val, $list_rule[$list_key][0], $list_rule[$list_key][1], $packet_length);
                        }
                    }
                } else {
                    list($rule, $length) = array_map('strtolower', explode('|', $rule));
                    $refer[$key] = $this->__encryptDataExt($val, $rule, $length, $packet_length);
                }
            }

            return implode('', $refer);
        }

        private function __encryptDataExt($val, $rule, $length, &$packet_length) {
            switch ($rule) {
                case 'char':
                    return $this->__encryptChar($val, $length, $packet_length);
                case 'int':
                    switch ($length) {
                        case '8':
                            return $this->__encrypt8UInt($val, $packet_length);
                        case '16':
                            return $this->__encrypt16UInt($val, $packet_length);
                        case '32':
                            return $this->__encrypt32UInt($val, $packet_length);
                    }
                case 'float':
                    return $this->__encryptFloat($val, $packet_length);
                case 'datetime':
                    return $this->__encryptDateTime($val, $packet_length);
                case 'date':
                    return $this->__encryptDate($val, $packet_length);
                case 'time':
                    return $this->__encryptTime($val, $packet_length);
            }
        }

        private function __encrypt16Int($val, &$packet_length) {
            $packet_length += 2;

            return pack('s', $val);
        }

        private function __encryptInt($val, &$packet_length) {
            $packet_length += 4;

            return pack('i', $val);
        }

        private function __encrypt8UInt($val, &$packet_length) {
            $packet_length += 1;

            return pack('C', $val);
        }

        private function __encrypt16UInt($val, &$packet_length) {
            $packet_length += 2;

            return pack('v', $val);
        }

        private function __encrypt32UInt($val, &$packet_length) {
            $packet_length += 4;

            return pack('L', $val);
        }

        private function __encryptFloat($val, &$packet_length) {
            $packet_length += 4;

            return pack('f', $val);
        }

        private function __encryptDateTime($val, &$packet_length) {
            return $this->__encryptChar(date('Y-m-d\TH:i:s', $val), 20, $packet_length);
        }

        private function __encryptDate($val, &$packet_length) {
            return $this->__encryptChar(date('Y-m-d', $val), 11, $packet_length);
        }

        private function __encryptTime($val, &$packet_length) {
            return $this->__encryptChar(date('H:i:s', $val), 9, $packet_length);
        }

        private function __encryptChar($val, $bit, &$packet_length) {
            $val = array_map('ord', str_split(iconv('utf-8', 'gb18030', $val)));
            $packet_length += $bit;

            $size = 0;
            $refer = '';
            foreach ($val as $char) {
                $size += 1;
                $refer .= pack('C', $char);
            }

            for (; $size < $bit; $size++) {
                $refer .= pack('C', ord("\0"));
            }

            return $refer;
        }

        private function __crc($data) {
            return \Api\Information\CUKey::call(\Api\Information\CUKey::KEY_CRC, $data);
        }

        public function __destruct() {
            call_user_func_array(array(
                D('Api/HelpTicket'),
                'logout'
            ), array(
                'ip' => $this->_ip
            ));
        }
    }

?>
