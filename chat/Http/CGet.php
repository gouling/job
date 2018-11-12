<?php

namespace Http;

class CGet extends IMulti
{
    /**
     * GET方式 DATA 附加到 URL
     */
    protected function __formatAddress($address, $data)
    {
        if (count($data) > 0) {
            $params = implode('&', $this->__convertArrToStr($data));
            $symbol = stripos($address, '?') === false ? '?' : '&';
            
            return $address . $symbol . $params;
        } else {
            return $address;
        }
    }
    
    /**
     * 设定 CURL 为 GET 方式提交
     * GET方式 DATA 已附加到 URL 无此操作
     */
    protected function __formatData($k, $data)
    {
        curl_setopt($this->__curl[$k], CURLOPT_POST, 0);
    }
    
    /**
     * 数据参数转换为URL形式 多维数组转换为一唯 key=val 值的形式
     * @param $src
     * @param array $tar
     * @param bool $pk
     * @return array
     */
    private function __convertArrToStr($src, &$tar = array(), $pk = false)
    {
        foreach ($src as $k => $v) {
            if (is_array($v)) {
                $this->__convertArrToStr($v, $tar, $pk == false ? $k : $pk . '[' . $k . ']');
            } else {
                $tar[] = $pk == false ? "{$k}={$v}" : "{$pk}[{$k}]={$v}";
            }
        }
        
        return $tar;
    }
}
