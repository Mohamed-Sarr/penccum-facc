<?php

if (role(['permissions' => ['audio_player' => 'edit']])) {

    include 'fns/filters/load.php';
    $form = array();

    if (isset($load['audio_file'])) {
        $load['audio_file'] = sanitize_filename($load['audio_file']);
    } else {
        return false;
    }

    if (isset($load["audio_content_id"]) && !empty($load['audio_file'])) {

        $form['loaded'] = new stdClass();
        $form['fields'] = new stdClass();

        $columns = [
            'audio_player.audio_title',
            'audio_player.audio_description'
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

        $form['fields']->audio_file = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load["audio_file"]
        ];

        $form['loaded']->title = Registry::load('strings')->rename_audio_file;
        $form['loaded']->button = Registry::load('strings')->update;
        
        $form['fields']->update = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "audio_file"
        ];

        $load['audio_file'] = explode('-gr-', $load['audio_file'], 2);

        if (isset($load['audio_file'][1])) {
            $load['audio_file'] = $load['audio_file'][1];
        } else {
            $load['audio_file'] = $load['audio_file'][0];
        }

        $load['audio_file'] = pathinfo($load['audio_file']);
        $load['audio_file'] = $load['audio_file']['filename'];

        $form['fields']->new_file_name = [
            "title" => Registry::load('strings')->file_name, "tag" => 'input', "type" => "text", "class" => 'field',
            "value" => $load['audio_file']
        ];

        $form['fields']->audio_title = [
            "title" => Registry::load('strings')->playlist, "tag" => 'input', "type" => "text", "class" => 'field',
            "attributes" => ["disabled" => true], "value" => $audio['audio_title']
        ];

        $form['fields']->description = [
            "title" => Registry::load('strings')->description, "tag" => 'textarea', "class" => 'field',
            "attributes" => ["rows" => 6, "disabled" => true], "value" => $audio['audio_description']
        ];



    }
}

?>