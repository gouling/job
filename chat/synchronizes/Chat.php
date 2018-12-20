<?php
    namespace Synchronizes;
    
    class Chat {
        const API_GET_DEPART_LIST = 'https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token=:token';
        const API_GET_USER_LIST = 'https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token=:token&department_id=:id';
        const API_GET_USER_ID = 'https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=:token&userid=:id';
        
        protected $opts = [];
        
        public final function __construct(array $opts) {
            $this->opts = $opts;
        }
        
        public function getAuthInfo() {
            return [
                'id' => $this->opts['id'],
                'name' => 'MichaelYoungBusiness',
                'departs' => '',
                'users' => ''
            ];
        }

        public function getDeparts():array {
            $address = str_ireplace(':token', $this->opts['token'], self::API_GET_DEPART_LIST);
 
            if($data = $this->getApi($address)) {
                if($data['errcode'] == 0) {
                    // 以层级编号为key，层级信息为val的数组，为检查顶层不存在时创建顶层做准备
                    return array_combine(array_column($data['department'], 'id'), $data['department']);
                } else {
                    throw new \Exception($data['errmsg'], $data['errcode']);
                }
            }
            
            throw new \Exception("请求企业微信端无任何数据响应({$address})。", 500);
        }
        
        public function getUsers($id):array {
            $address = str_ireplace([':token', ':id'], [$this->opts['token'], $id], self::API_GET_USER_LIST);
            
            if($data = $this->getApi($address)) {
                if($data['errcode'] == 0) {
                    return $data['userlist'];
                } else {
                    throw new \Exception($data['errmsg'], $data['errcode']);
                }
            }
            
            throw new \Exception("请求企业微信端无任何数据响应({$address})。", 500);
        }
        
        public function getUser($id):array {
            $address = str_ireplace([':token', ':id'], [$this->opts['token'], $id], self::API_GET_USER_ID);
            
            if($data = $this->getApi($address)) {
                if($data['errcode'] == 0) {
                    unset($data['errcode'], $data['errmsg']);
                    return $data;
                } else {
                    throw new \Exception($data['errmsg'], $data['errcode']);
                }
            }
            
            throw new \Exception("请求企业微信端无任何数据响应({$address})。", 500);
        }
        
        protected function getApi($address) {
            $http = curl_init();
            $opts = [
                CURLOPT_URL => $address,
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT => 5,
            ];

            curl_setopt_array($http, $opts);

            try {
                if($data = curl_exec($http)) {
                    $data = json_decode($data, true);
                }
            } finally {
                curl_close($http);
            }

            return $data;
        }
        
        protected function postApi($address, $data) {
            $http = curl_init();
            $opts = [
                CURLOPT_URL => $address,
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_POSTFIELDS => http_build_query($data)
            ];

            curl_setopt_array($http, $opts);

            try {
                if($data = curl_exec($http)) {
                    $data = json_decode($data, true);
                }
            } finally {
                curl_close($http);
            }

            return $data;
        }
    }
