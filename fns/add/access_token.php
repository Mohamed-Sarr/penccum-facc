<?php

$result = array();
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->account_not_found;
$result['error_key'] = 'account_not_found';
$result['error_variables'] = [];
$noerror = true;
$generate_token = false;
$current_timestamp = Registry::load('current_user')->time_stamp;

$access_token = random_string(['length' => 10]);

if (!isset($data['user']) || empty(trim($data['user']))) {
    $result['error_message'] = Registry::load('strings')->invalid_value;
    $result['error_key'] = 'invalid_value';
    $result['error_variables'][] = 'user';
    $noerror = false;
}

if (!Registry::load('current_user')->logged_in) {

    if (isset(Registry::load('settings')->captcha) && Registry::load('settings')->captcha !== 'disable') {
        include 'fns/captcha/load.php';
    }

    if (isset(Registry::load('settings')->captcha) && Registry::load('settings')->captcha === 'google_recaptcha_v2') {
        if (!isset($data['g-recaptcha-response']) || empty(trim($data['g-recaptcha-response']))) {
            $result['error_message'] = Registry::load('strings')->invalid_captcha;
            $result['error_variables'][] = 'captcha';
            $noerror = false;
        } else if (!validate_captcha('google_recaptcha_v2', $data['g-recaptcha-response'])) {
            $result['error_message'] = Registry::load('strings')->invalid_captcha;
            $result['error_variables'][] = 'captcha';
            $noerror = false;
        }
    } else if (isset(Registry::load('settings')->captcha) && Registry::load('settings')->captcha === 'hcaptcha') {
        if (!isset($data['h-captcha-response']) || empty(trim($data['h-captcha-response']))) {
            $result['error_message'] = Registry::load('strings')->invalid_captcha;
            $result['error_variables'][] = 'captcha';
            $noerror = false;
        } else if (!validate_captcha('hcaptcha', $data['h-captcha-response'])) {
            $result['error_message'] = Registry::load('strings')->invalid_captcha;
            $result['error_variables'][] = 'captcha';
            $noerror = false;
        }
    }

    if ($noerror) {
        $columns = $join = $where = null;

        $columns = [
            'site_users.user_id', 'site_users.access_token', 'site_users.token_generated_on',
            'site_users.site_role_id', 'site_roles.site_role_attribute', 'site_users.email_address'
        ];

        $join["[>]site_roles"] = ['site_users.site_role_id' => 'site_role_id'];

        $where["OR"] = ["site_users.username" => $data['user'], "site_users.email_address" => $data['user']];
        $where["LIMIT"] = 1;

        $site_user = DB::connect()->select('site_users', $join, $columns, $where);

        if (isset($site_user[0])) {

            $site_user = $site_user[0];
            $hashed_password = null;
            $columns = $join = $where = null;


            if ($site_user['site_role_attribute'] === 'unverified_user_role') {
                $result = array();
                $result['success'] = false;
                $result['error_message'] = Registry::load('strings')->confirm_your_email_address;
                $result['error_key'] = 'confirm_your_email_address';
                $result['error_type'] = "warning";
            } else if ($site_user['site_role_attribute'] === 'banned_user_role') {
                $result = array();
                $result['success'] = false;
                $result['error_message'] = Registry::load('strings')->account_banned;
                $result['error_key'] = 'account_banned';
                $result['error_type'] = "message";
            } else {

                $time_now = new DateTime($current_timestamp);
                $token_generate_time = new DateTime($site_user['token_generated_on']);
                $time_difference = $time_now->diff($token_generate_time);
                $time_difference = $time_difference->h + ($time_difference->days * 24);

                if ($time_difference >= 3 || empty($site_user['access_token'])) {
                    $update = ['access_token' => $access_token, 'token_generated_on' => $current_timestamp];
                    $where = ['site_users.user_id' => $site_user['user_id']];
                    DB::connect()->update('site_users', $update, $where);
                } else {
                    $access_token = $site_user['access_token'];
                }

                $generate_token = true;
            }
        }
        if ($generate_token) {

            include('fns/mailer/load.php');

            $password_reset_link = Registry::load('config')->site_url.'entry/access_token/'.$site_user['user_id'].'/'.$access_token;

            $mail = array();
            $mail['email_addresses'] = $site_user['email_address'];
            $mail['category'] = 'reset_password';
            $mail['user_id'] = $site_user['user_id'];
            $mail['parameters'] = ['link' => $password_reset_link];
            $mail['send_now'] = true;
            mailer('compose', $mail);

            $result = array();
            $result['success'] = true;
            $result['todo'] = 'alert';
            $result['message'] = Registry::load('strings')->reset_password_success_message;
            $result['type'] = 'success';

        }
    }

}
?>