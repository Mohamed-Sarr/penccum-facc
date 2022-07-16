<?php
if (Registry::load('current_user')->logged_in) {

    session_write_close();

    $current_user_id = Registry::load('current_user')->id;

    $raw_post = json_decode(file_get_contents('php://input'), true);
    $update_data = ['last_seen_on' => Registry::load('current_user')->time_stamp];

    if (!empty($raw_post) && isset($raw_post['offline'])) {
        $update_data['online_status'] = 0;
    } else {
        $update_data['online_status'] = 1;
    }


    DB::connect()->update("site_users", $update_data, ["user_id" => $current_user_id]);

    exit;
}