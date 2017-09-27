<?php
    return array(
        0x01 => array(
            'username' => 'char|33',
            'password' => 'char|33'
        ),
        0x02 => array(
            'return_value' => 'int|8'
        ),
        0x03 => array(
            'ticket_count' => 'int|8',
            'list' => array(
                'length' => 'ticket_count',
                'print_no' => 'char|17'
            )
        ),
        0x04 => array(
            'return_value' => 'int|8',
            'ticket_count' => 'int|8',
            'list' => array(
                'print_no' => 'char|17',
                'ticket_info_code' => 'char|181',
                'cinema_code' => 'char|9',
                'cinema_name' => 'char|64',
                'screen_code' => 'char|17',
                'screen_name' => 'char|64',
                'film_code' => 'char|13',
                'film_name' => 'char|64',
                'session_code' => 'char|17',
                'session_datetime' => 'datetime|0',
                'code' => 'char|17',
                'seat_code' => 'char|17',
                'seat_name' => 'char|32',
                'price' => 'float|0',
                'service' => 'float|0',
                'print_flag' => 'int|8'
            )
        ),

        0x05 => array(
            'ticket_count' => 'int|8',
            'list' => array(
                'length' => 'ticket_count',

                'print_no' => 'char|17',
                'verify_code_md5' => 'char|33'
            )
        ),
        0x06 => array(
            'ticket_count' => 'int|8',
            'list' => array(
                'print_no' => 'char|17',
                'return_value' => 'int|8'
            )
        ),
        0x07 => array(
            'ticket_count' => 'int|8',
            'list' => array(
                'length' => 'ticket_count',
                'print_no' => 'char|17'
            )
        ),
        0x08 => array(
            'return_value' => 'int|8'
        )
    );
