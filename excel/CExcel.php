<?php

    class CExcel {
        private $__settings = null;
        private $__file_identity = null, $__cache_identity = null;
        private $__zip = null;
        private $__file_data = null, $__file_download = null;

        public function create() {
            $this->__createSharedStrings();
            $this->__createSheetData();
            $this->__createExcel();

            return $this;
        }

        public function load($data) {
            $this->__settings['rowTotal'] += count($data);
            $this->__settings['pageTotal'] += 1;
            file_put_contents($this->__cache_identity . '.' . $this->__settings['pageTotal']. '.json', json_encode($data));
        }

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

        private function __createExcel() {
            $this->__zip->addFile($this->__cache_identity . '.sharedStrings', 'xl/sharedStrings.xml');
            $this->__zip->addFile($this->__cache_identity . '.sheet1', 'xl/worksheets/sheet1.xml');
            $this->__zip->close();
        }

        private function __createCacheFile() {
            list($time, $date) = explode(' ', microtime());
            $this->__file_identity = $date . substr($time, 1) . sprintf('.%u', rand(1000, 9999));
            $this->__cache_identity = $this->__settings['cache'] . DIRECTORY_SEPARATOR . $this->__file_identity;
            $this->__zip = new ZipArchive();
            if (copy($this->__settings['template'], $this->__cache_identity) == false) {
                throw new \Exception('未能找到设定模板文件。', 404);
            }
            if ($this->__zip->open($this->__cache_identity) == false) {
                throw new \Exception('未能打开设定模板文件。', 412);
            }
        }

        //sheet xl sharedStrings.xml
        private function __createSharedStrings() {
            $this->__file_data = fopen($this->__cache_identity . '.sharedStrings', 'w');
            fwrite($this->__file_data,
                '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL .
                '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . PHP_EOL
            );
            //头数据
            foreach ($this->__settings['column'] as $val) {
                fwrite($this->__file_data,
                    '	<si>' . PHP_EOL .
                    '		<t>' . $val . '</t>' . PHP_EOL .
                    '	</si>' . PHP_EOL
                );
            }
            //列数据
            for ($page = 1; $page <= $this->__settings['pageTotal']; $page++) {
                $data = file_get_contents($this->__cache_identity . '.' . $page . '.json');
                $data = json_decode($data, true);
                foreach ($data as $key => $row) {
                    foreach ($row as $field => $val) {
                        fwrite($this->__file_data,
                            '	<si>' . PHP_EOL .
                            '		<t>' . $val . '</t>' . PHP_EOL .
                            '	</si>' . PHP_EOL
                        );
                    }
                }
            }
            fwrite($this->__file_data, '</sst>');
            fclose($this->__file_data);
        }

        //sheet xl worksheets sheet1.xml
        private function __createSheetData() {
            $this->__file_data = fopen($this->__cache_identity . '.sheet1', 'w');
            fwrite($this->__file_data,
                '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL .
                '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:x14="http://schemas.microsoft.com/office/spreadsheetml/2009/9/main" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006">' . PHP_EOL .
                '	<sheetPr/>' . PHP_EOL .
                '	<dimension ref="A1:' . $this->__getColumnName(64 + $this->__settings['columnTotal']) . ($this->__settings['dataRow'] + $this->__settings['rowTotal']) . '" />' . PHP_EOL .
                '	<sheetFormatPr defaultColWidth="' . $this->__settings['columnWidth'] . '" defaultRowHeight="' . $this->__settings['rowHeight'] . '" />' . PHP_EOL
            );
            //列名
            fwrite($this->__file_data, '	<cols>' . PHP_EOL);
            foreach ($this->__settings['column'] as $key => $val) {
                $key += 1;
                fwrite($this->__file_data, '		<col min="' . $key . '" max="' . $key . '" width="' . $this->__settings['columnWidth'] . '" style="1" customWidth="1"/>' . PHP_EOL);
            }
            fwrite($this->__file_data, '		<col min="' . ($this->__settings['columnTotal'] + 1) . '" max="16384" width="' . $this->__settings['columnWidth'] . '" style="1"/>' . PHP_EOL);
            fwrite($this->__file_data, '	</cols>' . PHP_EOL);
            //数据
            fwrite($this->__file_data, '    <sheetData>' . PHP_EOL);
            for ($row = $this->__settings['dataRow'], $index = 0; $row < $this->__settings['rowTotal'] + $this->__settings['dataRow'] + 1; $row++) {
                fwrite($this->__file_data, '		<row r="' . $row . '" customHeight="1" spans="1:' . $this->__settings['columnTotal'] . '">' . PHP_EOL);
                foreach ($this->__settings['column'] as $key => $val) {
                    $columnName = $this->__getColumnName(65 + $key) . $row;
                    fwrite($this->__file_data,
                        '			<c r="' . $columnName . '" s="2" t="s">' . PHP_EOL .
                        '				<v>' . $index++ . '</v>' . PHP_EOL .
                        '			</c>' . PHP_EOL
                    );
                }
                fwrite($this->__file_data, '		</row>' . PHP_EOL);
            }
            fwrite($this->__file_data, '	</sheetData>' . PHP_EOL);
            fwrite($this->__file_data, '</worksheet>');
            fclose($this->__file_data);
        }

        public function __construct($settings = array()) {
            $settings['columnTotal'] = count($settings['column']);  //列数
            $settings['rowTotal'] = $settings['pageTotal'] = 0; //行数，记录数

            $this->__settings = array_merge(array(
                'dataRow' => 1, //开始写数据行
                'rowHeight' => 20,  //默认行高
                'columnWidth' => 20,   //默认列宽
            ), $settings);
            $this->__createCacheFile();
        }

        private function __getColumnName($column) {
            if ($column < 65 || $column > 766) {
                throw new \Exception('未能支持大于766列数据导出。', 410);
            }
            $multiple = intval(($column - 65) / 26);
            $tens = $multiple + 64;
            $unit = $column - $multiple * 26;

            return ($tens > 64 ? chr($tens) : '') . chr($unit);
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

?>
