<?php

function isEnabled($func) {
    return is_callable($func) && false === stripos(ini_get('disable_functions'), $func);
}

$requirements = [
    'php_version' => [
        'title' => 'PHP Version',
        'description' => 'Requires at least PHP version 7.4 or higher. We recommend using PHP 8.0 or higher.',
        'required' => true,
    ],
    'server_software' => [
        'title' => 'Server Software',
        'description' => 'We recommend Apache or NGINX as the most robust server for our script, however, any server that supports PHP and MySQL will do.',
    ],
    'pdo' => [
        'title' => 'PDO PHP Extension',
        'required' => true,
    ],
    'pdo_mysql' => [
        'title' => 'PDO MySQL PHP Extension',
        'required' => true,
    ],
    'curl' => [
        'title' => 'cURL PHP Extension',
        'required' => true,
    ],
    'dom' => [
        'title' => 'DOM PHP Extension',
        'required' => true,
    ],
    'openssl' => [
        'title' => 'OpenSSL PHP Extension',
        'required' => true,
    ],
    'mbstring' => [
        'title' => 'MBString PHP Extension',
        'required' => true,
    ],
    'exif' => [
        'title' => 'Exif PHP Extension',
        'required' => true,
    ],
    'imap' => [
        'title' => 'IMAP PHP Extension',
    ],
    'pcre' => [
        'title' => 'PCRE PHP Extension',
        'required' => true,
    ],
    'gd' => [
        'title' => 'GD PHP Extension',
        'required' => true,
    ],
    'zip' => [
        'title' => 'Zip PHP Extension',
        'required' => true,
    ],
    'fileinfo' => [
        'title' => 'FileInfo PHP Extension',
        'required' => true,
    ],
    'write_permission' => [
        'title' => 'File Permissions',
        'required' => true,
    ],
    'json' => [
        'title' => 'Json PHP Extension',
        'required' => true,
    ],
    'mod_rewrite' => [
        'title' => 'mod_rewrite Extension',
        'required' => true,
    ],
    'allow_url_fopen' => [
        'title' => 'allow_url_fopen',
        'required' => true,
    ],
    'imagick' => [
        'title' => 'Imagick Extension (Optional)',
    ],
    'output_buffering' => [
        'title' => 'Output buffering (Optional)',
    ],
    'ffmpeg' => [
        'title' => 'FFmpeg (Optional)',
    ],
];

if (strpos(strtolower($_SERVER["SERVER_SOFTWARE"]), "apache") === false && strpos(strtolower($_SERVER["SERVER_SOFTWARE"]), "litespeed") === false) {
    unset($requirements['mod_rewrite']);
} elseif (!isEnabled('apache_get_modules')) {
    unset($requirements['mod_rewrite']);
}

if (isset($requirements['mod_rewrite'])) {
    if (!file_exists('.htaccess')) {
        if (file_exists('htaccess.backup')) {
            rename('htaccess.backup', '.htaccess');
        }
    }
}

?>


<div class="system_requirements">
    <div class="list-group">
        <?php

        $proceed = true;

        foreach ($requirements as $index => $requirement) {
            $image = Registry::load('config')->site_url.'assets/files/defaults/installer_warning.png';

            $output = 'Disabled';
            $result = false;

            if (isset($requirement['required']) && $requirement['required']) {
                $image = Registry::load('config')->site_url.'assets/files/defaults/installer_error.png';
            }

            if (!isset($requirement['description'])) {
                $requirement['description'] = '';
            }

            if ($index === 'php_version') {
                $output = PHP_VERSION;

                if (version_compare(PHP_VERSION, '7.4') >= 0) {
                    $result = true;
                }
            } elseif ($index === 'server_software') {
                $output = $_SERVER["SERVER_SOFTWARE"];

                if (strpos(strtolower($_SERVER["SERVER_SOFTWARE"]), "nginx") !== false) {
                    $output .= '<br><br>You are using Grupo with Nginx Server, you will need to setup Nginx specific rewrite rules.';
                }

                if (strpos(strtolower($_SERVER["SERVER_SOFTWARE"]), "apache") !== false || strpos(strtolower($_SERVER["SERVER_SOFTWARE"]), "litespeed") !== false || strpos(strtolower($_SERVER["SERVER_SOFTWARE"]), "nginx") !== false) {
                    $result = true;
                }
            } elseif ($index === 'write_permission') {
                if (is_writable('assets') && is_writable('pages')) {
                    $result = true;
                    $output = 'Writable';
                } else {
                    $output = 'You do not have permissions to create a directory or to write files. ';
                    $output .= 'Kindly check File Ownership & File Permissions.';
                }
            } elseif ($index === 'mod_rewrite') {
                if (in_array('mod_rewrite', apache_get_modules())) {
                    $result = true;
                    $output = 'Enabled';
                }
            } elseif ($index === 'output_buffering') {
                if (ini_get('output_buffering')) {
                    $result = true;
                    $output = 'Enabled';
                }
            } elseif ($index === 'allow_url_fopen') {
                if (ini_get('allow_url_fopen')) {
                    $result = true;
                    $output = 'Enabled';
                }
            } elseif ($index === 'imagick') {
                if (extension_loaded('imagick') || class_exists("Imagick")) {
                    $result = true;
                    $output = 'Enabled';
                }
            } elseif ($index === 'ffmpeg') {
                $output = 'Unrecognizable';

                if (isEnabled('shell_exec')) {
                    $ffmpeg = shell_exec('which ffmpeg');
                    if (!empty($ffmpeg)) {
                        $result = true;
                        $output = 'Enabled';
                    }
                }
            } elseif (extension_loaded($index)) {
                $output = 'Enabled';
                $result = true;
            }

            if ($result) {
                $image = Registry::load('config')->site_url.'assets/files/defaults/installer_tick.png';
            } else {
                if (isset($requirement['required']) && $requirement['required']) {
                    $proceed = false;
                }
            } ?>
            <div class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
                <img src="<?php echo $image; ?>" width="32" height="32" class="flex-shrink-0" />
                <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                        <h6 class="mb-0 title"><?php echo $requirement['title']; ?></h6>
                        <p class="mb-10 description">
                            <?php echo $requirement['description']; ?>
                        </p>
                        <p class="mb-0 result">
                            Result : <?php echo $output; ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php
        } ?>

    </div>
    <?php

    if (!$proceed) {
        ?>
        <div class="error mt-4 text-center">
            <div class="alert alert-danger" role="alert">
                <strong>NOTE :</strong> Does not meet minimum requirements for installing the Application.
            </div>
        </div>
        <?php
    } else {
        ?>
        <div class="proceed mt-4 text-center">
            <span>Proceed</span>
        </div>
        <?php
    } ?>
</div>