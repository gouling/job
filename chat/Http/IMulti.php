<?php

namespace Http;

abstract class IMulti extends IHttp
{
    protected $__curl;
    protected $__handle;
    protected $__opt;
        
    final public function __construct($timeout = 5)
    {
        parent::__construct($timeout);

        $this->__handle = curl_multi_init();
        $this->__opt = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_TIMEOUT => $timeout,
        );
    }
    
    /**
     * 重设CURL地址
     * @param $address
     * @param $data
     * @return mixed
     */
    abstract protected function __formatAddress($address, $data);
    
    /**
     * 格式化数据
     * @param $k
     * @param $data
     * @return mixed
     */
    abstract protected function __formatData($k, $data);


    final public function sendRequest($data = array())
    {
        $this->__setOpt($data);
        $this->__doSend();

        return $this->__getData($data);
    }

    private function __setOpt($data)
    {
        foreach ($data as $k => $v) {
            $address = $this->__formatAddress($v['address'], $v['data']);
            $this->__curl[$k] = curl_init($address);
            foreach ($this->__opt as $opt => $value) {
                curl_setopt($this->__curl[$k], $opt, $value);
            }
            $this->__formatData($k, $v['data']);

            curl_multi_add_handle($this->__handle, $this->__curl[$k]);
        }
    }

    private function __doSend()
    {
        do {
            curl_multi_exec($this->__handle, $active);
        } while ($active);
    }

    private function __getData($data)
    {
        foreach ($data as $k => $v) {
            $data[$k]['res'] = array(
                'code' => curl_getinfo($this->__curl[$k], CURLINFO_HTTP_CODE),
                'err' => curl_error($this->__curl[$k]),
                'data' => curl_multi_getcontent($this->__curl[$k])
            );

            curl_multi_remove_handle($this->__handle, $this->__curl[$k]);
            curl_close($this->__curl[$k]);
        }

        return $data;
    }
}
