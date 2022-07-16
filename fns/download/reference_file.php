<?php

$file_name = '';

if (isset($download["import_users"])) {
    if (role(['permissions' => ['site_users' => 'import_users']])) {
        $file_name = 'fns/download/reference_files/import_users.csv';
    }
}

if (!empty($file_name) && file_exists($file_name)) {
    $download_file = [
        'download' => $file_name,
        'real_path' => true
    ];

    files('download', $download_file);
} else {
    $output['error'] = Registry::load('strings')->file_expired;
}

?>