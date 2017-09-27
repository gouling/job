<?php
    namespace Api\Model;

use Think\Model;
use Api\Information\CUKey;
use Api\Information\CLog;
use Api\Information\CData;
use Admin\Api\SarftApi;

class InformationModel extends Model
{
    private $__setting, $__rule;
    private $__data, $__api, $__redis;

    private $__without_wait='n1o2w3a4i5t';  //不等待回包
        private $__size=65;
    private $__delay=100;

    private $__status=array(
            1=>'认证成功',
            2=>'没有此用户',
            3=>'证书与用户不匹配',
            4=>'注册软件版本不匹配',
            5=>'服务暂停',
            6=>'未知错误'
        );

    public function __construct()
    {
        $this->__api=new SarftApi();
        $this->__data=new CData();
        $this->__setting=$this->__data->getSetting();
        $this->__rule=$this->__data->getRule();
        $this->__redis=$this->__data->getRedis();
    }

    public function getIdentity()
    {
        return $this->__data->getIdentity();
    }

    public static function getInterface()
    {
        return \Admin\Api\SarftApi::getInterface();
    }

    public function keyRestart()
    {
        exec('rm -rf '.RUNTIME_PATH.'/*');
        opcache_reset();

        $state=CUKey::call(CUKey::KEY_RESTART, false);
        if ($state) {
            CLog::debug('keyRestart->success');
        } else {
            CLog::debug('keyRestart->fail');
        }

        return $state;
    }

    public function checkUserPassword($data)
    {
        CLog::debug("checkUserPassword->username:{$data['username']}, password:{$data['password']}");

        $data['password']=strtoupper(md5($data['password']));
        if ($identity=$this->setLogin($data)) {
            if ($refer=$this->getLogin($identity)) {
                $refer['payload_data']['return_value']+=1;
                CLog::debug("checkUserPassword->username:{$data['username']}, password:{$data['password']}, return:{$this->__status[$refer['payload_data']['return_value']]}");
                return $refer['payload_data']['return_value'];
            }
        }

        return false;
    }

    public function getLoginText($status)
    {
        return $this->__status[$status];
    }

    public function setLogin($refer=array())
    {
        if ($address=self::getInterface()) {
            $identity="{$address['host']}:{$address['port']}";
            $data=array(
                    'username'=>$address['username'],
                    'password'=>strtoupper(md5($address['password'])),
                    'software_md5'=>strtoupper(C('INSTALL_INFO.MD5')),
                    'raw_data_count'=>$this->__api->getRawDataCount()
                );
            $data=array_merge($data, $refer);

            if ($data['username']=='' || $data['password']=='') {
                CLog::debug('read config username or password deny');
                return false;
            }

            $this->__redis->initialize();   //清除关于登陆指令的业务
                $this->__data->send($identity, 0x01, $data, $this->__setting['prefix']['login']['send']);
            CLog::debug("setLogin->{$identity}, username:{$data['username']}, password:{$data['password']}");
            $this->keyRestart();

            return $identity;
        } else {
            CLog::debug('read config deny');
            return false;
        }
    }

    public function getLogin($identity)
    {
        return $this->__data->accept($identity, $this->__setting['prefix']['login']['accept']);
    }

    public function setLoginStatus($data)
    {
        CLog::debug("setLoginStatus->{$data['key']}, return:{$data['data']['payload_data']['return_value']}");
        if ($data['data']['payload_data']['return_value']==0) {
            CLog::debug("setLoginStatus->{$data['key']}, uploadTicket");
            $this->__api->restartUploadTicket();
        }
    }

    public function getCinemaCode()
    {
        if ($data=CUKey::call(CUKey::KEY_CINEMA_CODE, false)) {
            return $data;
        }

        return false;
    }

    public function setNotice($data)
    {
        CLog::debug("setNotice->code:{$data['data']['payload_data']['code']}");
        $refer=array(
                'return_value'=>$this->__api->setNotice($data['data']['payload_data'])
            );
        $this->__data->send($data['key'].$this->__without_wait, 0x05, $refer);
        CLog::debug("setNotice->code:{$data['data']['payload_data']['code']}, return:{$refer['return_value']}");
    }

    public function getCinemaInfo($status=1)
    {
        $data=array(
                'cinema_code'=>$this->getCinemaCode(),
                'request'=>$status
            );

        if ($data['cinema_code']==false) {
            return false;
        }

        $identity=$this->__data->getIdentity();
        CLog::debug("getCinemaInfo->identity:{$identity}, cinema_code:{$data['cinema_code']}");
        $this->__data->send($identity, 0x06, $data);
        if ($refer=$this->__data->accept($identity)) {
            CLog::debug("getCinemaInfo->identity:{$identity}, cinema_code:{$data['cinema_code']}, return:{$refer['payload_data']['return_value']}");
            return $refer['payload_data'];
        }

        return false;
    }

    public function getMovie($start_date, $end_date)
    {
        $data=array(
                'start_date'=>$start_date,
                'end_date'=>$end_date
            );
        $status=array(
                'total'=>0,
                'count'=>0
            );

        $identity=$this->__data->getIdentity();
        $payload_id=0x08;
        CLog::debug("getMovie->identity:{$identity}, start_date:{$start_date}, end_date:{$end_date}");

        do {
            $this->__data->send($identity, $payload_id, $data);
                //数据接收完成, 数据查询完成
                if (($data=$this->__data->accept($identity)) && $data['payload_data']['return_value']==0) {
                    CLog::debug("getMovie->identity:{$identity}, start_date:{$start_date}, end_date:{$end_date}, count:{$data['payload_data']['count']}");
                    if ($data['payload_data']['count']>0) {
                        $status['count']+=$this->__api->downMovieExt($data['payload_data']['list']);
                    }

                    if ($payload_id=0x08) {
                        $payload_id=0x0A;   //使用0x08发起进程, 0x0A控制
                        $status['total']=$data['payload_data']['total'];
                    }

                    $data=array(
                        'ack'=>$data['payload_data']['remain']>0?0:2,   //大于0表示还有影片需要接收, 2表示终止接收
                        'delay'=>$this->__delay
                    );

                    if ($data['ack']==2) {
                        $identity.=$this->__without_wait;
                        $this->__data->send($identity, $payload_id, $data);
                        break;
                    }
                } else {
                    break;
                }
        } while ($data['ack']!=2);
        CLog::debug("getMovie->identity:{$identity}, start_date:{$start_date}, end_date:{$end_date}, total:{$status['total']}, count:{$status['count']}");

        return $status;
    }

    public function uploadTicket($identity, $data)
    {
        $this->__data->send($identity, 0x1C, $data);
        CLog::debug("uploadTicket->identity:{$identity}");
    }

    public function setUploadTicket($data)
    {
        $identity=$data['key'];
        $payload_data=$data['data']['payload_data'];
        //20161011 修改
        //if(substr($payload_data['delay'], -1)==1) {
        if (substr($payload_data['delay'], -1)>0) {
            $set=$this->__api->setUploadTicket($identity, $payload_data['ack']);
            CLog::debug("setUploadTicket->identity:{$identity}, delay:{$payload_data['delay']}, ack:{$payload_data['ack']}, api->setUploadTicket:{$set}");
        } else {
            $this->__call('getUploadTicket', array(0=>$data));
        }
    }

    public function timeoutTicket($identity, $data, $is_refund=true)
    {
        CLog::debug("timeoutTicket->identity:{$identity}, type:{$is_refund}");
        $this->__data->send($identity, $is_refund?0x11:0x12, $data);
        if ($refer=$this->__data->accept($identity)) {
            CLog::debug("timeoutTicket->identity:{$identity}, return:{$refer['payload_data']['return_value']}, code:{$refer['payload_data']['code']}");
            return $refer['payload_data'];
        }

        return false;
    }

    public function setTimeoutTicket($data)
    {
        CLog::debug("setTimeoutTicket->identity:{$data['key']}, code:{$data['data']['payload_data']['code']}, return:{$data['data']['payload_data']['return_value']}");

        $this->__api->setTimeOutTicket($data['data']['payload_data']['code'], $data['data']['payload_data']['return_value']);
        $this->__data->send($data['key'].$this->__without_wait, 0x15);
    }

    public function updateSoft($identity, $data)
    {
        CLog::debug("updateSoft->identity:{$identity}");
        $this->__data->send($identity, 0x23, $data);
        if ($refer=$this->__data->accept($identity)) {
            CLog::debug("updateSoft->identity:{$identity}, return:{$refer['payload_data']['return_value']}");
        }
    }

    public function queryVersion($data)
    {
        CLog::debug("queryVersion->identity:{$data['key']}");

        $install=C('INSTALL_INFO');
        $refer=array('RELEASE_DATE', 'TEST_DATE', 'REGISTER_DATE', 'UPDATE_DATE', 'INSTALL_DATE');
        foreach ($refer as $val) {   //标准时间转时间戳
                $install[$val]=strtotime($install[$val]);
        }
        $install=array_change_key_case($install, CASE_LOWER);

        $this->__data->send($data['key'].$this->__without_wait, 0x26, $install);
    }

    public function getPublicKey($cinema_code)
    {
        CLog::debug("getPublicKey->cinema_code:{$cinema_code}");

        $identity=$this->getIdentity();
        $this->__data->send($identity, 0x27, array('cinema_code'=>$cinema_code));
        if ($refer=$this->__data->accept($identity)) {
            CLog::debug("getPublicKey->cinema_code:{$cinema_code}, return:{$refer['payload_data']['return_value']}");
            if ($refer['payload_data']['return_value']==0) {
                return $refer['payload_data']['public_key'];
            }
        }

        return false;
    }

    public function uploadReport($report)
    {
        $identity=$this->getIdentity();
        CLog::debug("uploadReport->identity:{$identity}");
        $data=$this->__data->post($identity, $report);

        if (isset($data['TicketResponse'])) {
            $ticketResponse=strtolower($data['TicketResponse']);
            CLog::debug("uploadReport->identity:{$identity}, return:{$ticketResponse}");
        } else {
            CLog::debug("uploadReport->identity:{$identity}, return:fail");
        }

        return $data;
    }

    public function __call($name, $data)
    {
        if (!isset($this->__rule[$name])) {
            return;
        }

        if (substr($name, 0, 3)=='get') {
            if ($data[0]['data']['payload_data']['ack']!=0) {
                $this->__hDel($data);
                return;
            }
        }

        $refer=$this->__getQueryPageData($name, $data);
        $this->__data->send($data[0]['key'], $this->__rule[$name], $refer);
    }

    private function __getQueryPageData($name, $data)
    {
        $page=(int)$this->__redis->hGet($this->__setting['prefix']['query'], "page_{$data[0]['key']}", false);
        if ($page==0 && substr($name, 0, 5)=='query') {
            $queryData=$this->__api->$name($data[0]['data']['payload_data']);
            $this->__redis->hSet($this->__setting['prefix']['query'], "query_{$data[0]['key']}", serialize($queryData));
        } else {
            if ($queryData=$this->__redis->hGet($this->__setting['prefix']['query'], "query_{$data[0]['key']}", false)) {
                $queryData=unserialize($queryData);
            } else {
                $queryData=array();
            }
        }

        $total=count($queryData);
        $list=array_slice($queryData, $page++*$this->__size, $this->__size);
        $count=count($list);
        $remain=$total-$page*$this->__size;

        $refer=array(
                'return_value'=>$total>0?0:2,
                'total'=>$total,
                'remain'=>$remain>0?$remain:0,
                'count'=>$count
            );

        if (strcasecmp($name, 'querySeat')==0) {
            $refer['screen_code']=$data[0]['data']['payload_data']['screen_code'];
            $this->__redis->hSet($this->__setting['prefix']['query'], "seat_{$data[0]['key']}", $refer['screen_code']);
        }

        if (strcasecmp($name, 'getSeat')==0) {
            $refer['screen_code']=$this->__redis->hGet($this->__setting['prefix']['query'], "seat_{$data[0]['key']}", false);
        }

        $refer['list']=$count>0?$list:array();

        $this->__redis->hSet($this->__setting['prefix']['query'], "page_{$data[0]['key']}", $page);
        if ($remain<1) {
            $this->__hDel($data);
        }

        CLog::debug("{$name}->identity:{$data[0]['key']}, total:{$refer['total']}, remain:{$refer['remain']}, count:{$refer['count']}");
        return $refer;
    }

    private function __hDel($data)
    {
        $this->__redis->hDel($this->__setting['prefix']['query'], "query_{$data[0]['key']}");
        $this->__redis->hDel($this->__setting['prefix']['query'], "page_{$data[0]['key']}");
        $this->__redis->hDel($this->__setting['prefix']['query'], "seat_{$data[0]['key']}");
    }
}
