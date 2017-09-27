<?php

    /**
     * Date: 6/1/16
     * Time: 6:51 PM
     */
    class CDict {
        const REPAYMENT_DAY = 1;
        const REPAYMENT_MONTH = 2;
        const REPAYMENT_EVERY_MONTH = 3;
        const REPAYMENT_AVERAGE_CAPITAL_PLUS_INTEREST = 4;
        private $__repaymentType = array();
        private $__decimalDigits = array();

        public function __construct($decimalDigits = 2) {
            bcscale($decimalDigits);
            $this->__decimalDigits = array(
                'round' => $decimalDigits,
                'floor' => $decimalDigits,
                'ceil' => pow(10, $decimalDigits),
            );

            $this->__repaymentType = array(
                self::REPAYMENT_DAY => '__getDay',
                self::REPAYMENT_MONTH => '__getMonth',
                self::REPAYMENT_EVERY_MONTH => '__getEveryMonth',
                self::REPAYMENT_AVERAGE_CAPITAL_PLUS_INTEREST => '__getAverageCapitalPlusInterest',
            );
        }

        public function setDecimalDigits($decimalDigits = 2) {
            $this->__construct($decimalDigits);
        }

        private function __getLeastMinimum($data, $optionList) {
            return min($this->getArrayColumn($optionList[$data['action']], 'minimum'));
        }

        public function getOption($data, $optionList) {
            $least = $this->__getLeastMinimum($data, $optionList);
            $option = array_map(function ($val) use ($least) {
                $val['scope'] = json_decode($val['scope'], true);
                $val['least'] = $least;
                unset($val['action']);
                return $val;
            }, $this->getArraySort($optionList[$data['action']], 'minimum', 'DESC'));

            $index = $this->getBaseOptionIndex($option, $data['data']['cash']);
            if ($index !== false) {
                return array_slice($option, $index, null, true);
            } else {
                return false;
            }
        }

        public function getArrayColumn($data, $column) {
            return array_map(function ($element) use ($column) {
                return $element[$column];
            }, $data);
        }

        public function getArraySort($data, $key, $order = 'ASC') {
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

        public function getBaseOptionIndex($option, $cash) {
            foreach ($option as $key => $val) {
                if ($cash > $val['scope']['min'] && $cash <= $val['scope']['max']) {
                    return $key;
                }
            }

            return false;
        }

        public function getJsonExt($data) {
            if(defined('JSON_UNESCAPED_UNICODE')){
                return json_encode($data, JSON_UNESCAPED_UNICODE);
            }

            $refer = json_encode($data);
            return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', create_function(
                '$matches',
                'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
            ), $refer);
        }

        protected function getRepaymentAction($repayment) {
            return isset($this->__repaymentType[$repayment]) ? $this->__repaymentType[$repayment] : false;
        }

        /**
         * 向上保留小数位
         * @param $number
         * @return float|int
         */
        protected function getCeilDigits($number) {
            $number = number_format($number, 8, '.', '');

            return ceil($number * $this->__decimalDigits['ceil']) / $this->__decimalDigits['ceil'];
        }

        /**
         * 向下保留小数位
         * @param $number
         * @return float|int
         */
        protected function getFloorDigits($number) {
            $number = number_format($number, 8, '.', '');
            preg_match('/^[-\d]\d*\.\d{' . $this->__decimalDigits['floor'] . '}/i', $number , $ref);

            return doubleval($ref[0]);
        }

        /**
         * 四舍五入
         * @param $number
         * @return float
         */
        protected function getRoundDigits($number) {
            return round($number, $this->__decimalDigits['round']);
        }
    }