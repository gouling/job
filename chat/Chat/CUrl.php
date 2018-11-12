<?php
/**
 * 企业微信端api地址定义、获取、参数映射
 * https://work.weixin.qq.com/api/doc
 */
namespace Chat;

class CUrl {
    private $__key_data = [];
    
    /**
     * 参数映射 本地参数->微信参数
     */
    public function __construct() {
        $this->__key_data = [
            'user_id' => 'userid',
        ];
    }
    
    /**
     * 获取组织部门信息
     * @param array $data(access_token)
     * @return array
     */
    public function getDepartmentList(array $data) {
        return $this->__createUrlData('https://qyapi.weixin.qq.com/cgi-bin/department/list', $data);
    }
    
    /**
     * 根据部门编号获取部门成员详情
     * @param array $data(access_token,department_id)
     * @return array
     */
    public function getUserListByDepartmentId(array $data) {
        return $this->__createUrlData('https://qyapi.weixin.qq.com/cgi-bin/user/list', $data);
    }
    
    /**
     * 根据用户编号获取用户详情
     * @param array $data(access_token,user_id)
     * @return array
     */
    public function getUserInfoById(array $data) {
        return $this->__createUrlData('https://qyapi.weixin.qq.com/cgi-bin/user/get', $data);
    }
    
    /**
     * 创建Url数据
     * @param string $address
     * @param array $data
     * @return array
     */
    private function __createUrlData(string $address, array $data) {
        $res = [];
        foreach ($data as $k=>$v) {
            $v = $this->__getFormatKeyData($v);
            $res[$k] = [
                'address' => $address,
                'data' => $v,
            ];
        }
    
        return $res;
    }
    
    /**
     * 参数映射 本地参数转微信参数
     * @param array $data
     * @return array
     */
    private function __getFormatKeyData(array $data) {
        $key_data = array_intersect_key($this->__key_data, $data);
        foreach ($key_data as $local_key=>$chat_key) {
            if(isset($data[$local_key])) {
                $data[$chat_key] = $data[$local_key];
                unset($data[$local_key]);
            }
        }
        
        return $data;
    }
}