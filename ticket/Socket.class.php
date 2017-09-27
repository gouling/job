<?php
    namespace Api\HelpTicket;

    class Socket {
        static $HANDLE = false;
        static $STATE = false;
        static $THREAD = false;
        static $REDIS = false;

        public function __construct() {
            self::$THREAD = array();
            self::$REDIS = new \Api\Extendtion\Redis();

            //循环创建服务直到成功
            while (!self::$STATE) {
                sleep(5);

                if ($this->__initialize()) {
                    pcntl_signal(SIGCHLD, SIG_IGN);
                    $this->__listen();
                } else {
                }
            }
        }

        private function __initialize() {
            if (self::$HANDLE) {
                socket_close(self::$HANDLE);
            }
            self::$HANDLE = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            self::$STATE = socket_bind(self::$HANDLE, C('TICKET_INTERFACE.HOST'), C('TICKET_INTERFACE.PORT'));
            self::$STATE = socket_listen(self::$HANDLE);

            return self::$STATE;
        }

        private function __listen() {
            set_time_limit(0);

            while (true) {
                if (($client = socket_accept(self::$HANDLE)) && socket_getpeername($client, $ip, $port)) {
                    if (pcntl_fork() == 0) {
                        self::$THREAD[$ip] = new \Api\HelpTicket\Client($client, $ip, $port);
                        break;
                    }
                }
            }
        }
    }

?>
