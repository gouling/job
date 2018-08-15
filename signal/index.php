#!/usr/bin/env /usr/share/php-7.2.7/bin/php
<?php
    include 'CData.php';
    include 'CTask.php';
    include 'CSignal.php';
    include 'CLog.php';
    
    new CTask(new CData());
