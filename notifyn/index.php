<?php
    //createPhar('notifyn.phar');
    //createCode('notifyn.phar', 'library');

    function createPhar($name) {
        if(file_exists($name)) {
            unlink($name);
        }

        $phar = new Phar($name, 0, $name);
        $phar->buildFromDirectory(dirname(__FILE__) . '/library');
        $phar->setDefaultStub('index.php', 'index.php');
        $phar->compressFiles(Phar::GZ);
    }

    function createCode($name, $dir) {
        if(file_exists($name)) {
            $phar = new Phar($name);
            $phar->convertToData(Phar::ZIP);
        }
    }
