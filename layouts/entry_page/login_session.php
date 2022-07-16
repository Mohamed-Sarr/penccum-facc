<?php

if (isset($_GET['login_session_id']) && isset($_GET['access_code']) && isset($_GET['session_time_stamp'])) {

    if (isset(Registry::load('settings')->login_cookie_validity) && !empty(Registry::load('settings')->login_cookie_validity)) {
        $cookie_time = time() + (86400 * Registry::load('settings')->login_cookie_validity);
    } else {
        $cookie_time = time() + (86400);
    }

    add_cookie('login_session_id', $_GET['login_session_id'], $cookie_time);
    add_cookie('access_code', $_GET['access_code'], $cookie_time);
    add_cookie('session_time_stamp', $_GET['session_time_stamp'], $cookie_time);

    add_cookie('current_language_id', 0);
    add_cookie('current_color_scheme', 0);

    if (isset($_GET['redirect'])) {
        redirect($_GET['redirect']);
    } else {
        redirect('');
    }

    exit;
}

?>