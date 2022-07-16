<?php

if (role(['permissions' => ['avatars' => 'upload']])) {

    $form = array();
    include 'fns/filters/load.php';
    include 'fns/files/load.php';

    $form['loaded'] = new stdClass();
    $form['fields'] = new stdClass();


    $form['loaded']->title = Registry::load('strings')->upload_avatar;
    $form['loaded']->button = Registry::load('strings')->upload;



    $form['fields']->process = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "add"
    ];

    $form['fields']->add = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "avatars"
    ];

    $form['fields']->supported_files = [
        "title" => Registry::load('strings')->supported_image_formats, "tag" => 'input', "type" => "text", "class" => 'field',
        "attributes" => ["disabled" => "yes"],
        "value" => 'JPEG, GIF & PNG',
    ];

    if (function_exists('ini_get')) {
        $form['fields']->max_upload_size = [
            "title" => Registry::load('strings')->max_file_upload_size, "tag" => 'input', "type" => "text", "class" => 'field',
            "attributes" => ["disabled" => "yes"],
            "value" => ini_get('upload_max_filesize'),
        ];
    }


    $form['fields']->avatars = [
        "title" => Registry::load('strings')->avatars, "tag" => 'input', "type" => 'file', 'multi_select' => true,
        "class" => 'field filebrowse',
        "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
    ];
    $form['fields']->avatars['infotip'] = Registry::load('strings')->infotip_select_multiple_files;

}
?>