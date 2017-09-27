<?php

    namespace Api\Information;

    class CLog {
        const INTERFACE_INFORMATION = 'information';
        const INTERFACE_NETSALE = 'netsale';

        static function debug($message, $interface = 'information') {
            $datetime = date('Y-m-d H:i:s');

            file_put_contents("/tmp/{$interface}.log", "{$datetime} {$message}" . PHP_EOL, FILE_APPEND);
            //self::systemLog($message);
        }

        static function binary($identity, $binary) {
            file_put_contents("/tmp/key.{$identity}", $binary);
        }

        static function systemLog($message) {
            $data = array(
                'user_id' => 2,
                'record_id' => 0,
                'content' => $message,
                'time' => time(),
                'ip' => '127.0.0.1',
                'model' => '接口'
            );
            M('ActionLog')->add($data);
        }
    }