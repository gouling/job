<?php

    namespace Api\Information;

    class CBinary {
        protected function checkBinary($setting, $binary, $crc, &$data) {
            $data['sync_tag'] = array(
                $this->decryptHex(substr($binary, 0, 1)),
                $this->decryptHex(substr($binary, 1, 1))
            );
            sort($data['sync_tag']);
            $data['sync_tag'] = implode('', array_map('strtoupper', $data['sync_tag']));
            $data['version'] = $this->decryptHex(substr($binary, 2, 1));
            $data['packet_length'] = $this->decrypt16UInt(substr($binary, 3, 2));
            $data['payload_id'] = $this->decrypt8UInt(substr($binary, 5, 1));
            $data['crc'] = $this->decrypt16UInt(substr($binary, -2));

            //校验同步标记
            if (strcasecmp($data['sync_tag'], sprintf('%X', $setting['sync'])) !== 0) {
                return false;
            }

            //校验版本
            if (strcasecmp(sprintf('%X', $data['version']), sprintf('%X', $setting['version'])) !== 0) {
                return false;
            }

            //校验长度
            if (strlen($binary) !== $data['packet_length']) {
                return false;
            }

            //循环冗余校验
            if ($data['crc'] !== $crc) {
                return false;
            }

            return true;
        }

        protected function decryptData($payload, $binary, &$data) {
            $rule = $payload[$data['payload_id']];   //取解包规则

            $position = 6;    //数据开始位置
            $data['payload_data'] = array();  //数据存放位置

            foreach ($rule as $key => $val) {
                //单个数据的处理
                if (!is_array($val)) {
                    $val = explode('|', $val);

                    //常规处理
                    if (count($val) == 2) {
                        list($rule, $length) = array_map('strtolower', $val);
                        if ($key == 'public_key') {
                            $data['payload_data'][$key] = substr($binary, $position, $length);
                        } else {
                            $data['payload_data'][$key] = $this->decryptDataExt($rule, $length, $binary, $position);
                        }
                    }

                    //此情况特殊处理,第三字段表示这个字段的长度字段
                    if (count($val) == 3) {
                        $data['payload_data'][$key] = $this->decryptDataExt($val[0], $data['payload_data'][$val[2]], $binary, $position);
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
                            $data['payload_data'][$key][$row][$row_key] = $this->decryptDataExt($rule, $length, $binary, $position);
                        }
                    }
                }
            }
        }

        protected function decryptDataExt($rule, $length, $binary, &$position) {
            $data = false;

            switch ($rule) {
                case 'int':
                    switch ($length) {
                        case '8':
                            $data = $this->decrypt8UInt(substr($binary, $position, 1));
                            $position += 1;
                            break;
                        case '16':
                            $data = $this->decrypt16UInt(substr($binary, $position, 2));
                            $position += 2;
                            break;
                        case '32':
                            $data = $this->decrypt32UInt(substr($binary, $position, 4));
                            $position += 4;
                            break;
                    }
                    break;
                case 'char':
                    $data = $this->decryptChar(substr($binary, $position, $length));
                    $position += $length;
                    break;
                case 'float':
                    $data = $this->decryptFloat(substr($binary, $position, 4));
                    $position += 4;
                    break;
                case 'datetime':
                    $data = $this->decryptDateTime(substr($binary, $position, 20));
                    $position += 20;
                    break;
                case 'date':
                    $data = $this->decryptDate(substr($binary, $position, 11));
                    $position += 11;
                    break;
                case 'time':
                    $data = $this->decryptTime(substr($binary, $position, 9));
                    $position += 9;
                    break;
            }

            return $data;
        }

        protected function encryptData($payload, $payload_id, $data, &$packet_length) {
            $refer = array();

            foreach ($data as $key => $val) {
                $rule = $payload[$payload_id][$key];

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
                            $refer[$key] .= $this->encryptDataExt($list_val, $list_rule[$list_key][0], $list_rule[$list_key][1], $packet_length);
                        }
                    }
                } else {
                    list($rule, $length) = array_map('strtolower', explode('|', $rule));
                    $refer[$key] = $this->encryptDataExt($val, $rule, $length, $packet_length);
                }
            }

            return implode('', $refer);
        }

        protected function encryptDataExt($val, $rule, $length, &$packet_length) {
            $binary = false;

            switch ($rule) {
                case 'char':
                    $binary = $this->encryptChar($val, $length, $packet_length);
                    break;
                case 'int':
                    switch ($length) {
                        case '8':
                            $binary = $this->encrypt8UInt($val, $packet_length);
                            break;
                        case '16':
                            $binary = $this->encrypt16UInt($val, $packet_length);
                            break;
                        case '32':
                            $binary = $this->encrypt32UInt($val, $packet_length);
                            break;
                    }
                    break;
                case 'float':
                    $binary = $this->encryptFloat($val, $packet_length);
                    break;
                case 'datetime':
                    $binary = $this->encryptDateTime($val, $packet_length);
                    break;
                case 'date':
                    $binary = $this->encryptDate($val, $packet_length);
                    break;
                case 'time':
                    $binary = $this->encryptTime($val, $packet_length);
                    break;
            }

            return $binary;
        }

        protected function encrypt16Int($val, &$packet_length) {
            $packet_length += 2;

            return pack('s', $val);
        }

        protected function encryptInt($val, &$packet_length) {
            $packet_length += 4;

            return pack('i', $val);
        }

        protected function encrypt8UInt($val, &$packet_length) {
            $packet_length += 1;

            return pack('C', $val);
        }

        protected function encrypt16UInt($val, &$packet_length) {
            $packet_length += 2;

            return pack('v', $val);
        }

        protected function encrypt32UInt($val, &$packet_length) {
            $packet_length += 4;

            return pack('L', $val);
        }

        protected function encryptFloat($val, &$packet_length) {
            $packet_length += 4;

            return pack('f', $val);
        }

        protected function encryptDateTime($val, &$packet_length) {
            return $this->encryptChar(date('Y-m-d\TH:i:s', $val), 20, $packet_length);
        }

        protected function encryptDate($val, &$packet_length) {
            return $this->encryptChar(date('Y-m-d', $val), 11, $packet_length);
        }

        protected function encryptTime($val, &$packet_length) {
            return $this->encryptChar(date('H:i:s', $val), 9, $packet_length);
        }

        protected function encryptChar($val, $bit, &$packet_length) {
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

        protected function decryptHex($val) {
            return unpack('H2', $val)[1];
        }

        protected function decrypt8UInt($val) {
            return unpack('C', $val)[1];
        }

        protected function decrypt16UInt($val) {
            return unpack('v', $val)[1];
        }

        protected function decrypt32UInt($val) {
            return unpack('L', $val)[1];
        }

        protected function decryptFloat($val) {
            return unpack('f', $val)[1];
        }

        protected function decryptDateTime($val) {
            return strtotime($this->decryptChar($val));
        }

        protected function decryptDate($val) {
            return strtotime($this->decryptChar($val));
        }

        protected function decryptTime($val) {
            return strtotime($this->decryptChar($val));
        }

        protected function decryptChar($val) {
            $val = iconv('gb18030', 'utf-8', $val);
            $val = unpack('C*', $val);
            $val = array_filter($val);
            $val = implode('', array_map('chr', $val));

            return $val;
        }

        /**
         * @param $address host api port
         * @param $data
         * @return string 长度+包内容
         */
        protected function getXmlBinary($address, $data) {
            $address['data'] = $data;
            foreach ($address as $key => $val) {
                $binary = pack('a*', $val);
                $length = pack('i', strlen($binary));

                $address[$key] = $length . $binary;
            }

            return implode('', $address);
        }

        protected function getBinaryXml($binary) {
            return (array)simplexml_load_string($binary);
        }
    }