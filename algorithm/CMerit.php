<?php

    /**
     * 多次转让公充价值计算
     * 提前正常还款 支援
     * 提前+1期还款 支援
     * Class CMerit
     */
    class CMerit extends CDict {
        public function __construct($decimalDigits = 2) {
            parent::__construct($decimalDigits);
        }

        /**
         * 债权公允价值
         * @param $data
         * @return float/bool
         */
        protected function get($data) {
            $this->__setFormatData($data);  //地址引用 为转让债权时剩余期数未还期数 leaving.period 赋值
            if ($action = $this->getRepaymentAction($data['original']['type'])) {
                return $this->$action($data);
            }

            return false;
        }

        /**
         * 等额本息
         * @param $data
         * @return float
         */
        private function __getAverageCapitalPlusInterest($data) {
            $original = array();//原始债权还款计划表
            $leavings = array();//原始债权剩余期数还款计划表
            $totalPrincipal = 0;
            $totalInterest = 0;

            $data['original']['apr'] /= 12;
            $repayTotal = $data['leavings']['principal'] * $data['original']['apr'] * pow(1 + $data['original']['apr'], $data['leavings']['period']) / (pow(1 + $data['original']['apr'], $data['leavings']['period']) - 1);
            $repayTotalFloorDigits = $this->getFloorDigits($repayTotal);
            $acceptDate = date_create($data['accept']['date']->format('Y-m-d'));

            $calendar = date_create($data['original']['date']->format('Y-m-1'));
            for ($i = 1, $j = 1; $i <= $data['original']['period']; $i++) {
                $totalDay = cal_days_in_month(CAL_GREGORIAN, $calendar->format('m'), $calendar->format('Y'));
                $calendar->modify('+1 month');

                $originalStartDate = date_create($data['original']['date']->format('Y-m-d'));//取本期开始与结束时间
                $data['original']['date']->modify("+{$totalDay} days");
                $originalEndDate = date_create($data['original']['date']->format('Y-m-d'));

                $original[$i] = array(
                    'startDate' => $originalStartDate,
                    'endDate' => $originalEndDate,
                    'totalDay' => $totalDay,
                    'repayTotal' => $repayTotalFloorDigits,
                );

                if ($originalStartDate->diff($data['leavings']['date'])->format('%R%a') >= 0 && $originalEndDate->diff($data['leavings']['date'])->format('%R%a') < 0) {//提现时间所在期数
                    $data['leavings']['current'] = $i;
                }

                if ($originalStartDate->diff($acceptDate)->format('%R%a') >= 0 && $originalEndDate->diff($acceptDate)->format('%R%a') < 0) {//承接时间所在期数
                    $data['accept']['period'] = $i;
                }

                if ($i > $data['original']['repaid']) {//未还期开始
                    $havingDay = 0;
                    $currentInterest = $this->getFloorDigits(($data['leavings']['principal'] * $data['original']['apr'] - $repayTotal) * pow(1+$data['original']['apr'], $j++ - 1) + $repayTotal);
                    $currentPrincipal = $repayTotalFloorDigits- $currentInterest;

                    if($i==$data['original']['period']) {//修正最后一期本金，重设最后一期利息
                        $currentPrincipal = $data['leavings']['principal'] - $totalPrincipal;
                        $currentInterest = $this->getFloorDigits($currentPrincipal *  $data['original']['apr']);
                    } else {
                        $totalPrincipal += $currentPrincipal;
                    }

                    $original[$i]['principal'] = $currentPrincipal;
                    $original[$i]['interest'] = $currentInterest;

                    if($data['leavings']['current']==$i) {//正常还款中提现当期应计利息
                        $havingDay = $originalStartDate->diff($data['leavings']['date'])->format('%R%a');
                        if($havingDay > $totalDay) {//修正严重预期时当前期开始时间到转让时间的天数将超过当前期天数
                            $havingDay = $totalDay;
                        }
                        $currentInterest = $this->getFloorDigits($havingDay / $totalDay * $currentInterest);
                    } else if($data['leavings']['current']>$i) {//逾期应计利息
                        $havingDay = $totalDay;
                    }

                    if($havingDay <= 0) {//持有天数为0, 应计利息为0
                        $currentInterest =0;
                    }

                    $leavings[$i] = array(
                        'havingDay' => $havingDay,
                        'principal' => $currentPrincipal,
                        'interest' => $currentInterest
                    );

                    $totalInterest += $currentInterest;
                }
            }

            if ($data['leavings']['current'] <= $data['original']['repaid']) {//提前还款
                if($data['leavings']['current'] == $data['accept']['period']) {//提现所在期数=承接时间所在期数 提前正常还款
                    if(date_diff($data['accept']['date'], $data['last']['date'])->format('%R%s')<0) {//先还款再承接 则最后还款利息为负数
                        $havingDay = $data['leavings']['date']->diff($original[$data['leavings']['current']]['endDate'])->format('%R%a');//取提现时间到本期结束时间的天数为需要退款的天数
                        $totalInterest += $this->getFloorDigits($havingDay / $acceptDate->diff($original[$data['leavings']['current']]['endDate'])->format('%R%a') * $data['accept']['interest']);
                    } else {//先承接再还款 则还款利息为负数
                        $havingDay = $original[$data['leavings']['current']]['endDate']->diff($data['leavings']['date'])->format('%R%a');
                        $totalInterest += $this->getFloorDigits($havingDay / $original[$data['leavings']['current']]['totalDay'] * $data['last']['interest']);
                    }
                }  else if($data['leavings']['current']>$data['accept']['period']) {//提现所在期数>提现用户承接的期数，超期还款  提前正常还款+1期的支持
                    $havingDay = $original[$data['leavings']['current']]['endDate']->diff($data['leavings']['date'])->format('%R%a');
                    $totalInterest += $this->getFloorDigits($havingDay / $original[$data['leavings']['current']]['totalDay'] * $data['last']['interest']);
                }
            }

            return $this->getFloorDigits($totalInterest);
        }

        /**
         * 按月付息到期还本
         * @param $data
         * @return float
         */
        private function __getEveryMonth($data) {
            $original = array();//原始债权还款计划表
            $leavings = array();//原始债权剩余期数还款计划表
            $totalInterest = 0;

            $interest = $this->getFloorDigits($data['leavings']['principal'] * $data['original']['apr'] / 12);//每月还款利息
            $acceptDate = date_create($data['accept']['date']->format('Y-m-d'));

            $calendar = date_create($data['original']['date']->format('Y-m-1'));
            for ($i = 1; $i <= $data['original']['period']; $i++) {
                $totalDay = cal_days_in_month(CAL_GREGORIAN, $calendar->format('m'), $calendar->format('Y'));
                $calendar->modify('+1 month');

                $originalStartDate = date_create($data['original']['date']->format('Y-m-d'));//取本期开始与结束时间
                $data['original']['date']->modify("+{$totalDay} days");
                $originalEndDate = date_create($data['original']['date']->format('Y-m-d'));

                $original[$i] = array(
                    'startDate' => $originalStartDate,
                    'endDate' => $originalEndDate,
                    'totalDay' => $totalDay,
                    'interest' => $interest,
                );

                if ($originalStartDate->diff($data['leavings']['date'])->format('%R%a') >= 0 && $originalEndDate->diff($data['leavings']['date'])->format('%R%a') < 0) {//提现时间所在期数
                    $data['leavings']['current'] = $i;
                }

                if ($originalStartDate->diff($acceptDate)->format('%R%a') >= 0 && $originalEndDate->diff($acceptDate)->format('%R%a') < 0) {//承接时间所在期数
                    $data['accept']['period'] = $i;
                }

                if ($i > $data['original']['repaid']) {//当期期数>已还期数 未还期开始
                    $havingDay = 0;
                    if ($data['leavings']['current'] == $i) {//正常还款当期应计利息
                        $havingDay = $originalStartDate->diff($data['leavings']['date'])->format('%R%a');
                        if($havingDay > $totalDay) {//修正严重预期时当前期开始时间到转让时间的天数将超过当前期天数
                            $havingDay = $totalDay;
                        }
                    } else if ($data['leavings']['current'] > $i) {
                        $havingDay = $totalDay;
                    }

                    $currentInterest = $this->getFloorDigits($havingDay / $totalDay * $interest);
                    $totalInterest += $currentInterest;

                    $leavings[$i] = array(
                        'havingDay' => $havingDay,
                        'interest' => $currentInterest
                    );
                }
            }

            if ($data['leavings']['current'] <= $data['original']['repaid']) {//提前还款 请注意此处仅支持提前正常还款
                if($data['leavings']['current'] == $data['accept']['period']) {//提现所在期数=承接时间所在期数
                    if(date_diff($data['accept']['date'], $data['last']['date'])->format('%R%s')<0) {//先还款再承接 则最后还款利息为负数
                        $havingDay = $data['leavings']['date']->diff($original[$data['leavings']['current']]['endDate'])->format('%R%a');//取提现时间到本期结束时间的天数为需要退款的天数
                        $totalInterest += $this->getFloorDigits($havingDay / $acceptDate->diff($original[$data['leavings']['current']]['endDate'])->format('%R%a') * $data['accept']['interest']);
                    } else {//先承接再还款 则还款利息为负数
                        $havingDay = $original[$data['leavings']['current']]['endDate']->diff($data['leavings']['date'])->format('%R%a');   //此持有天数为需要退款的天数 负数
                        $totalInterest += $this->getFloorDigits($havingDay / $original[$data['leavings']['current']]['totalDay'] * $interest);
                    }
                } else if($data['leavings']['current']>$data['accept']['period']) {//提现所在期数>提现用户承接的期数，超期还款  提前正常还款+1期的支持
                    $havingDay = $original[$data['leavings']['current']]['endDate']->diff($data['leavings']['date'])->format('%R%a');   //此持有天数为需要退款的天数 负数
                    $totalInterest += $this->getFloorDigits($havingDay / $original[$data['leavings']['current']]['totalDay'] * $interest);
                }
            }

            return $this->getFloorDigits($totalInterest);
        }

        /**
         * 到期还本付息(月) 期数为多少月
         * @param $data
         * @return float
         */
        private function __getMonth($data) {
            $originalStartDate = date_create($data['original']['date']->format('Y-m-d'));
            $originalEndDate = date_create($data['original']['date']->modify("+{$data['original']['period']} month")->format('Y-m-d'));

            $havingDay = $originalStartDate->diff($data['leavings']['date'])->format('%R%a');
            $totalDay = date_diff($originalStartDate, $originalEndDate)->format('%R%a');
            if ($havingDay > $totalDay) {
                $havingDay = $totalDay;
            }
            $apr = $data['leavings']['principal'] * $data['original']['apr'] / 12 * $data['original']['period'] / $totalDay * $havingDay;

            return $this->getFloorDigits($apr);
        }

        /**
         * 到期还本付息(天) 期数为多少天
         * @param $data
         * @return float
         */
        private function __getDay($data) {
            $havingDay = $data['original']['date']->diff($data['leavings']['date'])->format('%R%a');
            if ($havingDay > $data['original']['period']) {
                $havingDay = $data['original']['period'];
            }
            $apr = $data['original']['apr'] / 365 * $havingDay * $data['leavings']['principal'];

            return $this->getFloorDigits($apr);
        }

        /**
         * 验证参数并格式化日期参数
         * 地址引用 为转让债权时剩余期数未还期数 leaving.period 赋值
         * @param $data
         * @throws Exception
         */
        private function __setFormatData(&$data) {
            $dateFields = array(
                'original' => 'Y-m-d',
                'leavings' => 'Y-m-d',
                'accept' => 'Y-m-d H:i:s',
                'last' => 'Y-m-d H:i:s',
            );
            foreach ($dateFields as $key=>$format) {
                $data[$key]['date'] = $data[$key]['date'] ? date_create(date($format, $data[$key]['date'])) : false;
            }

            if(date_diff($data['accept']['date'], $data['leavings']['date'])->format('%R%a') < 0) {
                throw new \Exception('承接时间 大于 提现时间。还未承接，却要转让。', 412);
            }

            $data['accept']['period'] = 1;//用户上次承接时间所在期数默认为第1期
            $data['leavings']['period'] = $data['original']['period'] - $data['original']['repaid'];//转让时债权剩余期数未还期数
            $data['leavings']['current'] = $data['original']['period'];//用户转让时间所在期数默认为最后一期防止逾期无法找到转让时间所在的期数
        }
    }