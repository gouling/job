<?php
    /**
     * php 5.3.29 +
     * phar.readonly = Off
     */
    require 'notify.phar';

    $thread = new \thread\CThread(require 'setting.php');
    $thread->listen();