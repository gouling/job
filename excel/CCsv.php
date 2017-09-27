<?php

    /**
     * Date: 9/9/16
     * Time: 9:39 AM
     */
    class CCsv {
        private $__settings = null;
        private $__file_identity = null, $__cache_identity = null, $__file_download = null;
        private $__file_data = null;

        public function download($filename = 'sheet.csv') {
            if (!file_exists($this->__cache_identity)) {
                throw new \Exception('未能找到缓存文件，无法启动下载。', 404);
            }
            header('Cache-Control: no-cache');
            header('Pragma: no-cache');
            header('Content-type: application/octet-stream');
            header('Accept-Ranges: bytes');
            header('Accept-Length: ' . filesize($this->__cache_identity));
            header("Content-Disposition:attachment;filename={$filename}");
            $this->__file_download = fopen($this->__cache_identity, 'r');
            while (!feof($this->__file_download)) {
                echo fread($this->__file_download, 1024);
            }
            fclose($this->__file_download);
        }

        public function load($data) {
            $this->__settings['rowTotal'] += count($data);
            $this->__settings['pageTotal'] += 1;
            file_put_contents($this->__cache_identity . '.' . $this->__settings['pageTotal']. '.json', json_encode($data));
        }

        public function create() {
            $this->__writeTitle();
            $this->__writeData();

            return $this;
        }

        private function __writeData() {
            $this->__file_data = fopen($this->__cache_identity, 'a');
            for ($page = 1; $page <= $this->__settings['pageTotal']; $page++) {
                $data = file_get_contents($this->__cache_identity . '.' . $page . '.json');
                $data = json_decode($data, true);
                foreach ($data as $key => $row) {
                    fwrite($this->__file_data, implode(',', $row).PHP_EOL);
                }
            }
            fclose($this->__file_data);
        }

        private function __writeTitle() {
            $this->__file_data = fopen($this->__cache_identity, 'a');
            fwrite($this->__file_data, implode(',', $this->__settings['column']).PHP_EOL);
            fclose($this->__file_data);
        }

        private function __createCacheFile() {
            list($time, $date) = explode(' ', microtime());
            $this->__file_identity = $date . substr($time, 1) . sprintf('.%u', rand(1000, 9999));
            $this->__cache_identity = $this->__settings['cache'] . DIRECTORY_SEPARATOR . $this->__file_identity;
        }

        public function __construct($settings = array()) {
            $settings['columnTotal'] = count($settings['column']);  //列数
            $settings['rowTotal'] = $settings['pageTotal'] = 0; //行数，记录数

            $this->__settings = $settings;
            $this->__createCacheFile();
        }

        public function __destruct() {
            $directory = $this->__settings['cache'];
            $cache = scandir($directory);
            $identity = $this->__file_identity;
            
            array_map(function ($file) use ($directory, $identity) {
                if (stripos($file, $identity) !== false) {
                    unlink($directory . DIRECTORY_SEPARATOR . $file);
                }
            }, $cache);
        }
    }
