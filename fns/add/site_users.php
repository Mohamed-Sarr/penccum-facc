<?php

if (!isset($private_data['exclude_filters_function'])) {
    include 'fns/filters/load.php';
}

if (!isset($private_data['exclude_files_function'])) {
    include 'fns/files/load.php';
}


$noerror = true;
$disabled = 0;
$strict_mode = true;
$email_login_link = false;
$required_fields = ['full_name', 'username', 'email_address', 'password'];
$validate_custom_fields = true;
$create_user = $created_by_admin = $require_email_verification = false;

if (!$force_request) {
    $required_fields[] = 'confirm_password';
}

if ($force_request || role(['permissions' => ['site_users' => 'create_user']])) {
    $create_user = true;
    $created_by_admin = true;
    $validate_custom_fields = false;
}

if (!$force_request) {
    if (!Registry::load('current_user')->logged_in && Registry::load('settings')->user_registration === 'enable') {
        $create_user = true;
    } elseif (!Registry::load('current_user')->logged_in && Registry::load('settings')->user_registration !== 'enable') {
        $result = array();
        $result['success'] = false;
        $result['error_message'] = Registry::load('strings')->went_wrong;
        $result['error_key'] = 'something_went_wrong';
        $result['error_variables'] = [];
    }
}

if ($create_user) {
    $result = array();
    $result['success'] = false;
    $result['error_message'] = Registry::load('strings')->invalid_value;
    $result['error_key'] = 'invalid_value';
    $result['error_variables'] = [];


    $columns = $where = null;
    $columns = ['custom_fields.field_id', 'custom_fields.string_constant(field_name)', 'custom_fields.field_type', 'custom_fields.required'];
    $where['AND'] = ['custom_fields.field_category' => 'profile', 'custom_fields.disabled' => 0];

    if (!$force_request) {
        if (isset($data['signup_page'])) {
            $where['AND']['custom_fields.show_on_signup'] = 1;
        }
    }

    $where["ORDER"] = ["custom_fields.field_id" => "ASC"];
    $custom_fields = DB::connect()->select('custom_fields', $columns, $where);

    if (Registry::load('settings')->non_latin_usernames === 'enable') {
        $strict_mode = false;
    }

    if (!Registry::load('current_user')->logged_in && isset($data['signup_page'])) {
        if (Registry::load('settings')->hide_email_address_field_in_registration_page === 'yes') {
            if (!isset($data['email_address'])) {
                $data['email_address'] = 'user_'.strtotime("now").'@'.random_string(['length' => 10]).'.user';
            }
        }

        if (Registry::load('settings')->hide_name_field_in_registration_page === 'yes') {
            if (!isset($data['full_name'])) {
                if (isset($data['username']) && !empty($data['username'])) {
                    $data['full_name'] = $data['username'];
                } else {
                    $data['full_name'] = 'user_'.strtotime("now");
                }
            }
        }

        if (Registry::load('settings')->hide_username_field_in_registration_page === 'yes') {
            if (!isset($data['username'])) {
                $data['username'] = 'user_'.strtotime("now").'_'.random_string(['length' => 5]);
            }
        }
    }

    if (isset($data['username'])) {
        $data['username'] = sanitize_username($data['username'], $strict_mode);
    }

    if (isset($data['email_address']) && !filter_var($data['email_address'], FILTER_VALIDATE_EMAIL)) {
        $data['email_address'] = null;
        $result['error_message'] = Registry::load('strings')->invalid_email_address;
        $result['error_key'] = 'invalid_email_address';
    }

    if ($validate_custom_fields) {
        foreach ($custom_fields as $custom_field) {
            if ((int)$custom_field['required'] === 1) {
                $required_fields[] = $custom_field['field_name'];
                $custom_field_name = $custom_field['field_name'];

                if (isset($data[$custom_field_name]) && !empty($data[$custom_field_name])) {
                    if ($custom_field['field_type'] === 'number') {
                        $data[$custom_field_name] = filter_var($data[$custom_field_name], FILTER_SANITIZE_NUMBER_INT);
                        if (empty($data[$custom_field_name])) {
                            $data[$custom_field_name] = '';
                        }
                    } elseif ($custom_field['field_type'] === 'link') {
                        $data[$custom_field_name] = filter_var($data[$custom_field_name], FILTER_SANITIZE_URL);
                        if (empty($data[$custom_field_name]) || !filter_var($data[$custom_field_name], FILTER_VALIDATE_URL)) {
                            $data[$custom_field_name] = '';
                        }
                    }
                }
            }
        }
    }

    foreach ($required_fields as $required_field) {
        if (!isset($data[$required_field]) || empty(trim($data[$required_field]))) {
            $result['error_variables'][] = [$required_field];
            $noerror = false;
        }
    }

    if (!$force_request) {
        if (isset($data['username']) && !empty($data['username'])) {
            $user_name_length = mb_strlen($data['username']);
            if (!empty(Registry::load('settings')->minimum_username_length)) {
                if ($user_name_length < Registry::load('settings')->minimum_username_length) {
                    $data['username'] = null;
                    $result['error_message'] = Registry::load('strings')->requires_minimum_username_length;
                    $result['error_message'] .= ' ['.Registry::load('settings')->minimum_username_length.']';
                    $result['error_key'] = 'requires_minimum_username_length';
                    $result['error_variables'][] = 'username';
                    $noerror = false;
                }
            }
            if (!empty(Registry::load('settings')->maximum_username_length)) {
                if ($user_name_length > Registry::load('settings')->maximum_username_length) {
                    $data['username'] = null;
                    $result['error_message'] = Registry::load('strings')->exceeds_username_length;
                    $result['error_message'] .= ' ['.Registry::load('settings')->maximum_username_length.']';
                    $result['error_key'] = 'exceeds_username_length';
                    $result['error_variables'][] = 'username';
                    $noerror = false;
                }
            }
        }
    }

    if (!$force_request) {
        if (isset($data['password']) && !empty($data['password'])) {
            if (!isset($data['confirm_password']) || isset($data['confirm_password']) && $data['password'] !== $data['confirm_password']) {
                $result['error_variables'] = ['password', 'confirm_password'];
                $result['error_message'] = Registry::load('strings')->password_doesnt_match;
                $result['error_key'] = 'password_doesnt_match';
                $noerror = false;
            }
        }

        if (!Registry::load('current_user')->logged_in) {
            $required_fields[] = 'terms_agreement';
            if (!isset($data['terms_agreement']) || $data['terms_agreement'] !== 'agreed') {
                $result['error_variables'] = ['terms_agreement'];
                $result['error_message'] = Registry::load('strings')->requires_consent;
                $result['error_key'] = 'terms_agreement';
                $noerror = false;
            }

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

    if (isset($data['email_address']) && !empty($data['email_address'])) {
        $data['email_address'] = htmlspecialchars(trim($data['email_address']), ENT_QUOTES, 'UTF-8');
        $email_exists = DB::connect()->select('site_users', 'site_users.user_id', ['site_users.email_address' => $data['email_address']]);

        if (isset($email_exists[0])) {
            $result['error_variables'] = ['email_address'];
            $result['error_message'] = Registry::load('strings')->email_exists;
            $result['error_key'] = 'email_exists';
            $noerror = false;
        }
    }

    if (isset($data['username']) && !empty($data['username'])) {
        if (username_exists($data['username'])) {
            $result['error_variables'] = ['username'];
            $result['error_message'] = Registry::load('strings')->username_exists;
            $result['error_key'] = 'username_exists';
            $noerror = false;
        }
    }


    if ($noerror) {
        $data['full_name'] = trim($data['full_name']);
        $data['full_name'] = trim($data['full_name']);
        $data['full_name'] = preg_replace('|\s+|', ' ', $data['full_name']);
        $data['full_name'] = htmlspecialchars($data['full_name'], ENT_QUOTES, 'UTF-8');

        if ($created_by_admin && isset($data['disabled']) && $data['disabled'] === 'yes') {
            $disabled = 1;
        }

        $site_role = 1;

        if (isset($private_data['guest_user']) && $private_data['guest_user']) {
            $guest_user_role = DB::connect()->select('site_roles', ['site_roles.site_role_id'], ["site_roles.site_role_attribute" => 'guest_users']);
            if (isset($guest_user_role[0])) {
                $site_role = $guest_user_role[0]['site_role_id'];
            }
        } elseif (!$force_request && !$created_by_admin && Registry::load('settings')->user_email_verification === 'enable') {
            $require_email_verification = true;
            $unverified_user_role = DB::connect()->select('site_roles', ['site_roles.site_role_id'], ["site_roles.site_role_attribute" => 'unverified_users']);
            if (isset($unverified_user_role[0])) {
                $site_role = $unverified_user_role[0]['site_role_id'];
            }
        } else {
            $default_site_role = DB::connect()->select('site_roles', ['site_roles.site_role_id'], ["site_roles.site_role_attribute" => 'default_site_role']);
            if (isset($default_site_role[0])) {
                $site_role = $default_site_role[0]['site_role_id'];
            }
        }

        if ($created_by_admin && isset($data['site_role']) && !empty($data['site_role'])) {
            $check_site_role = DB::connect()->select('site_roles', ['site_roles.site_role_id'], ["site_roles.site_role_id" => $data['site_role']]);
            if (isset($check_site_role[0])) {
                $site_role = $data['site_role'];
            }
        }

        if ($created_by_admin && isset($data['site_role_attribute']) && !empty($data['site_role_attribute'])) {
            $check_site_role = DB::connect()->select('site_roles', ['site_roles.site_role_id'], ["site_roles.site_role_attribute" => $data['site_role_attribute']]);
            if (isset($check_site_role[0])) {
                $site_role = $check_site_role[0]['site_role_id'];
            }
        }


        if ($created_by_admin && isset($data['email_login_link']) && $data['email_login_link'] === 'yes') {
            $email_login_link = true;
        }


        $verification_code = random_string(['length' => 10]);
        $approved = 1;

        if (!$force_request && !$created_by_admin && Registry::load('settings')->new_user_approval === 'enable') {
            $approved = 0;
        }

        if ($force_request) {
            if (isset($data['requires_user_approval'])) {
                if ($data['requires_user_approval']) {
                    $approved = 0;
                } else {
                    $approved = 1;
                }
            }
        }

        $insert_data = [
            "display_name" => $data['full_name'],
            "username" => $data['username'],
            "email_address" => $data['email_address'],
            "password" => password_hash($data['password'], PASSWORD_BCRYPT),
            "encrypt_type" => 'php_password_hash',
            "salt" => '',
            "site_role_id" => $site_role,
            "approved" => $approved,
            "previous_site_role_id" => $site_role,
            "verification_code" => $verification_code,
            "created_on" => Registry::load('current_user')->time_stamp,
            "updated_on" => Registry::load('current_user')->time_stamp,
        ];

        if (!$created_by_admin && Registry::load('settings')->user_email_verification === 'enable' && $require_email_verification) {
            $insert_data["unverified_email_address"] = $data['email_address'];
        }

        if (isset($private_data['social_login_provider_id'])) {
            $private_data['social_login_provider_id'] = filter_var($private_data['social_login_provider_id'], FILTER_SANITIZE_NUMBER_INT);

            if (!empty($private_data['social_login_provider_id'])) {
                $insert_data["social_login_provider_id"] = $private_data['social_login_provider_id'];
            }
        }




        DB::connect()->insert("site_users", $insert_data);

        if (!DB::connect()->error) {
            $user_id = DB::connect()->id();

            $disable_private_messages = 0;

            if ($created_by_admin && isset($data['disable_private_messages']) && $data['disable_private_messages'] === 'yes') {
                $disable_private_messages = 1;
            }

            $insert_data = [
                "user_id" => $user_id,
                "time_zone" => '',
                "disable_private_messages" => $disable_private_messages,
                "updated_on" => Registry::load('current_user')->time_stamp,
            ];

            if ($created_by_admin && isset($data['deactivate']) && $data['deactivate'] === 'yes') {
                $insert_data["deactivated"] = 1;
            } elseif (isset($data['deactivate']) && $data['deactivate'] === 'no') {
                $insert_data["deactivated"] = 0;
            }

            $check_array = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

            if (isset($data['timezone']) && $data['timezone'] === 'Default') {
                $insert_data["time_zone"] = 'default';
            } elseif (isset($data['timezone']) && in_array($data['timezone'], $check_array)) {
                $insert_data["time_zone"] = $data['timezone'];
            }

            $check_array = glob('assets/files/alerts/*');

            if ($created_by_admin && isset($data['notification']) && in_array($data['notification'], $check_array)) {
                $insert_data["notification_sound"] = $data['notification'];
            }

            DB::connect()->insert("site_users_settings", $insert_data);

            if ($created_by_admin && isset($_FILES['custom_background']['name']) && !empty($_FILES['custom_background']['name'])) {
                if (isImage($_FILES['custom_background']['tmp_name'])) {
                    foreach (glob("assets/files/site_users/backgrounds/".$user_id.Registry::load('config')->file_seperator."*.*") as $oldimage) {
                        unlink($oldimage);
                    }

                    $extension = pathinfo($_FILES['custom_background']['name'])['extension'];
                    $filename = $user_id.Registry::load('config')->file_seperator.random_string(['length' => 6]).'.'.$extension;

                    if (files('upload', ['upload' => 'custom_background', 'folder' => 'site_users/backgrounds', 'saveas' => $filename])['result']) {
                        files('resize_img', ['resize' => 'site_users/backgrounds/'.$filename, 'width' => 1920, 'height' => 1080, 'crop' => false]);
                    }
                }
            }

            if ($created_by_admin && isset($_FILES['cover_pic']['name']) && !empty($_FILES['cover_pic']['name'])) {
                if (isImage($_FILES['cover_pic']['tmp_name'])) {
                    foreach (glob("assets/files/site_users/cover_pics/".$user_id.Registry::load('config')->file_seperator."*.*") as $oldimage) {
                        unlink($oldimage);
                    }

                    $extension = pathinfo($_FILES['cover_pic']['name'])['extension'];
                    $filename = $user_id.Registry::load('config')->file_seperator.random_string(['length' => 6]).'.'.$extension;

                    if (files('upload', ['upload' => 'cover_pic', 'folder' => 'site_users/cover_pics', 'saveas' => $filename])['result']) {
                        files('resize_img', ['resize' => 'site_users/cover_pics/'.$filename, 'width' => 400, 'height' => 400, 'crop' => true]);
                    }
                }
            }


            if ($created_by_admin && isset($_FILES['custom_avatar']['name']) && !empty($_FILES['custom_avatar']['name'])) {
                if (isImage($_FILES['custom_avatar']['tmp_name'])) {
                    foreach (glob("assets/files/site_users/profile_pics/".$user_id.Registry::load('config')->file_seperator."*.*") as $oldimage) {
                        unlink($oldimage);
                    }

                    $extension = pathinfo($_FILES['custom_avatar']['name'])['extension'];
                    $filename = $user_id.Registry::load('config')->file_seperator.random_string(['length' => 6]).'.'.$extension;

                    if (files('upload', ['upload' => 'custom_avatar', 'folder' => 'site_users/profile_pics', 'saveas' => $filename])['result']) {
                        files('resize_img', ['resize' => 'site_users/profile_pics/'.$filename, 'width' => 150, 'height' => 150, 'crop' => true]);
                    }
                }
            } elseif ($created_by_admin && isset($data['avatar'])) {
                $data['avatar'] = 'assets/files/avatars/'.sanitize_filename($data['avatar']);

                if (file_exists($data['avatar'])) {
                    foreach (glob("assets/files/site_users/profile_pics/".$user_id.Registry::load('config')->file_seperator."*.*") as $oldimage) {
                        unlink($oldimage);
                    }

                    $filename = 'assets/files/site_users/profile_pics/'.$user_id.Registry::load('config')->file_seperator.random_string(['length' => 6]).'.png';
                    files('copy', ['from' => $data['avatar'], 'to' => $filename, 'real_path' => true]);
                }
            } elseif ($created_by_admin && isset($data['avatarURL'])) {
                foreach (glob("assets/files/site_users/profile_pics/".$user_id.Registry::load('config')->file_seperator."*.*") as $oldimage) {
                    unlink($oldimage);
                }

                $avatar_file_name = $user_id.Registry::load('config')->file_seperator.random_string(['length' => 6]).'.png';
                $avatar_file = "assets/files/site_users/profile_pics/".$avatar_file_name;

                $curl_request = curl_init($data['avatarURL']);
                $save_avatar = fopen($avatar_file, 'wb');
                curl_setopt($curl_request, CURLOPT_FILE, $save_avatar);
                curl_setopt($curl_request, CURLOPT_HEADER, 0);
                curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($curl_request, CURLOPT_ENCODING, '');
                curl_exec($curl_request);
                curl_close($curl_request);
                fclose($save_avatar);

                $avatar_location = 'assets/files/site_users/profile_pics/'.$avatar_file_name;

                if (file_exists($avatar_location)) {
                    $avatar_content_type = mime_content_type($avatar_location);
                    if (strstr($avatar_content_type, 'image/')) {
                        files('resize_img', ['resize' => 'site_users/profile_pics/'.$avatar_file_name, 'width' => 150, 'height' => 150, 'crop' => true]);
                    } else {
                        unlink($avatar_location);
                    }
                }
            }

            foreach ($custom_fields as $custom_field) {
                $field_name = $custom_field['field_name'];
                $insert = false;

                if (isset($data[$field_name])) {
                    if ($custom_field['field_type'] === 'date') {
                        if (validate_date($data[$field_name], 'Y-m-d')) {
                            $insert = true;
                        }
                    } elseif ($custom_field['field_type'] === 'link') {
                        $data[$field_name] = filter_var($data[$field_name], FILTER_SANITIZE_URL);
                        if (!empty($data[$field_name]) && filter_var($data[$field_name], FILTER_VALIDATE_URL)) {
                            $insert = true;
                        }
                    } elseif ($custom_field['field_type'] === 'number') {
                        $data[$field_name] = filter_var($data[$field_name], FILTER_SANITIZE_NUMBER_INT);
                        if (!empty($data[$field_name])) {
                            $insert = true;
                        }
                    } elseif ($custom_field['field_type'] === 'dropdown') {
                        if (!empty($data[$field_name])) {
                            $dropdownoptions = $field_name.'_options';
                            if (isset(Registry::load('strings')->$dropdownoptions)) {
                                $field_options = json_decode(Registry::load('strings')->$dropdownoptions);
                                $find_index = $data[$field_name];
                                if (isset($field_options->$find_index)) {
                                    $insert = true;
                                }
                            }
                        }
                    } else {
                        $data[$field_name] = htmlspecialchars(trim($data[$field_name]), ENT_QUOTES, 'UTF-8');
                        $insert = true;
                    }

                    if ($insert) {
                        $insert_data = ['field_value' => $data[$field_name], 'updated_on' => Registry::load('current_user')->time_stamp];
                        $insert_data["field_id"] = $custom_field['field_id'];
                        $insert_data["user_id"] = $user_id;
                        DB::connect()->insert("custom_fields_values", $insert_data);
                    }
                }
            }

            if ($require_email_verification) {
                include('fns/mailer/load.php');

                $verification_link = Registry::load('config')->site_url.'entry/verify_email_address/'.$user_id.'/'.$verification_code;

                $mail = array();
                $mail['email_addresses'] = $data['email_address'];
                $mail['category'] = 'verification';
                $mail['user_id'] = $user_id;
                $mail['parameters'] = ['link' => $verification_link];
                $mail['send_now'] = true;
                mailer('compose', $mail);
            } elseif ($email_login_link) {
                $current_timestamp = Registry::load('current_user')->time_stamp;
                $access_token = random_string(['length' => 10]);
                $update = ['access_token' => $access_token, 'token_generated_on' => $current_timestamp];
                $where = ['site_users.user_id' => $user_id];
                DB::connect()->update('site_users', $update, $where);

                include('fns/mailer/load.php');

                $login_link = Registry::load('config')->site_url.'entry/access_token/'.$user_id.'/'.$access_token;

                $mail = array();
                $mail['email_addresses'] = $data['email_address'];
                $mail['category'] = 'login_link';
                $mail['user_id'] = $user_id;
                $mail['parameters'] = ['link' => $login_link];
                $mail['send_now'] = true;
                mailer('compose', $mail);
            }


            $default_group_role_id = DB::connect()->select('group_roles', ['group_roles.group_role_id'], ['group_roles.group_role_attribute' => 'default_group_role', 'LIMIT' => 1]);

            if (isset($default_group_role_id[0])) {
                $default_group_role_id = $default_group_role_id[0]['group_role_id'];
                $auto_join_groups = DB::connect()->select('groups', ['groups.group_id'], ['groups.auto_join_group' => 1]);
                $join_groups = array();

                foreach ($auto_join_groups as $auto_join_group) {
                    $join_group_id = $auto_join_group['group_id'];
                    $last_read_message_id = 0;

                    $last_group_message_id = DB::connect()->select(
                        'group_messages',
                        ['group_messages.group_message_id'],
                        [
                            'group_messages.group_id' => $join_group_id,
                            "ORDER" => ["group_messages.group_message_id" => "DESC"],
                            'LIMIT' => 1
                        ]
                    );

                    if (isset($last_group_message_id[0])) {
                        $last_read_message_id = $last_group_message_id[0]['group_message_id'];
                    }

                    $join_groups[] = [
                        'group_id' => $join_group_id,
                        'user_id' => $user_id,
                        'last_read_message_id' => $last_read_message_id,
                        'group_role_id' => $default_group_role_id,
                        "previous_group_role_id" => $default_group_role_id,
                        "joined_on" => Registry::load('current_user')->time_stamp,
                        "updated_on" => Registry::load('current_user')->time_stamp,
                    ];
                }

                if (!empty($join_groups)) {
                    DB::connect()->insert('group_members', $join_groups);
                }
            }


            $result = array();
            $result['success'] = true;

            if (!Registry::load('current_user')->logged_in || isset($private_data['auto_login']) && $private_data['auto_login']) {
                if (isset($data['signup_page']) || isset($private_data['auto_login']) && $private_data['auto_login']) {
                    $login_session = [
                        'add' => 'login_session',
                        'user' => $data['username'],
                        'return' => true
                    ];

                    if (isset($data['redirect'])) {
                        $login_session['redirect'] = $data['redirect'];
                    }
                    $result = add($login_session, ['force_request' => true]);
                    $result['reset_form'] = true;
                } else {
                    $result['todo'] = 'refresh';
                }
            } else {
                if ((int)$user_id === (int)Registry::load('current_user')->id) {
                    $result['todo'] = 'refresh';
                } else {
                    $result['todo'] = 'reload';
                    $result['reload'] = 'site_users';
                }
            }
        } else {
            $result['error_message'] = Registry::load('strings')->went_wrong;
            $result['error_key'] = 'something_went_wrong';
        }
    }
}