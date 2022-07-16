<?php

$result = array();
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->invalid_login;
$result['error_key'] = 'invalid_login';
$result['error_variables'] = [];
$noerror = true;
$valid_login = false;
$current_timestamp = Registry::load('current_user')->time_stamp;
$login_as_another_user = false;


if (isset($data['user'])) {
    $data['user'] = trim($data['user']);
}

if (!isset($data['user']) || empty(trim($data['user']))) {
    $result['error_message'] = Registry::load('strings')->invalid_value;
    $result['error_key'] = 'invalid_value';
    $result['error_variables'][] = 'user';
    $noerror = false;
}

if (Registry::load('current_user')->logged_in) {
    if (role(['permissions' => ['site_users' => 'login_as_another_user']])) {
        $force_request = true;
        $login_as_another_user = true;
    }
}

if (!$force_request) {
    if (!isset($data['password']) || empty(trim($data['password']))) {
        $result['error_message'] = Registry::load('strings')->invalid_value;
        $result['error_key'] = 'invalid_value';
        $result['error_variables'][] = 'password';
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
            } elseif (!validate_captcha('google_recaptcha_v2', $data['g-recaptcha-response'])) {
                $result['error_message'] = Registry::load('strings')->invalid_captcha;
                $result['error_variables'][] = 'captcha';
                $noerror = false;
            }
        } elseif (isset(Registry::load('settings')->captcha) && Registry::load('settings')->captcha === 'hcaptcha') {
            if (!isset($data['h-captcha-response']) || empty(trim($data['h-captcha-response']))) {
                $result['error_message'] = Registry::load('strings')->invalid_captcha;
                $result['error_variables'][] = 'captcha';
                $noerror = false;
            } elseif (!validate_captcha('hcaptcha', $data['h-captcha-response'])) {
                $result['error_message'] = Registry::load('strings')->invalid_captcha;
                $result['error_variables'][] = 'captcha';
                $noerror = false;
            }
        }
    }
}

if ($noerror) {
    $columns = $join = $where = null;

    $columns = [
        'site_users.user_id', 'site_users.password', 'site_users.encrypt_type',
        'site_users.salt', 'site_users.site_role_id', 'site_roles.site_role_attribute',
        'site_users_settings.deactivated', 'site_users.approved',
    ];

    $join["[>]site_roles"] = ['site_users.site_role_id' => 'site_role_id'];
    $join["[>]site_users_settings"] = ["site_users.user_id" => "user_id"];

    $where["OR"] = ["site_users.username" => $data['user'], "site_users.email_address" => $data['user']];
    $where["LIMIT"] = 1;

    $site_user = DB::connect()->select('site_users', $join, $columns, $where);

    if (isset($site_user[0])) {
        $site_user = $site_user[0];
        $hashed_password = null;
        $columns = $join = $where = null;

        if ($site_user['site_role_attribute'] === 'unverified_users') {
            $result = array();
            $result['success'] = false;
            $result['error_message'] = Registry::load('strings')->confirm_your_email_address;
            $result['error_key'] = 'confirm_your_email_address';
            $result['error_type'] = "warning";
        } elseif ((int)$site_user['approved'] === 0) {
            $result = array();
            $result['success'] = false;
            $result['error_message'] = Registry::load('strings')->wait_for_profile_approval;
            $result['error_key'] = 'wait_for_profile_approval';
            $result['error_type'] = "warning";
        } elseif ($site_user['site_role_attribute'] === 'banned_users') {
            $result = array();
            $result['success'] = false;
            $result['error_message'] = Registry::load('strings')->account_banned;
            $result['error_key'] = 'account_banned';
            $result['error_type'] = "message";
        } else {
            $columns = ['login_session_id', 'user_id', 'access_code', 'time_stamp', 'failed_attempts', 'last_access'];

            $where["AND"] = [
                "login_sessions.user_id" => $site_user['user_id'],
                "login_sessions.initiated_ip_address" => Registry::load('current_user')->ip_address,
                "login_sessions.status" => 0,
            ];

            $where["LIMIT"] = 1;

            $login_session = DB::connect()->select('login_sessions', $columns, $where);

            if (!$force_request) {
                $encrypt_type = filter_var($site_user['encrypt_type'], FILTER_SANITIZE_NUMBER_INT);

                if ($site_user['encrypt_type'] === 'php_password_hash') {
                    if (password_verify($data['password'], $site_user['password'])) {
                        $valid_login = true;
                    }
                } elseif ($site_user['encrypt_type'] === 'blowfish') {
                    $password_hashed = crypt($data['password'], "$2y$12$".$site_user['salt']);
                    if (hash_equals($site_user['password'], $password_hashed)) {
                        $valid_login = true;
                    }
                } elseif ((int)$encrypt_type === 11 || $site_user['encrypt_type'] === 'md5') {
                    $hashed_password = md5($data['password']);
                } elseif ((int)$encrypt_type === 1) {
                    $hashed_password = md5(md5(sha1(sha1(md5($site_user['salt'] . $data['password'])))));
                } elseif ((int)$encrypt_type === 2) {
                    $hashed_password = hash('ripemd128', (md5(md5($site_user['salt'] . $data['password']))));
                } elseif ((int)$encrypt_type === 3) {
                    $hashed_password = hash('sha256', (crc32($site_user['salt'] . $data['password'])));
                } elseif ((int)$encrypt_type === 4) {
                    $hashed_password = hash('ripemd128', (crc32(crc32($site_user['salt'] . $data['password']))));
                } elseif ((int)$encrypt_type === 5) {
                    $hashed_password = hash('md4', (md5($site_user['salt'] . $data['password'])));
                } elseif ((int)$encrypt_type === 6) {
                    $hashed_password = md5(hash('sha256', (md5($site_user['salt'] . $data['password']))));
                } elseif ((int)$encrypt_type === 7) {
                    $hashed_password = hash('ripemd128', (sha1($site_user['salt'] . $data['password'])));
                } elseif ((int)$encrypt_type === 8) {
                    $hashed_password = hash('md2', (md5(sha1($site_user['salt'] . $data['password']))));
                } elseif ((int)$encrypt_type === 9) {
                    $hashed_password = sha1(crc32(sha1(crc32(md5($site_user['salt'] . $data['password'])))));
                } elseif ((int)$encrypt_type === 10) {
                    $hashed_password = md5(md5(sha1(sha1(crc32($site_user['salt'] . $data['password'])))));
                } elseif ((int)$encrypt_type === 12) {
                    $hashed_password = md5($site_user['salt'] . $data['password']);
                }

                if (!empty($hashed_password) && $hashed_password === $site_user['password']) {
                    $valid_login = true;
                }
            } else {
                $valid_login = true;
            }

            if (isset($login_session[0])) {
                $time_now = new DateTime($current_timestamp);
                $last_acess_time = new DateTime($login_session[0]['last_access']);
                $time_difference = $time_now->diff($last_acess_time);
                $time_difference = $time_difference->h + ($time_difference->days * 24);

                if ($time_difference >= 1) {
                    $update = ['status' => 2];
                    $where = ['login_sessions.login_session_id' => $login_session[0]['login_session_id']];
                    DB::connect()->update('login_sessions', $update, $where);

                    unset($login_session[0]);
                }
            }

            if (isset($login_session[0])) {
                $login_session = $login_session[0];

                if (isset(Registry::load('settings')->maximum_login_attempts) && !empty(Registry::load('settings')->maximum_login_attempts)) {
                    if ($login_session['failed_attempts'] >= Registry::load('settings')->maximum_login_attempts) {
                        $result = array();
                        $result['success'] = false;
                        $result['error_message'] = Registry::load('strings')->device_blocked;
                        return $result;
                    }
                }

                if ($valid_login) {
                    $update = ['status' => 1];
                    $where = ['login_sessions.login_session_id' => $login_session['login_session_id']];

                    DB::connect()->update('login_sessions', $update, $where);
                } else {
                    $login_session['failed_attempts'] = $login_session['failed_attempts']+1;
                    $update = ['failed_attempts' => $login_session['failed_attempts'], 'last_access' => $current_timestamp];
                    $where = ['login_sessions.login_session_id' => $login_session['login_session_id']];

                    DB::connect()->update('login_sessions', $update, $where);
                }
            } else {
                $login_session = array();
                $login_session['user_id'] = $site_user['user_id'];
                $login_session['initiated_ip_address'] = Registry::load('current_user')->ip_address;
                $login_session['access_code'] = random_string(['length' => 20]);
                $login_session['time_stamp'] = strtotime($current_timestamp);
                $login_session['last_access'] = $current_timestamp;
                $login_session['failed_attempts'] = 1;
                $login_session['status'] = 0;

                if ($login_as_another_user) {
                    $login_session['log_device'] = 0;
                }

                if ($valid_login) {
                    $login_session['failed_attempts'] = 0;
                    $login_session['status'] = 1;
                }

                DB::connect()->insert('login_sessions', $login_session);

                if (!DB::connect()->error) {
                    $login_session['login_session_id'] = DB::connect()->id();
                }
            }
        }
    } else {
        $result['error_message'] = Registry::load('strings')->account_not_found;
        $result['error_key'] = 'account_not_found';
    }
    if ($valid_login) {
        if ($login_as_another_user) {
            $session_id = $session_time_stamp = $access_code = null;

            if (isset($_COOKIE["login_session_id"]) && isset($_COOKIE["session_time_stamp"]) && isset($_COOKIE["access_code"])) {
                $session_id = $_COOKIE["login_session_id"];
                $session_time_stamp = $_COOKIE["session_time_stamp"];
                $access_code = $_COOKIE["access_code"];
            }


            $update_status = [
                'online_status' => 0,
                "last_seen_on" => Registry::load('current_user')->time_stamp,
                "updated_on" => Registry::load('current_user')->time_stamp,
            ];
            DB::connect()->update('site_users', $update_status, ['user_id' => Registry::load('current_user')->id]);

            $update = ['status' => 2];
            $where = [
                'login_sessions.login_session_id' => $session_id,
                'login_sessions.time_stamp' => $session_time_stamp,
                'login_sessions.access_code' => $access_code,
            ];

            DB::connect()->update('login_sessions', $update, $where);
        } else {
            if (isset($site_user['deactivated']) && !empty($site_user['deactivated'])) {
                DB::connect()->update('site_users_settings', ['deactivated' => 0], ['user_id' => $site_user['user_id']]);
            }

            $device_log['login_session_id'] = $login_session['login_session_id'];
            $device_log['ip_address'] = Registry::load('current_user')->ip_address;
            $device_log['user_agent'] = Registry::load('current_user')->user_agent;
            $device_log['user_id'] = $site_user['user_id'];
            $device_log['created_on'] = $current_timestamp;

            DB::connect()->insert('site_users_device_logs', $device_log);

            $log_session = [
                "last_seen_on" => Registry::load('current_user')->time_stamp,
                "last_login_session" => Registry::load('current_user')->time_stamp,
            ];
            DB::connect()->update('site_users', $log_session, ['user_id' => $site_user['user_id']]);
        }

        if (isset($data['remember_me']) && !empty($data['remember_me'])) {
            $cookie_time = time() + (86400 * 60);
        } elseif (isset(Registry::load('settings')->login_cookie_validity) && !empty(Registry::load('settings')->login_cookie_validity)) {
            $cookie_time = time() + (86400 * Registry::load('settings')->login_cookie_validity);
        } else {
            $cookie_time = time() + (86400);
        }

        add_cookie('login_session_id', $login_session['login_session_id'], $cookie_time);
        add_cookie('access_code', $login_session['access_code'], $cookie_time);
        add_cookie('session_time_stamp', $login_session['time_stamp'], $cookie_time);

        add_cookie('current_language_id', 0);
        add_cookie('current_color_scheme', 0);

        $result = array();
        $result['success'] = true;

        $result['auto_login_url'] = Registry::load('config')->site_url.'entry/';
        $result['auto_login_url'] .= '?login_session_id='.$login_session['login_session_id'];
        $result['auto_login_url'] .= '&access_code='.$login_session['access_code'];
        $result['auto_login_url'] .= '&session_time_stamp='.$login_session['time_stamp'];

        if (!$login_as_another_user) {
            $result['todo'] = 'register_login_session';
            $result['login_session_id'] = $login_session['login_session_id'];
            $result['access_code'] = $login_session['access_code'];
            $result['session_time_stamp'] = $login_session['time_stamp'];

            if (isset($site_user['deactivated']) && !empty($site_user['deactivated'])) {
                $result['alert'] = Registry::load('strings')->account_reactivated;
            }

            if (isset($data['redirect'])) {
                $result['redirect'] = htmlspecialchars($data['redirect']);
            } else {
                $result['redirect'] = Registry::load('config')->site_url;
            }
        } else {
            $result['todo'] = 'refresh';
        }
    }
}