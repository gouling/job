<?php
    /* 调用服务器函数 */

    $i = 0;
    while ($i++ < 3) {
        var_dump(CSynchronizeClient::callAction('getReport', array(
            'id' => $i,
            'data' => 'hello word'
        )));
    }

    class CSynchronizeClient {
        private static $__service = null;
        private $__client = null, $__config = array();

        public function __construct() {
            $this->__config = array(
                'location' => 'http://localhost/CService.php',
                'login' => 'root',
                'password' => 'root',
                'timeout' => 1
            );

            ini_set('default_socket_timeout', $this->__config['timeout']);
            $this->__client = new \SoapClient(null, array(
                'location' => $this->__config['location'],
                'uri' => 'CSynchronizeServer',
                'version' => SOAP_1_2,
                'login' => $this->__config['login'],
                'password' => $this->__config['password']
            ));
        }

        public function callRemote($action, $data = array()) {
            if (!is_array($data)) {
                return 'data must is array';
            }

            try {
                return $this->__client->$action($data);
            } catch (SoapFault $err) {
                return $err->faultstring;
            }
        }

        public static function callAction($action, $data) {
            if (is_null(self::$__service)) {
                self::$__service = new CSynchronizeClient();
            }

            return self::$__service->callRemote($action, $data);
        }
    }

?>
