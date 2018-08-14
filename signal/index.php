#!/usr/bin/env /usr/share/php-7.2.7/bin/php
<?php
    include 'CTask.php';
    include 'CSignal.php';
    include 'CLog.php';
    
    class CData {
        public function get():array {
            return [
                'time' => time()
            ];
        }
        
        public function set($data = []):bool {
            return true;
        }
    }
    
    new CTask(new CData());
