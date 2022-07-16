<?php

include 'fns/filters/load.php';

$result = array();
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->invalid_value;
$result['error_key'] = 'invalid_value';
$result['error_variables'] = ['nickname'];
$noerror = true;
$strict_mode = true;


if (Registry::load('settings')->non_latin_usernames === 'enable') {
    $strict_mode = false;
}

if (!isset($data['nickname'])) {
    $noerror = false;
} else {
    $data['username'] = sanitize_username($data['nickname'], $strict_mode);
    if (empty(trim($data['username']))) {
        $noerror = false;
    }
}

if (Registry::load('settings')->guest_login !== 'enable') {
    $result['error_message'] = Registry::load('strings')->went_wrong;
    $result['error_key'] = 'something_went_wrong';
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
}

if ($noerror) {
    $guest_user = [
        'add' => 'site_users',
        'full_name' => $data['nickname'],
        'username' => $data['username'],
        'password' => random_string(['length' => 6]),
        'signup_page' => true,
        'return' => true
    ];

    if (username_exists($data['username'])) {
        $guest_user['username'] = $data['username'].'_'.random_string(['length' => 5]);
    }

    $guest_user['email_address'] = 'user_'.strtotime("now").'@'.random_string(['length' => 5]).'.guestuser';

    if (isset($data['redirect'])) {
        $guest_user['redirect'] = $data['redirect'];
    }
    $result = add($guest_user, ['force_request' => true, 'exclude_filters_function' => true, 'guest_user' => true]);
}

?>