<?php
    class COption {
        private $__borrow, $__rule;
        
        public function __construct() {
            $this->__borrow = array(
                'car' => 1,
                'house' => 1,
            );
            
            $this->__rule = array(
                /**
                 * borrow.set 官方标
                 * borrow.get 用户提现
                 * borrow.special 内部提现
                 */
                1=>array(
                    'borrow.set' => array(
                        array(
                            'minimum' => 300,
                            'maximum' => 500,
                            'people' => 10,
                            'most' => 5,
                            'scope' => array(
                                'min' => 0,
                                'max' => 500,
                            ),
                        ),
                        array(
                            'minimum' => 100,
                            'maximum' => 500,
                            'people' => 10,
                            'most' => 5,
                            'scope' => array(
                                'min' => 500,
                                'max' => 1000,
                            ),
                        ),
                        array(
                            'minimum' => 50,
                            'maximum' => 500,
                            'people' => 10,
                            'most' => 5,
                            'scope' => array(
                                'min' => 5000,
                                'max' => 10000,
                            ),
                        ),
                        array(
                            'minimum' => 200,
                            'maximum' => 500,
                            'people' => 10,
                            'most' => 5,
                            'scope' => array(
                                'min' => 50000,
                                'max' => 100000,
                            ),
                        ),
                    ),
                    'borrow.get' => array(),
                    'borrow.special' => array(),
                ),
            );
        }
        
        public function get($category, $action, $cash = 0) {
            if(!isset($this->__borrow[$category]) || 
                !isset($this->__rule[$this->__borrow[$category]]) || 
                !isset($this->__rule[$this->__borrow[$category]][$action])) {
                return array(
                    'code' => 404,
                    'data' => "债权类型 {$category} 中未找到 {$action} 规则方法。"
                );
            }
            
            $data =  $this->__getSort($this->__rule[$this->__borrow[$category]][$action], 'minimum', 'DESC');
            $minimum = end($data);
            $index = $this->__getIndex($data, $cash);
            
            if($index === false) {
                return array(
                    'code' => 404,
                    'data' => "债权类型 {$category} 规则 {$action} 中未找到债权金额 {$cash} 所属范围值。"
                );
            }
            
            return array(
                'code' => 200,
                'data' => array_slice($data, $index, null, true),
                'minimum' => $minimum
            );
        }
        
        private function __getIndex($data, $cash) {
            foreach ($data as $k => $v) {
                if ($cash > $v['scope']['min'] && $cash <= $v['scope']['max']) {
                    return $k;
                }
            }

            return false;
        }
        
        private function __getSort($data, $key, $order = 'ASC') {
            $new_array = array();
            $sortable_array = array();

            if (count($data) > 0) {
                foreach ($data as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $k2 => $v2) {
                            if ($k2 == $key) {
                                $sortable_array[$k] = $v2;
                            }
                        }
                    } else {
                        $sortable_array[$k] = $v;
                    }
                }

                switch ($order) {
                    case 'ASC':
                        asort($sortable_array);
                        break;
                    case 'DESC':
                        arsort($sortable_array);
                        break;
                }

                foreach ($sortable_array as $k => $v) {
                    $new_array[] = $data[$k];
                }
            }

            return $new_array;
        }
    }
