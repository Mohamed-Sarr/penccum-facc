<?php

$access_token = explode('/', $permalink);

if (isset($access_token[1]) && isset($access_token[2])) {
    $user_id = $access_token[1];
    $access_token = $access_token[2];
    $columns = $join = $where = null;
    $alert_message = Registry::load('strings')->access_token_expired;
    $alert_type = 'warning';

    $columns = ['site_users.username', 'site_users.token_generated_on'];

    $where["AND"] = ["site_users.user_id" => $user_id, "site_users.access_token" => $access_token];
    $where["LIMIT"] = 1;

    $verify_token = DB::connect()->select('site_users', $columns, $where);

    if (isset($verify_token[0])) {
        $time_now = new DateTime(get_date());
        $token_generate_time = new DateTime($verify_token[0]['token_generated_on']);
        $time_difference = $time_now->diff($token_generate_time);
        $time_difference = $time_difference->h + ($time_difference->days * 24);

        if ($time_difference <= 3) {

            include_once('fns/add/load.php');

            $alert_message = $alert_type = null;
            $login_session = [
                'add' => 'login_session',
                'user' => $verify_token[0]['username'],
                'return' => true
            ];
            add($login_session, ['force_request' => true]);
            redirect('');
        }
    }
}