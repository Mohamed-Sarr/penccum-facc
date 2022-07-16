<?php

if (role(['permissions' => ['super_privileges' => 'core_settings']])) {

    $form = array();

    $form['loaded'] = new stdClass();
    $form['loaded']->title = Registry::load('strings')->system_info;

    $form['fields'] = new stdClass();


    if (isset(Registry::load('config')->app_version)) {
        $form['fields']->script_version = [
            "title" => 'Script Version', "tag" => 'input', "type" => 'text', "class" => 'field',
            "attributes" => ['disabled' => 'disabled'], 'value' => Registry::load('config')->app_version
        ];
    }

    $form['fields']->php_version = [
        "title" => 'PHP Version', "tag" => 'input', "type" => 'text', "class" => 'field',
        "attributes" => ['disabled' => 'disabled'], 'value' => PHP_VERSION
    ];

    $form['fields']->server_software = [
        "title" => 'Server Software', "tag" => 'textarea', "class" => 'field',
        "attributes" => ['disabled' => 'disabled'], 'value' => $_SERVER["SERVER_SOFTWARE"]
    ];

    if (is_writable('assets') && is_writable('pages')) {
        $output = 'Writable';
    } else {
        $output = 'You do not have permissions to create a directory or to write files. ';
        $output .= 'Kindly check File Ownership & File Permissions.';
    }

    $form['fields']->file_permission = [
        "title" => 'File Permission', "tag" => 'input', "type" => "text", "class" => 'field',
        "attributes" => ['disabled' => 'disabled'], 'value' => $output
    ];


    $output = ini_get('upload_max_filesize');
    $form['fields']->upload_max_filesize = [
        "title" => 'Upload Max Filesize', "tag" => 'input', "type" => "text", "class" => 'field',
        "attributes" => ['disabled' => 'disabled'], 'value' => $output
    ];

    $output = ini_get('post_max_size');
    $form['fields']->post_max_size = [
        "title" => 'Post Max Size', "tag" => 'input', "type" => "text", "class" => 'field',
        "attributes" => ['disabled' => 'disabled'], 'value' => $output
    ];

    $output = ini_get('max_file_uploads');
    $form['fields']->max_file_uploads = [
        "title" => 'Max File Uploads', "tag" => 'input', "type" => "text", "class" => 'field',
        "attributes" => ['disabled' => 'disabled'], 'value' => $output
    ];

    $output = ini_get('memory_limit');
    $form['fields']->memory_limit = [
        "title" => 'Memory Limit', "tag" => 'input', "type" => "text", "class" => 'field',
        "attributes" => ['disabled' => 'disabled'], 'value' => $output
    ];

    $output = ini_get('max_input_vars');
    $form['fields']->max_input_vars = [
        "title" => 'Max Input Vars', "tag" => 'input', "type" => "text", "class" => 'field',
        "attributes" => ['disabled' => 'disabled'], 'value' => $output
    ];


    $php_extensions = [
        'pdo' => [
            'title' => 'PDO PHP Extension',
        ],
        'pdo_mysql' => [
            'title' => 'PDO MySQL PHP Extension',
        ],
        'curl' => [
            'title' => 'cURL PHP Extension',
        ],
        'dom' => [
            'title' => 'DOM PHP Extension',
        ],
        'openssl' => [
            'title' => 'OpenSSL PHP Extension',
        ],
        'mbstring' => [
            'title' => 'MBString PHP Extension',
        ],
        'exif' => [
            'title' => 'Exif PHP Extension',
        ],
        'imap' => [
            'title' => 'IMAP PHP Extension',
        ],
        'pcre' => [
            'title' => 'PCRE PHP Extension',
        ],
        'gd' => [
            'title' => 'GD PHP Extension',
        ],
        'zip' => [
            'title' => 'Zip PHP Extension',
        ],
        'fileinfo' => [
            'title' => 'FileInfo PHP Extension',
        ],
        'json' => [
            'title' => 'Json PHP Extension',
        ],
    ];

    foreach ($php_extensions as $index => $extension) {
        if (extension_loaded($index)) {
            $output = 'Enabled';
        } else {
            $output = 'Disabled';
        }

        $form['fields']->$index = [
            "title" => $extension['title'], "tag" => 'input', "type" => "text", "class" => 'field',
            "attributes" => ['disabled' => 'disabled'], 'value' => $output
        ];
    }

    if (ini_get('allow_url_fopen')) {
        $output = 'Enabled';
    } else {
        $output = 'Disabled';
    }

    $form['fields']->allow_url_fopen = [
        "title" => 'allow_url_fopen', "tag" => 'input', "type" => "text", "class" => 'field',
        "attributes" => ['disabled' => 'disabled'], 'value' => $output
    ];


    if (ini_get('output_buffering')) {
        $output = 'Enabled';
    } else {
        $output = 'Disabled';
    }

    $form['fields']->output_buffering = [
        "title" => 'Output Buffering', "tag" => 'input', "type" => "text", "class" => 'field',
        "attributes" => ['disabled' => 'disabled'], 'value' => $output
    ];

    if (extension_loaded('imagick') || class_exists("Imagick")) {
        $output = 'Enabled';
    } else {
        $output = 'Disabled';
    }

    $form['fields']->imagick = [
        "title" => 'Imagick', "tag" => 'input', "type" => "text", "class" => 'field',
        "attributes" => ['disabled' => 'disabled'], 'value' => $output
    ];

}