<?php

    namespace Api\Controller;

    use Api\Information\CUKey;

    class ServiceController extends Controller {
        public function Information() {
            $data = new \Api\Information\CData();
            D('Api/Information')->setLogin();

            while (true) {
                if ($list = $data->getBinaryList()) {
                    foreach ($list as $key => $binary) {
                        $data->read($binary, $key);
                    }
                }

                usleep(10);
            }
        }

        public function HelpTicket() {
            new \Api\HelpTicket\Socket();
        }
    }
