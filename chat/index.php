<?php
    require('Chat.php');
    require('Sync.php');
    
    $opts = [
        'suite_id' => 'wxb388a3469a14426b',
        'corp_id' => 'wwfd9b94c8c22a0bdf',
        'token' => 'ni6bBmtw5Nwnil_Pw9IJejPNZRbZ9bJYovkJWcnnMwTqVOBmzWGvQY5Si9c9cIJ8HzHRnjf_m7PwHtXFogZeAr0K1zlCkucCiqgl9e5qXkCx1CQDdjb3JPfVDdfbfQVMp-hZ13dbNb5gOkfg8xp4hTbV4U0vmSaO6WOg29Zerk4_ez3EeUsT7vrt_YayYWvT00dTgazNuiYW_AFD0EsnLg',
    ];
    $sync = new Sync($opts);
    print_r($sync->auth());
    
    
