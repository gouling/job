<?php

    class CReportServer {
        private $__cache = null, $__cache_identity = null;
        private $__file_data = null, $__file_download = null, $__file_identity = null, $__zip = null;
        private $__name = array(), $__data = array();

        public function create() {
            $this->__createReport();
        }

        public function load($data, $name) {
            if (isset($this->__name[$name])) {
                $this->__name[$name]++;
            } else {
                $this->__name[$name] = 1;
            }

            file_put_contents("{$this->__cache_identity}.{$name}.{$this->__name[$name]}", json_encode($data));
        }

        public function download($name = 'data.zip') {
            if (!file_exists($this->__cache_identity)) {
                throw new Exception('Failed to download data, please try again');
            }

            header('Cache-Control: no-cache');
            header('Pragma: no-cache');
            header('Content-type: application/octet-stream');
            header('Accept-Ranges: bytes');
            header('Accept-Length: ' . filesize($this->__cache_identity));
            header("Content-Disposition:attachment;filename='{$name}'");

            $this->__file_download = fopen($this->__cache_identity, 'r');
            while (!feof($this->__file_download)) {
                echo fread($this->__file_download, 1024);
            }

            fclose($this->__file_download);
        }

        private function __createReport() {
            if ($this->__zip->open($this->__cache_identity, ZIPARCHIVE::CREATE) == false) {
                throw new Exception('Failed to create report');
            }

            foreach ($this->__name as $key => $val) {
                for ($page = 1; $page <= $val; $page++) {
                    $this->__zip->addFile("{$this->__cache_identity}.{$key}.{$page}", "{$key}.{$page}.json");
                }
            }
            $this->__zip->close();
        }

        //获取传输的数据
        public function getData() {
            return $this->__data;
        }

        public function __construct($cache) {
            ini_set('default_socket_timeout', 0);
            //ini_set('display_errors', 0);

            $this->__zip = new ZipArchive();
            $this->__cache = $cache;
            $this->__data = $_POST;

            list($time, $date) = explode(' ', microtime());
            $this->__file_identity = $date . substr($time, 1);
            $this->__cache_identity = $this->__cache . $this->__file_identity . sprintf('.%u', rand(1000, 9999));
        }

        public function __destruct() {
            if (stristr(php_uname(), 'Linux') != false) {
                exec("rm -rf {$this->__cache_identity}*");
            } else {
                exec("cmd /c del /f /s /q {$this->__cache_identity}*");
            }
        }
    }

?>
