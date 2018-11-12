<?php

namespace Http;

abstract class IHttp
{
    protected $__timeout;
    
    const REQUEST_GET = 'get';
    const REQUEST_POST = 'post';
    
    public function __construct($timeout)
    {
        $this->__timeout = $timeout;
    }
    
    /**
     * @param array $data (k=>address,data)
     */
    abstract public function sendRequest($data);
}
