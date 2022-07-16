<?php

if (role(['permissions' => ['audio_player' => ['add', 'edit']], 'condition' => 'OR'])) {
    $form = array();

    $todo = 'add';
    $form['loaded'] = new stdClass();
    $form['fields'] = new stdClass();

    if (isset($load["audio_content_id"])) {

        $todo = 'update';
        $columns = [
            'audio_player.audio_title', 'audio_player.disabled',
            'audio_player.audio_description', 'audio_player.audio_type',
            'audio_player.radio_stream_url',
        ];

        $where["audio_player.audio_content_id"] = $load["audio_content_id"];
        $where["LIMIT"] = 1;

        $audio = DB::connect()->select('audio_player', $columns, $where);

        if (!isset($audio[0])) {
            return false;
        } else {
            $audio = $audio[0];
        }

        $form['fields']->audio_content_id = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load["audio_content_id"]
        ];

        $form['loaded']->title = Registry::load('strings')->edit_audio;
        $form['loaded']->button = Registry::load('strings')->update;
    } else {
        $form['loaded']->title = Registry::load('strings')->add_audio;
        $form['loaded']->button = Registry::load('strings')->add;
    }


    $form['fields']->process = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $todo
    ];

    $form['fields']->$todo = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "audio_player_content"
    ];

    $form['fields']->audio_title = [
        "title" => Registry::load('strings')->title, "tag" => 'input', "type" => "text", "class" => 'field',
        "placeholder" => Registry::load('strings')->title,
    ];

    $form['fields']->description = [
        "title" => Registry::load('strings')->description, "tag" => 'textarea', "class" => 'field',
        "placeholder" => Registry::load('strings')->description
    ];

    $form['fields']->description["attributes"] = ["rows" => 6];

    $form['fields']->image = [
        "title" => Registry::load('strings')->icon, "tag" => 'input', "type" => 'file', "class" => 'field filebrowse',
        "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
    ];

    $form['fields']->audio_type = [
        "title" => Registry::load('strings')->type, "tag" => 'select', "class" => 'field showfieldon'
    ];

    $form['fields']->audio_type["attributes"] = [
        "fieldclass" => "stream_url",
        "checkvalue" => "radio_station",
        "hideclass" => "audio_files"
    ];

    $form['fields']->audio_type['options'] = [
        "radio_station" => Registry::load('strings')->radio_station,
        "playlist" => Registry::load('strings')->playlist,
    ];

    $form['fields']->audio_files = [
        "title" => Registry::load('strings')->audio_files, "tag" => 'input', "type" => 'file',
        "multi_select" => true, "class" => 'field filebrowse audio_files d-none',
        "accept" => 'audio/wav,audio/mpeg,audio/mp4,audio/webm,audio/ogg,audio/x-wav'
    ];
    $form['fields']->audio_files['infotip'] = Registry::load('strings')->infotip_select_multiple_files;

    $form['fields']->stream_url = [
        "title" => Registry::load('strings')->stream_url, "tag" => 'input', "type" => "text",
        "class" => 'stream_url field base_encode d-none',
        "placeholder" => Registry::load('strings')->stream_url,
    ];

    $form['fields']->disabled = [
        "title" => Registry::load('strings')->disabled, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->disabled['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    if (isset($load["audio_content_id"])) {
        $disabled = 'no';

        if ((int)$audio['disabled'] === 1) {
            $disabled = 'yes';
        }

        $form['fields']->audio_title["value"] = $audio['audio_title'];
        $form['fields']->description["value"] = $audio['audio_description'];
        $form['fields']->stream_url["value"] = $audio['radio_stream_url'];
        $form['fields']->disabled["value"] = $disabled;


        if ((int)$audio['audio_type'] === 1) {
            $form['fields']->audio_type["value"] = 'radio_station';
            $form['fields']->stream_url["class"] = 'stream_url field base_encode';
        } else {
            $form['fields']->audio_type["value"] = 'playlist';
            $form['fields']->audio_files["class"] = 'field filebrowse audio_files';
        }

    }
}

?>