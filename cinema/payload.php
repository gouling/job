<?php
    $ack = array(
        'ack' => 'int|8',
        'delay' => 'int|32'
    );

    return array(
        0x01 => array(
            'username' => 'char|33',
            'password' => 'char|33',
            'software_md5' => 'char|33',
            'raw_data_count' => 'int|32'
        ),
        0x02 => array(
            'return_value' => 'int|8',
            'software_update' => 'char|33'
        ),

        0x03 => array(
            'cinema_code' => 'char|9',
            'datetime' => 'datetime|0'
        ),

        0x04 => array(
            'code' => 'int|32',
            'title' => 'char|60',
            'source' => 'char|60',
            'content_length' => 'int|16',
            'content' => 'char|1|content_length'
        ),
        0x05 => array(
            'return_value' => 'int|8'
        ),
        0x06 => array(
            'cinema_code' => 'char|9',
            'request' => 'int|8'
        ),
        0x07 => array(
            'return_value' => 'int|8',
            'code' => 'char|9',
            'name' => 'char|64',
            'corporation' => 'char|64',
            'contact' => 'char|32',
            'telephone' => 'char|32',
            'fax' => 'char|32',
            'cinemas' => 'char|64',
            'status' => 'int|8',
            'screen_count' => 'int|8'
        ),
        0x08 => array(
            'start_date' => 'date|0',
            'end_date' => 'date|0'
        ),
        0x09 => array(
            'return_value' => 'int|8',
            'total' => 'int|32',
            'remain' => 'int|32',
            'count' => 'int|8',
            'list' => array(
                'length' => 'count',

                'code' => 'char|13',
                'name' => 'char|64',
                'version' => 'char|11',
                'duration' => 'int|16',
                'publish_date' => 'date|0',
                'publisher' => 'char|64',
                'producer' => 'char|32',
                'director' => 'char|32',
                'cast' => 'char|256',
                'introduction' => 'char|512'
            )
        ),
        0x0A => $ack,
        0x0B => array(
            'screen_code' => 'char|17'
        ),
        0x0C => array(
            'return_value' => 'int|8',
            'total' => 'int|32',
            'remain' => 'int|32',
            'count' => 'int|8',
            'list' => array(
                'code' => 'char|17',
                'name' => 'char|64',
                'seat_count' => 'int|16'
            )
        ),
        0x0D => $ack,
        0x0E => array(
            'screen_code' => 'char|17'
        ),
        0x0F => array(
            'return_value' => 'int|8',
            'total' => 'int|32',
            'remain' => 'int|32',
            'count' => 'int|8',
            'screen_code' => 'char|17',
            'list' => array(
                'code' => 'char|17',
                'row_num' => 'char|16',
                'column_num' => 'char|16',
                'x_coord' => 'int|32',
                'y_coord' => 'int|32'
            )
        ),
        0x10 => $ack,
        0x11 => array(
            'cinema_code' => 'char|9',
            'business_date' => 'date|0',
            'screen_code' => 'char|17',
            'film_code' => 'char|13',
            'session_code' => 'char|17',
            'session_datetime' => 'datetime|0',
            'count' => 'int|16',
            'list' => array(
                'code' => 'char|33',
                'price' => 'float|0'
            )
        ),
        0x12 => array(
            'cinema_code' => 'char|9',
            'business_date' => 'date|0',
            'screen_code' => 'char|17',
            'film_code' => 'char|13',
            'session_code' => 'char|17',
            'session_datetime' => 'datetime|0',
            'count' => 'int|16',
            'price' => 'float|0'
        ),
        0x13 => array(
            'return_value' => 'int|8',
            'code' => 'char|33'
        ),
        0x14 => array(
            'code' => 'char|33',
            'return_value' => 'int|8',
            'error_content_length' => 'int|16',
            'error_content' => 'char|1|error_content_length'
        ),
        0x15 => array(
            'data' => 'char|0'
        ),
        0x16 => array(
            'start_business_date' => 'date|0',
            'end_business_date' => 'date|0',
            'screen_code' => 'char|17',
            'film_code' => 'char|13',
            'session_code' => 'char|17',
            'start_session_datetime' => 'datetime|0',
            'end_session_datetime' => 'datetime|0'
        ),
        0x17 => array(
            'return_value' => 'int|8',
            'total' => 'int|32',
            'remain' => 'int|32',
            'count' => 'int|8',
            'list' => array(
                'business_date' => 'date|0',
                'screen_code' => 'char|17',
                'film_code' => 'char|13',
                'session_code' => 'char|17',
                'session_datetime' => 'datetime|0',
                'seat_by_number' => 'int|8',
                'price' => 'float|0',
                'lowest_price' => 'float|0'
            )
        ),
        0x18 => $ack,
        0x19 => array(
            'screen_code' => 'char|17',
            'film_code' => 'char|13',
            'session_code' => 'char|17',
            'start_datetime' => 'datetime|0',
            'end_datetime' => 'datetime|0'
        ),
        0x1A => array(
            'return_value' => 'int|8',
            'total' => 'int|32',
            'remain' => 'int|32',
            'count' => 'int|8',
            'list' => array(
                'business_date' => 'date|0',
                'screen_code' => 'char|17',
                'film_code' => 'char|13',
                'session_code' => 'char|17',
                'session_datetime' => 'datetime|0',
                'seat_by_number' => 'int|8',
                'price' => 'float|0',
                'lowest_price' => 'float|0',
                'operation' => 'int|8',
                'operation_datetime' => 'datetime|0'
            )
        ),
        0x1B => $ack,
        0x1C => array(
            'cinema_code' => 'char|9',
            'cinema_status' => 'int|8',
            'business_date' => 'date|0',
            'screen_code' => 'char|17',
            'film_code' => 'char|13',
            'session_code' => 'char|17',
            'session_datetime' => 'datetime|0',
            'operation' => 'int|8',
            'code' => 'char|17',
            'seat_code' => 'char|17',
            'price' => 'float|0',
            'service' => 'float|0',
            'online_sale' => 'int|8',
            'datetime' => 'datetime|0'
        ),
        0x1D => array(
            'start_business_date' => 'date|0',
            'end_business_date' => 'date|0',
            'screen_code' => 'char|17',
            'film_code' => 'char|13',
            'session_code' => 'char|17',
            'code' => 'char|32',
            'start_datetime' => 'datetime|0',
            'end_datetime' => 'datetime|0',
            'flag' => 'int|8'
        ),
        0x1E => array(
            'return_value' => 'int|8',
            'total' => 'int|32',
            'remain' => 'int|32',
            'count' => 'int|8',
            'list' => array(
                'business_date' => 'date|0',
                'screen_code' => 'char|17',
                'film_code' => 'char|13',
                'session_code' => 'char|17',
                'session_datetime' => 'datetime|0',
                'operation' => 'int|8',
                'code' => 'char|17',
                'seat_code' => 'char|17',
                'price' => 'float|0',
                'service' => 'float|0',
                'online_sale' => 'int|8',
                'datetime' => 'datetime|0'
            )
        ),
        0x1F => $ack,
        0x20 => array(
            'start_business_date' => 'date|0',
            'end_business_date' => 'date|0',
            'screen_code' => 'char|17',
            'film_code' => 'char|13',
            'session_code' => 'char|17',
            'start_session_datetime' => 'datetime|0',
            'end_session_datetime' => 'datetime|0'
        ),
        0x21 => array(
            'return_value' => 'int|8',
            'total' => 'int|32',
            'remain' => 'int|32',
            'count' => 'int|8',
            'list' => array(
                'cinema_status' => 'int|8',
                'business_date' => 'date|0',
                'screen_code' => 'char|17',
                'film_code' => 'char|13',
                'session_code' => 'char|17',
                'session_datetime' => 'datetime|0',
                'local_sales_count' => 'int|16',
                'local_refund_count' => 'int|16',
                'local_refund' => 'float|0',
                'local_sales' => 'float|0',
                'online_sales_count' => 'int|16',
                'online_refund_count' => 'int|16',
                'online_refund' => 'float|0',
                'online_sales' => 'float|0',
                'past_sale_count' => 'int|16',
                'past_sales' => 'float|0'
            )
        ),
        0x22 => $ack,
        0x23 => array(
            'software_md5' => 'char|33'
        ),
        0x24 => array(
            'return_value' => 'int|8',
            'software_md5' => 'char|33',
            'update_md5' => 'char|33',
            'ftp_address' => 'char|256',
            'ftp_username' => 'char|32',
            'ftp_password' => 'char|32'
        ),
        0x25 => array(
            'data' => 'char|1'
        ),
        0x26 => array(
            'return_value' => 'int|8',
            'name' => 'char|33',
            'version' => 'char|10',
            'manufacturer' => 'char|33',
            'release_date' => 'date|0',
            'test_date' => 'date|0',
            'register_date' => 'date|0',
            'md5' => 'char|33',
            'install_date' => 'date|0',
            'update_date' => 'date|0'
        ),
        0x27 => array(
            'cinema_code' => 'char|9'
        ),
        0x28 => array(
            'return_value' => 'int|8',
            'public_key' => 'char|132'
        )
    );
