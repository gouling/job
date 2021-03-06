 <?php
    class CHealth {
        private $__timeout;
        public function __construct($timeout = 5) {
            $this->__timeout = $timeout;
        }
        public function isRunning($host){
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            socket_set_option(
                $socket,
                SOL_SOCKET,
                SO_SNDTIMEO,
                array(
                    'sec'=>$this->__timeout,
                    'usec'=>0 
                )
            );
            list($ip, $port) = explode(':', $host);
            $health = @socket_connect($socket, $ip, $port);
            socket_close($socket);

            return $health;
        }
    }
