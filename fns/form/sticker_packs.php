<?php

if (role(['permissions' => ['stickers' => ['create', 'edit']], 'condition' => 'OR'])) {

    $form = array();
    include 'fns/filters/load.php';
    include 'fns/files/load.php';

    $todo = 'add';
    $location = 0;
    $form['loaded'] = new stdClass();
    $form['fields'] = new stdClass();

    if (isset($load["sticker_pack"])) {
        $load["sticker_pack"] = sanitize_filename($load['sticker_pack']);
        $location = 'assets/files/stickers/'.$load["sticker_pack"].'/';
    }

    if (role(['permissions' => ['stickers' => 'edit']]) && isset($load["sticker_pack"]) && !empty($load["sticker_pack"]) && file_exists($location)) {

        $todo = 'update';
        $form['fields']->sticker_pack = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load["sticker_pack"]
        ];

        $form['loaded']->title = Registry::load('strings')->edit_sticker_pack;
        $form['loaded']->button = Registry::load('strings')->update;
    } else {
        $form['loaded']->title = Registry::load('strings')->create_sticker_pack;
        $form['loaded']->button = Registry::load('strings')->create;
    }

    $form['fields']->$todo = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "sticker_packs"
    ];

    $form['fields']->name = [
        "title" => Registry::load('strings')->name, "tag" => 'input', "type" => "text", "class" => 'field',
        "placeholder" => Registry::load('strings')->name,
    ];


    $form['fields']->image = [
        "title" => Registry::load('strings')->image, "tag" => 'input', "type" => 'file', "class" => 'field filebrowse',
        "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
    ];

    $form['fields']->stickers = [
        "title" => Registry::load('strings')->stickers, "tag" => 'input', "type" => 'file', 'multi_select' => true, "class" => 'field filebrowse',
        "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
    ];
    $form['fields']->stickers['infotip'] = Registry::load('strings')->infotip_select_multiple_files;

    if ($todo === 'update' && isset($load["sticker_pack"]) && !empty($load["sticker_pack"]) && file_exists($location)) {
        $form['fields']->name["value"] = $load["sticker_pack"];
    }
}
?>