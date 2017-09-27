<?php

    /**
     * 匹配
     * Date: 4/8/16
     * Time: 3:04 PM
     */
    class CAlgorithm extends CMerit {
        public function __construct($decimalDigits = 2) {
            parent::__construct($decimalDigits);
        }

        /**
         * 获取需要转让的债权信息
         * @param $borrowList
         * @param $optionList
         * @param $data
         * @return mixed
         */
        public function getMerit($borrowList, $optionList, $data) {
            $totalRequireCash = 0;
            $get = $data;
            $this->__setMerit($borrowList, $data);
            $optionList = array_map(function ($option) {
                $option['scope'] = json_decode($option['scope'], true);
                return $option;
            }, $optionList[$data['action']]);

            foreach ($borrowList as $key => &$borrow) {
                $borrow['data'] = array_merge($data['data'], array(
                    'id' => $key,  //标识
                    'merit' => array_sum($borrow['merit']),
                    'people' => $borrow['original']['people'],
                ));

                $index = $this->getBaseOptionIndex($optionList, $borrow['data']['merit']);
                if ($index !== false) {
                    $borrow['option'] = $optionList[$index];
                    if($currentMerit = $this->__getMerit($borrow, $get)) {
                        $totalRequireCash += $borrow['data']['cash'] = $currentMerit['principal'] + $currentMerit['interest'] ;
                        $borrow['require'] = $currentMerit;
                        //债权全部转让且持有人数小于等于设定最大投人数，持有人数减去自己
                        if($borrow['merit']['principal'] == $currentMerit['principal'] && $borrow['data']['people']<=$borrow['option']['people']) {
                            $borrow['data']['people'] -= 1;
                        }
                        $borrow = array_merge($data, $borrow);
                        unset($borrow['original'], $borrow['leavings'], $borrow['accept'], $borrow['last'], $borrow['option'], $borrow['data']['merit'], $borrow['data']['let']);
                        //unset($borrow['merit'], $borrow['require']);
                        continue;
                    }
                }

                unset($borrowList[$key]);
            }

            return array(
                'borrowList' => $borrowList,
                'totalRequireCash' => $totalRequireCash
            );
        }

        /**
         * 承接债权
         * @param $data
         * @param $user
         * @return int
         */
        public function accept(&$data, &$user) {
            $balance = 0;
            if (bccomp($user['param']['enableBalance'], 0) == 0 || bccomp($data['param']['cash'], 0) == 0) {
                /**
                 * 用户不参与投标条件
                 * 投标人数=设定人数 $data['param']['people'] == $data['option']['people'] 2017-06-30 取消此限制
                 * 用户可用于投标余额为0
                 * 标余额为0
                 */
                $balance = 0;
            } else if (bccomp($data['param']['cash'], $data['option']['minimum'] * 2) < 0 || $data['param']['people'] + 1 >= $data['option']['people']) {
                /**
                 * 标金额<=2倍单人单标最小投金额 仅一人参与投标
                 * 已投人数+1>=标配置最大人数 仅一人参与投标
                 * 标金额<=单人单标最大投金额
                 *  用户金额==标金额 或者 用户金额 >= 标金额+单人单标最小投金额
                 *  真 投入金额=标金额
                 */
                if (bccomp($data['param']['cash'], $data['option']['maximum']) < 1) {
                    if (bccomp($user['param']['enableBalance'], $data['param']['cash']) == 0 || bccomp($user['param']['enableBalance'], $data['param']['cash'] + $data['option']['minimum']) > -1) {
                        $balance = $data['param']['cash'];
                    }
                }
            } else {
                if (bccomp($user['param']['enableBalance'], $data['param']['cash']) == 0) { //用户金额与标金额相同
                    /**
                     * 标的金额 > 单人单标最大投金额
                     * 单人单标最大投金额 > 标金额-单人单标最小投金额(上行代码提取)
                     * 真 投入金额=标金额-单人单标最小投金额(上行代码提取)
                     * 假 投入金额=单人单标最大投金额
                     * 投入金额=单人单标最多可投金额
                     */
                    $ref = $data['param']['cash'] - $data['option']['minimum'];
                    $balance = bccomp($data['param']['cash'], $data['option']['maximum']) == 1 ? (bccomp($data['option']['maximum'], $ref) == 1 ? $ref : $data['option']['maximum']) : $data['param']['cash'];
                } else if (bccomp($user['param']['enableBalance'], $data['param']['cash']) == -1) {   //用户金额<标金额
                    if (bccomp($user['param']['enableBalance'], $data['param']['cash'] - $data['option']['minimum']) < 1) { //用户金额<=标金额-单人单标最小投金额
                        /**
                         * 用户金额<=单人单标最大投金额
                         * 真 投入金额=用户金额
                         * 假
                         *  用户金额-单人单标最大投金额>=单人单标最小投金额
                         *  真 投入金额=单人单标最大投金额
                         *  假 投入金额=用户金额-单人单标最小投金额
                         */
                        $balance = bccomp($user['param']['enableBalance'], $data['option']['maximum']) < 1 ? $user['param']['enableBalance'] : (bccomp($user['param']['enableBalance'] - $data['option']['maximum'], $data['option']['minimum']) > -1 ? $data['option']['maximum'] : $user['param']['enableBalance'] - $data['option']['minimum']);
                    } else {    //用户金额>标金额-单人单标最小投金额
                        /**
                         * 标金额>=2倍单人单标最小投金额
                         * 真
                         *  用户金额-单人单标最小投金额<=单人单标最大投金额
                         *  真 投入金额=用户金额-单人单标最小投金额
                         *  假 投入金额=单人单标最大投金额
                         * 假 投入金额=0;
                         */
                        $ref = $user['param']['enableBalance'] - $data['option']['minimum'];
                        $balance = bccomp($user['param']['enableBalance'], 2 * $data['option']['minimum']) > -1 ? (bccomp($ref, $data['option']['maximum']) < 1 ? $ref : $data['option']['maximum']) : 0;
                    }
                } else {    //用户金额>标金额
                    if (bccomp($user['param']['enableBalance'], $data['param']['cash'] + $data['option']['minimum']) == -1) {   //用户金额<标金额+单人单标最小投金额
                        /**
                         * 标金额-单人单标最小投金额<=单人单标最大投金额
                         * 真 投入金额=标金额-单人单标最小投金额
                         * 假 投入金额=单人单标最大投金额
                         */
                        $ref = $data['param']['cash'] - $data['option']['minimum'];
                        $balance = bccomp($ref, $data['option']['maximum']) < 1 ? $ref : $data['option']['maximum'];
                    } else {    //用户金额>=标金额+单人单标最小投金额
                        /**
                         * 标金额<=单人单标最大投金额
                         * 真 投入金额=标金额
                         * 假
                         *  单人单标最大投金额>标金额-单人单标最小投金额
                         *  真 投入金额=标金额-单人单标最小投金额
                         *  假 投入金额=单人单标最大投金额
                         */
                        $ref = $data['param']['cash'] - $data['option']['minimum'];
                        $balance = bccomp($data['param']['cash'], $data['option']['maximum']) < 1 ? $data['param']['cash'] : (bccomp($data['option']['maximum'], $ref) == 1 ? $ref : $data['option']['maximum']);
                    }
                }
            }

            if ($balance > 0) {
                /**
                 * 投入金额 > 0 用户参与了投标
                 * 投标人数+1
                 * 标待投金额-=投入金额
                 * 设置标用户投入标与金额
                 * 用户已投标的钱+=投入金额
                 * 用户可用于投标的钱-=投入金额
                 */
                if($data['param']['people']<$data['option']['people']) {
                    $data['param']['people'] += 1;
                }
                $data['param']['cash'] = bcsub($data['param']['cash'], $balance);
                $data['param']['set'][$user['user_id']][$data['param']['index']] = $balance;
                $user['param']['useBalance'] += $balance;
                $user['param']['enableBalance'] -= $balance;
            }

            return $balance;
        }

        /**
         * 转让时由于截位引起的用户本金和不等于提现本金值 修正第一个用户的本金
         * @param $data
         */
        public function modifyDataByChange(&$data) {
            $data['modify'] = array();

            if (bccomp($data['param']['cash'], 0) == 0 && isset($data['param']['set'])) {
                $totalPrincipal = 0;
                foreach($data['param']['set'] as $userId=>$acceptList) {
                    $totalPrincipal += array_sum($this->getArrayColumn($acceptList, 'principal'));
                }

                $principal = bcsub($data['require']['principal'], $totalPrincipal);
                if(bccomp($principal, 0) != 0) {
                    foreach($data['param']['set'] as $userId=>$acceptList) {
                        foreach($acceptList as $index=>$accept) {
                            if(bccomp($principal, 0)==0) {
                                break 2;
                            }

                            if(bccomp($accept['interest'], 0)==-1) {
                                $data['modify'][$userId][$index] = array(
                                    'source' => $accept,
                                    'target' => array(
                                        'principal' => $accept['principal'] += $principal,
                                        'interest' => $accept['interest'] -= $principal
                                    )
                                );
                                $data['param']['set'][$userId][$index] = $data['modify'][$userId][$index]['target'];
                                $principal = 0;
                                continue;
                            }

                            if(bccomp($principal, $accept['interest'])>-1){
                                $principal = bcsub($principal, $accept['interest']);
                                $data['modify'][$userId][$index] = array(
                                    'source' => $accept,
                                    'target' => array(
                                        'principal' => $accept['principal'] += $accept['interest'],
                                        'interest' =>  0
                                    )
                                );
                                $data['param']['set'][$userId][$index] = $data['modify'][$userId][$index]['target'];
                                continue;
                            }

                            if(bccomp($principal, $accept['interest'])==-1) {
                                $data['modify'][$userId][$index] = array(
                                    'source' => $accept,
                                    'target' => array(
                                        'principal' => $accept['principal'] += $principal,
                                        'interest' => $accept['interest'] -= $principal
                                    )
                                );
                                $data['param']['set'][$userId][$index] = $data['modify'][$userId][$index]['target'];
                                $principal = 0;
                                continue;
                            }
                        }
                    }
                }
            }
        }

        /**
         * 将债权转让承接的公允价值换算成承接本金与利息
         * @param $data
         */
        public function formatDataByChange(&$data) {
            foreach ($data['param']['set'] as $userId=>&$acceptList) {
                foreach ($acceptList as $index=>&$accept) {
                    $principal = $this->getFloorDigits($data['require']['principal'] / $data['data']['cash'] * $accept);
                    $accept = array(
                        'principal' => $principal,
                        'interest' => bcsub($accept, $principal)
                    );
                }
            }
        }

        /**
         * 获取债权到提现日的公允价值
         * @param $data
         * @param $get
         * @return array()
         */
        private function __setMerit(&$data, $get) {
            $principal = $get['data']['cash'];
            $refer = array(
                'principal' => 0,
                'interest' => 0
            );

            foreach ($data as $key => &$borrow) {
                $currentInterest = $this->get($borrow);
                $currentPrincipal = $borrow['leavings']['principal'];

                if ($currentInterest === false || ($get['data']['let'] == false && $principal <= 0)) {
                    unset($data[$key]);
                    continue;
                }

                $borrow['merit'] = array(
                    'principal' => $currentPrincipal,
                    'interest' => $currentInterest
                );
                $refer['principal'] += $currentPrincipal;
                $refer['interest'] += $currentInterest;

                $principal -= $currentPrincipal;
            }

            return $refer;
        }

        /**
         * 获取债权到提现日需要转让的公允价值
         * @param $data
         * @param $get
         * @return array
         */
        private function __getMerit($data, &$get) {
            $current = array();
            if ($get['data']['let'] == false && bccomp($get['data']['cash'], 0) < 1) {
                return $current;
            }

            $minimum = $this->getCeilDigits($data['merit']['principal'] / $data['data']['merit'] * $data['option']['minimum']);    //m'对应内含本金的m。
            /**
             * 到提现日标的所有金额公共价值[本金，利息]
             * 到提现日提现标本金的公共价值[本金，利息]
             */
            if ($data['data']['let'] == true || bccomp($get['data']['cash'], $data['merit']['principal']) > -1) {
                $get['data']['cash'] -= $data['merit']['principal'];
                $current = array('minimum' => $minimum) + $data['merit'];
            } else {
                /**
                 * 标的余下本金>=2倍最小投金额
                 * 真
                 *      持有人数<最大人数限制
                 *      真
                 *          提现金额<=最小投金额
                 *          真
                 *              转让金额=最小投金额
                 *          假
                 *              提现金额<=标的余下本金-最小投金额
                 *              真
                 *                  转让金额=提现金额
                 *              假
                 *                  转让金额=标的余下本金
                 *      假
                 *          转让金额=标的余下本金
                 * 假
                 *      转让金额=标的余下本金
                 */
                $get['data']['cash'] -= $currentCash = $data['merit']['principal'] >= 2 * $minimum ? (
                    $data['data']['people'] < $data['option']['people'] ? (
                        $get['data']['cash'] <= $minimum ? $minimum : (
                            $get['data']['cash'] <= $data['merit']['principal'] - $minimum ? $get['data']['cash'] : $data['merit']['principal']
                        )
                    ) : $data['merit']['principal']
                ) : $data['merit']['principal'];

                $current = array(
                    'minimum' => $minimum,
                    'principal' => $currentCash,
                    'interest' => $this->getFloorDigits($currentCash / $data['merit']['principal'] * $data['merit']['interest'])
                );
            }

            return $current;
        }
    }