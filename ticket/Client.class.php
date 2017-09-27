<?php

    namespace Api\HelpTicket;

    class Client {
        private $_data = false;
        private $_client = false;

        private $_ip = false;
        private $_port = false;

        public function __construct(&$client, $ip, $port) {
            $this->_data = new \Api\HelpTicket\Data($ip);
            $this->_client = $client;

            $this->_ip = $ip;
            $this->_port = $port;

            $this->run();
        }

        public function run() {
            error_reporting(0);

            while (true) {
                if ($binary = socket_read($this->_client, 8192, PHP_BINARY_READ)) {
                    if ($data = $this->_data->readData($binary)) {
                        if (socket_write($this->_client, $data, strlen($data))) {
                            continue;
                        }
                    }
                }

                $this->__destroy();
                break;
            }
        }

        private function __destroy() {
            socket_close($this->_client);
            $this->_data->__destruct();
        }
    }
