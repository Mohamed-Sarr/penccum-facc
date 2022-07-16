<?php

if (role(['permissions' => ['site_users' => 'import_users']])) {

    $form = array();
    include 'fns/filters/load.php';
    include 'fns/files/load.php';

    $form['loaded'] = new stdClass();
    $form['fields'] = new stdClass();


    $form['loaded']->title = Registry::load('strings')->import_users;
    $form['loaded']->button = Registry::load('strings')->import;



    $form['fields']->process = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "add"
    ];

    $form['fields']->add = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "bulk_users"
    ];

    $form['fields']->supported_files = [
        "title" => Registry::load('strings')->supported_file_formats, "tag" => 'input', "type" => "text", "class" => 'field',
        "attributes" => ["disabled" => "yes"],
        "value" => 'Comma-separated values (CSV)',
    ];

    if (function_exists('ini_get')) {
        $form['fields']->max_upload_size = [
            "title" => Registry::load('strings')->max_file_upload_size, "tag" => 'input', "type" => "text", "class" => 'field',
            "attributes" => ["disabled" => "yes"],
            "value" => ini_get('upload_max_filesize'),
        ];
    }

    $sample_reference_file = Registry::load('config')->site_url.'download/reference_file/import_users/csv/';

    $form['fields']->sample_reference_file = [
        "title" => Registry::load('strings')->sample_reference_file, "tag" => 'link', "type" => 'external_link',
        "text" => Registry::load('strings')->download, "link" => $sample_reference_file, "class" => 'field',
    ];

    $form['fields']->csv_file = [
        "title" => Registry::load('strings')->csv_file, "tag" => 'input', "type" => 'file',
        "class" => 'field filebrowse',
        "accept" => '.csv'
    ];

}
?>