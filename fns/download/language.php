<?php

$language_id = 0;

if (isset($download['language_id'])) {
    $language_id = filter_var($download["language_id"], FILTER_SANITIZE_NUMBER_INT);
}

$file = 'assets/cache/languages/language-'.$language_id.'.cache';

if (!empty($language_id) && file_exists($file)) {
    if (role(['permissions' => ['languages' => 'export']])) {

        if (!isset($download['validate'])) {
            $download_language = [
                'download' => $file,
                'download_as' => 'language_file.json',
                'real_path' => true
            ];

            files('download', $download_language);
        } else {
            $output['download_link'] = Registry::load('config')->site_url.'download/language/language_id/'.$language_id;
        }
    } else {
        $output['error'] = Registry::load('strings')->permission_denied;
    }
} else {
    $output['error'] = Registry::load('strings')->file_expired;
}

?>