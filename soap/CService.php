<?php
    /* PHP 5.6+ php.ini -> always_populate_raw_post_data = -1 */

    include('CData.php');
    new CSynchronizeServer();

    class CSynchronizeServer {
        private $__server = null;
        private $__uri = 'CSynchronizeServer';

        public function __construct() {
            if (is_bool($this->__auth())) {
                $this->__server = new \SoapServer(null, array(
                    'uri' => $this->__uri,
                    'version' => SOAP_1_2
                ));
                $this->__server->setClass('CSynchronizeData');
                $this->__action();
            }
        }

        private function __auth() {
            if (!isset($_SERVER['REQUEST_METHOD']) || strcasecmp($_SERVER['REQUEST_METHOD'], 'post') != 0 || !isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
                header('WWW-Authenticate: Basic realm=\'You must enter a login ID and password\'');
                header('HTTP/1.0 401 Unauthorized');

                return 'You must enter a valid login ID and password to access this resource.';
            }

            if ($_SERVER['PHP_AUTH_USER'] == $_SERVER['PHP_AUTH_PW']) {
                return true;
            } else {
                header('WWW-Authenticate: Basic realm=\'login ID and password\'');
                header('HTTP/1.0 401 Unauthorized');

                return 'You enter login ID and password deny.';
            }
        }

        private function __action() {
            try {
                $this->__server->handle();
            } catch (SoapFault $err) {
                print $err->faultstring;
            }
        }
    }

?>