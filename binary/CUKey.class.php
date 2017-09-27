<?php
    namespace Api\Information;

    class CUKey {
        const KEY_MD5='CMd5_dataEx';
        const KEY_SHA='CSha1_dataEx';
        const KEY_CINEMA_CODE='CGet_cinema_code';
        const KEY_SIGN='CSign_dataEx';
        const KEY_VERIFY='CVerify_dataEx';
        const KEY_ENCRYPT='CEncryptEx';
        const KEY_DECRYPT='CDecrypt';
        const KEY_DECRYPT_PUBLIC='CDecryptByPublic';
        const KEY_CRC='Crc';
        const KEY_RESTART='Restart';

        const KEY_REBOOT='Reboot';
        const KEY_SHUTDOWN='Shutdown';

        static function call($pointer, $data) {
            $redis=new CRedis();
            $identity=uniqid();

            $refer=self::$pointer($data);
            if($refer=$redis->UKey($identity, $refer)) {
                $refer=self::decrypt($pointer, $refer);
            } else {
                $pointer=strtolower($pointer);
                CLog::debug("{$pointer} {$identity} deny");
            }

            return $refer;
        }

        static function decrypt($pointer, $bin) {
            switch($pointer) {
                case self::KEY_CRC:
                    $data=unpack('v', $bin)[1];
                    break;
                default:
                    $data=$bin;
            }

            return $data;
        }

        static function Restart() {
            $call=pack('a*', self::KEY_RESTART);
            $length=pack('i', strlen($call));

            return $length.$call;
        }

        static function Reboot() {
            $call=pack('a*', self::KEY_REBOOT);
            $length=pack('i', strlen($call));

            return $length.$call;
        }

        static function Shutdown() {
            $call=pack('a*', self::KEY_SHUTDOWN);
            $length=pack('i', strlen($call));

            return $length.$call;
        }

        static function CMd5_dataEx($data) {
            return $data;
        }

        static function CSha1_dataEx($data) {
            return $data;
        }

        static function CGet_cinema_code() {
            $call=pack('a*', self::KEY_CINEMA_CODE);
            $length=pack('i', strlen($call));

            return $length.$call;
        }

        static function CSign_dataEx($data) {
            $call=pack('a*', self::KEY_SIGN);
            $binary=pack('a*', $data);

            $length=array(
                'call'=>pack('i', strlen($call)),
                'binary'=>pack('i', strlen($binary))
            );

            return $length['call'].$call.$length['binary'].$binary;
        }

        static function CVerify_dataEx($data) {
            $data=array(
                'call'=>pack('a*', self::KEY_VERIFY),
                'src'=>pack('a*', $data['src']),
                'sign'=>$data['sign'],
                'key'=>$data['key']
            );

            foreach($data as $key=>$binary) {
                $length=pack('i', strlen($binary));
                $data[$key]=$length.$binary;
            }

            return implode('', $data);
        }

        static function CEncryptEx($data) {
            return $data;
        }

        static function CDecrypt($data) {
            return $data;
        }

        static function CDecryptByPublic($data) {
            $call=pack('a*', self::KEY_DECRYPT_PUBLIC);
            $binary=pack('a*', $data);

            $length=array(
                'call'=>pack('i', strlen($call)),
                'binary'=>pack('i', strlen($binary))
            );

            return $length['call'].$call.$length['binary'].$binary;
        }

        static function Crc($data) {
            $call=pack('a*', self::KEY_CRC);
            $binary=pack('a*', $data);

            $length=array(
                'call'=>pack('i', strlen($call)),
                'binary'=>pack('i', strlen($binary))
            );

            return $length['call'].$call.$length['binary'].$binary;
        }
    }