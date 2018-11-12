<?php

namespace Http;

class CPost extends IMulti
{
    protected function __formatAddress($address, $data)
    {
        return $address;
    }
    
    /**
     * 设定 CURL 为 POST 方式提交
     * POST 方式需要将 DATA 附加到 CURL
     */
    protected function __formatData($k, $data)
    {
        curl_setopt($this->__curl[$k], CURLOPT_POST, 1);
        curl_setopt($this->__curl[$k], CURLOPT_POSTFIELDS, http_build_query($data));
    }
}
