<?php

$output = array();
$output['audio_player_class'] = ' d-none';
$output['audio_title'] = $output['audio_description'] = $output['audio_content_id'] = '';
$output['audio_mime_type'] = $output['audio_url'] = '';
$output['image'] = get_image(['from' => 'audio_player/images', 'search' => 0]);
$output['audio_type'] = 'radio_station';

if (!isset($_COOKIE["audio_current_playing_id"]) || empty($_COOKIE["audio_current_playing_id"])) {
    if (role(['permissions' => ['audio_player' => 'listen_music']])) {
        $columns = $where = null;
        $columns = [
            'audio_player.audio_content_id', 'audio_player.audio_title', 'audio_player.audio_type'
        ];

        $where["audio_player.disabled[!]"] = 1;
        $where["ORDER"] = ["audio_player.audio_content_id" => "DESC"];
        $where["LIMIT"] = 1;

        $audio_record = DB::connect()->select('audio_player', $columns, $where);

        if (isset($audio_record[0])) {
            $_COOKIE["audio_current_playing_id"] = $audio_record[0]['audio_content_id'];

            if ((int)$audio_record[0]['audio_type'] !== 1) {
                $random_audio_file = 'assets/files/audio_player/playlists/'.$audio_record[0]['audio_content_id'].'/';
                $random_audio_file = glob($random_audio_file.'*');
                $random_audio_file = $random_audio_file[rand(0, count($random_audio_file) - 1)];
                $_COOKIE["audio_current_playing_file_name"] = basename($random_audio_file);
            }
        }
    }
}

if (isset($_COOKIE["audio_current_playing_id"]) && !empty($_COOKIE["audio_current_playing_id"])) {
    $columns = $where = null;

    $_COOKIE["audio_current_playing_id"] = filter_var($_COOKIE["audio_current_playing_id"], FILTER_SANITIZE_NUMBER_INT);

    if (!empty($_COOKIE["audio_current_playing_id"])) {
        $columns = [
            'audio_player.audio_content_id', 'audio_player.audio_title', 'audio_player.radio_stream_url',
            'audio_player.audio_description', 'audio_player.audio_type',
        ];

        $where["audio_player.disabled[!]"] = 1;
        $where["audio_player.audio_content_id"] = $_COOKIE["audio_current_playing_id"];
        $where["ORDER"] = ["audio_player.audio_content_id" => "DESC"];
        $where["LIMIT"] = 1;

        $audio_record = DB::connect()->select('audio_player', $columns, $where);

        if (isset($audio_record[0])) {
            $output = $audio_record[0];
            $output['audio_player_class'] = '';
            $output['audio_mime_type'] = $output['audio_url'] = '';
            $output['image'] = get_image(['from' => 'audio_player/images', 'search' => $output['audio_content_id']]);

            if ((int)$output['audio_type'] === 1) {
                $output['audio_type'] = 'radio_station';
                $output['audio_url'] = $output['radio_stream_url'];
            } else {
                if (isset($_COOKIE["audio_current_playing_file_name"]) && !empty($_COOKIE["audio_current_playing_file_name"])) {

                    $audio_file_name = htmlspecialchars($_COOKIE["audio_current_playing_file_name"], ENT_QUOTES, 'UTF-8');
                    $audio_file_name = preg_replace('~[<>:"/\\|?*]|[\x00-\x1F]|[\x7F\xA0\xAD]|[#\[\]@!$&\'()+,;=]|[{}^\~`]~x', '-', $audio_file_name);

                    $output['audio_title'] = explode('-gr-', $audio_file_name, 2);

                    if (isset($output['audio_title'][1])) {
                        $output['audio_title'] = $output['audio_title'][1];
                    } else {
                        $output['audio_title'] = $audio_file_name;
                    }

                    $output['audio_title'] = pathinfo($output['audio_title']);
                    $output['audio_title'] = $output['audio_title']['filename'];
                    $output['audio_type'] = 'audio_file';
                    $output['audio_url'] = 'assets/files/audio_player/playlists/'.$output['audio_content_id'].'/'.$audio_file_name;

                    if (file_exists($output['audio_url'])) {
                        $output['audio_mime_type'] = mime_content_type($output['audio_url']);
                    }

                    $output['audio_url'] = Registry::load('config')->site_url.$output['audio_url'];
                }
            }

        }
    }
}