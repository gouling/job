<?php
    /**
     * php 5.3.29 +
     * phar.readonly = Off
     * {"action":"request","data":{"id":"gouling","tel":["17612800917","13458585242"]},"retry":"0"}
     * {"action":"system","data":"stop/recovery"}
     */
    require 'notify.phar';

    $thread = new \thread\CThread(require 'setting.php');
    $thread->listen();