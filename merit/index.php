#!/usr/bin/env /usr/share/php-7.2.0/bin/php

<?php
    require __DIR__ . '/lib/index.php';

    $app = CApp::create(require __DIR__ . '/setting.php')->initialize();
    $app->work('algorithm', 'set', getopt('', [
        'platformId:'
    ]));