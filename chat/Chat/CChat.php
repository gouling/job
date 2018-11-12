<?php
/**
 * 检查输入数据，向企业微信端发起数据请求，校验格式化输出数据
 */
namespace Chat;
class CChat {
    private $__get, $__url;
    
    /**
     * 初始化请求对象
     */
    public function __construct() {
        $this->__get = new \Http\CGet();
        $this->__url = new \Chat\CUrl();
    }
    
    /**
     * 根据用户编号获取用户详情
     * @param array $data(access_token,user_id)
     * @return array
     * @throws \Exception
     */
    public function getUserInfoById(array $data) {
        $chat = $this->__get->sendRequest($this->__url->getUserInfoById($data));
        $user = $this->__getVerifyRequest($chat);
    
        foreach ($user as &$v) {
            if ($v['errcode'] != 0) {
                throw new \Exception($v['errmsg'], $v['errcode']);
            }
        }
    
        return $user;
    }
    
    /**
     * 获取组织部门信息
     * @param array $data(access_token)
     * @return array
     * @throws \Exception
     */
    public function getDepartmentList($data) {
        $chat = $this->__get->sendRequest($this->__url->getDepartmentList($data));
        $department = $this->__getVerifyRequest($chat);
        
        foreach ($department as &$v) {
            if ($v['errcode'] == 0) {
                $v = $v['department'];
            } else {
                throw new \Exception($v['errmsg'], $v['errcode']);
            }
        }
        
        return $department;
    }
    
    /**
     * 根据部门编号获取部门成员详情
     * @param array $data(access_token,department_id)
     * @return array
     * @throws \Exception
     */
    public function getUserListByDepartmentId($data) {
        $chat = $this->__get->sendRequest($this->__url->getUserListByDepartmentId($data));
        $user = $this->__getVerifyRequest($chat);
        
        foreach ($user as &$v) {
            if ($v['errcode'] == 0) {
                $v = $v['userlist'];
            } else {
                throw new \Exception($v['errmsg'], $v['errcode']);
            }
        }
        
        return $user;
    }
    
    /**
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function __getVerifyRequest(array $data) {
        foreach ($data as &$v) {
            if($v['res']['code'] == 200) {
                $v = json_decode($v['res']['data'], true);
            } else {
                throw new \Exception($v['res']['err'], $v['res']['code']);
            }
        }
        
        return $data;
    }
}
