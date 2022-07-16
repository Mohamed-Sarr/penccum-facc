<?php

if (role(['permissions' => ['site_users' => ['view_site_users', 'view_online_users']], 'condition' => 'OR'])) {

    $permission = array();


    if (role(['permissions' => ['site_users' => 'unban_users_from_site']])) {
        $permission['unban_users'] = true;
        $permission['ban_unban_users'] = true;
    }

    if (role(['permissions' => ['site_users' => 'ban_users_from_site']])) {
        $permission['ban_users'] = true;
        $permission['ban_unban_users'] = true;
    }

    if (role(['permissions' => ['site_users' => 'edit_users']])) {
        $permission['edit_users'] = true;
    }

    if (role(['permissions' => ['site_users' => 'approve_users']])) {
        $permission['approve_users'] = true;
    }

    $columns = [
        'site_users.user_id', 'site_users.display_name', 'site_users.email_address', 'site_users.approved',
        'site_roles.site_role_attribute', 'blacklist.ignore', 'blacklist.block', 'site_users.username',
        'site_users.last_seen_on', 'site_users.site_role_id', 'site_users_settings.deactivated',
        'site_users.online_status'
    ];

    if (isset($private_data["online"])) {
        $columns[] = 'site_users.site_role_id';

        unset($data["sortby"]);

        if (!role(['permissions' => ['site_users' => 'view_online_users']])) {
            return false;
        }

    } else if (!role(['permissions' => ['site_users' => 'view_site_users']])) {
        return false;
    }

    $join["[>]site_roles"] = ["site_users.site_role_id" => "site_role_id"];
    $join["[>]site_users_settings"] = ["site_users.user_id" => "user_id"];
    $join["[>]site_users_blacklist(blacklist)"] = ["site_users.user_id" => "blacklisted_user_id", "AND" => ["blacklist.user_id" => Registry::load('current_user')->id]];
    $join["[>]site_users_blacklist(blocked)"] = ["site_users.user_id" => "user_id", "AND" => ["blocked.blacklisted_user_id" => Registry::load('current_user')->id]];

    if (!empty($data["offset"])) {
        $data["offset"] = array_map('intval', explode(',', $data["offset"]));
        $where["site_users.user_id[!]"] = $data["offset"];
    }

    if ($data["filter"] === 'banned' && isset($permission['ban_unban_users'])) {
        $where["site_roles.site_role_attribute"] = 'banned_users';
    } else if ($data["filter"] === 'guest_users') {
        $where["site_roles.site_role_attribute"] = 'guest_users';
    } else if ($data["filter"] === 'unverified_users' && isset($permission['edit_users'])) {
        $where["site_roles.site_role_attribute"] = 'unverified_users';
    } else if ($data["filter"] === 'pending_approval' && isset($permission['approve_users'])) {
        $where["site_users.approved"] = 0;
    } else {

        if (!isset($permission['ban_unban_users'])) {
            $where["site_roles.site_role_attribute[!]"] = 'banned_users';
        }

        if (!isset($permission['approve_users'])) {
            $where["site_users.approved"] = 1;
        }
    }

    if (isset($private_data["online"])) {

        $hide_current_user = false;

        $where["site_users.online_status[!]"] = 0;

        if ($hide_current_user) {
            $where["site_users.user_id[!]"] = Registry::load('current_user')->id;
        }

        if (!role(['permissions' => ['site_users' => 'view_invisible_users']])) {
            $where["site_users_settings.offline_mode[!]"] = 1;
        }

    } else {

        if (isset($data["site_role_id"])) {

            $data["site_role_id"] = filter_var($data["site_role_id"], FILTER_SANITIZE_NUMBER_INT);

            if (!empty($data["site_role_id"])) {
                $where["site_users.site_role_id"] = $data["site_role_id"];
            }
        }
    }

    if (!empty($data["search"])) {
        $where["AND #search_query"]["OR"] = [
            "site_users.display_name[~]" => $data["search"],
            "site_users.username[~]" => $data["search"],
            "site_users.email_address[~]" => $data["search"],
        ];
    }

    if (!isset($permission['edit_users'])) {
        $where["AND"]["OR #blocked"] = ["blocked.block" => NULL, "blocked.block(blocked)" => 0];
    }

    $where["LIMIT"] = Registry::load('settings')->records_per_call;

    if (isset($private_data["online"])) {
        $where["ORDER"] = ["site_users.online_status" => "ASC", "site_users.last_login_session" => "DESC"];
    } else {
        if ($data["sortby"] === 'name_asc') {
            $where["ORDER"] = ["site_users.display_name" => "ASC"];
        } else if ($data["sortby"] === 'name_desc') {
            $where["ORDER"] = ["site_users.display_name" => "DESC"];
        } else if ($data["sortby"] === 'last_visit_asc') {
            $where["ORDER"] = ["site_users.last_seen_on" => "ASC"];
        } else if ($data["sortby"] === 'last_visit_desc') {
            $where["ORDER"] = ["site_users.last_seen_on" => "DESC"];
        } else {
            $where["ORDER"] = ["site_users.user_id" => "DESC"];
        }
    }

    $site_users = DB::connect()->select('site_users', $join, $columns, $where);

    $i = 1;


    $output = array();
    $output['loaded'] = new stdClass();
    $output['loaded']->title = Registry::load('strings')->users;
    $output['loaded']->offset = array();

    if (!empty($data["offset"])) {
        $output['loaded']->offset = $data["offset"];
    }

    if ($data["filter"] === 'pending_approval') {
        $output['loaded']->title = Registry::load('strings')->pending_approval;
    }

    if (!isset($private_data["online"])) {


        if (role(['permissions' => ['site_users' => 'create_user']])) {
            $output['todo'] = new stdClass();
            $output['todo']->class = 'load_form';
            $output['todo']->title = Registry::load('strings')->create_user;
            $output['todo']->attributes['form'] = 'site_users';
        }


        if (role(['permissions' => ['site_users' => 'delete_users']])) {

            $output['multiple_select'] = new stdClass();
            $output['multiple_select']->title = Registry::load('strings')->delete;
            $output['multiple_select']->attributes['class'] = 'ask_confirmation';
            $output['multiple_select']->attributes['data-remove'] = 'site_users';
            $output['multiple_select']->attributes['multi_select'] = 'user_id';
            $output['multiple_select']->attributes['submit_button'] = Registry::load('strings')->yes;
            $output['multiple_select']->attributes['cancel_button'] = Registry::load('strings')->no;
            $output['multiple_select']->attributes['confirmation'] = Registry::load('strings')->confirm_action;
        }

        $output['sortby'][1] = new stdClass();
        $output['sortby'][1]->sortby = Registry::load('strings')->sort_by_default;
        $output['sortby'][1]->class = 'load_aside';
        $output['sortby'][1]->attributes['load'] = 'site_users';

        if (isset($data["site_role_id"]) && !empty($data["site_role_id"])) {
            $output['sortby'][1]->attributes['data-site_role_id'] = $data["site_role_id"];
        }

        $output['sortby'][2] = new stdClass();
        $output['sortby'][2]->sortby = Registry::load('strings')->name;
        $output['sortby'][2]->class = 'load_aside sort_asc';
        $output['sortby'][2]->attributes['load'] = 'site_users';
        $output['sortby'][2]->attributes['sort'] = 'name_asc';

        if (isset($data["site_role_id"]) && !empty($data["site_role_id"])) {
            $output['sortby'][2]->attributes['data-site_role_id'] = $data["site_role_id"];
        }

        $output['sortby'][3] = new stdClass();
        $output['sortby'][3]->sortby = Registry::load('strings')->name;
        $output['sortby'][3]->class = 'load_aside sort_desc';
        $output['sortby'][3]->attributes['load'] = 'site_users';
        $output['sortby'][3]->attributes['sort'] = 'name_desc';

        if (isset($data["site_role_id"]) && !empty($data["site_role_id"])) {
            $output['sortby'][3]->attributes['data-site_role_id'] = $data["site_role_id"];
        }

        if (!isset($private_data["online"])) {
            if (isset($permission['edit_users'])) {
                $output['sortby'][4] = new stdClass();
                $output['sortby'][4]->sortby = Registry::load('strings')->last_visit;
                $output['sortby'][4]->class = 'load_aside sort_asc';
                $output['sortby'][4]->attributes['load'] = 'site_users';
                $output['sortby'][4]->attributes['sort'] = 'last_visit_asc';

                if (isset($data["site_role_id"]) && !empty($data["site_role_id"])) {
                    $output['sortby'][4]->attributes['data-site_role_id'] = $data["site_role_id"];
                }

                $output['sortby'][5] = new stdClass();
                $output['sortby'][5]->sortby = Registry::load('strings')->last_visit;
                $output['sortby'][5]->class = 'load_aside sort_desc';
                $output['sortby'][5]->attributes['load'] = 'site_users';
                $output['sortby'][5]->attributes['sort'] = 'last_visit_desc';

                if (isset($data["site_role_id"]) && !empty($data["site_role_id"])) {
                    $output['sortby'][5]->attributes['data-site_role_id'] = $data["site_role_id"];
                }
            }
        }
    } else {
        $output['loaded']->title = Registry::load('strings')->online;
    }

    foreach ($site_users as $user) {

        $output['loaded']->offset[] = $user['user_id'];

        $output['content'][$i] = new stdClass();
        $output['content'][$i]->image = get_image(['from' => 'site_users/profile_pics', 'search' => $user['user_id'], 'gravatar' => $user['email_address']]);
        $output['content'][$i]->title = $user['display_name'];
        $output['content'][$i]->identifier = $user['user_id'];
        $output['content'][$i]->icon = 0;
        $output['content'][$i]->unread = 0;

        $output['content'][$i]->class = "user";
        $output['content'][$i]->attributes = ['user_id' => $user['user_id']];

        if ($data["filter"] !== 'pending_approval') {
            $output['content'][$i]->class .= " get_info";
            $output['content'][$i]->attributes['stopPropagation'] = true;
        }


        if (isset($private_data["online"])) {

            if ((int)$user['online_status'] === 1) {
                $output['content'][$i]->online_status = 'online';
                $output['content'][$i]->subtitle = Registry::load('strings')->online;
            } else {
                $output['content'][$i]->online_status = 'idle';
                $output['content'][$i]->subtitle = Registry::load('strings')->idle;
            }

        } else {

            if (isset($permission['edit_users']) && $data["sortby"] === 'name_asc' || isset($permission['edit_users']) && $data["sortby"] === 'name_desc') {
                $output['content'][$i]->subtitle = $user['username'];
            } else if ($data["sortby"] === 'last_visit_desc' || $data["sortby"] === 'last_visit_asc') {
                if (!empty($user['last_seen_on'])) {

                    $last_login['date'] = $user['last_seen_on'];
                    $last_login['auto_format'] = true;
                    $last_login['include_time'] = true;
                    $last_login['timezone'] = Registry::load('current_user')->time_zone;
                    $last_login = get_date($last_login);

                    $output['content'][$i]->subtitle = $last_login['date'].' '.$last_login['time'];

                } else {
                    $output['content'][$i]->subtitle = Registry::load('strings')->data_unavailable;
                }
            } else {
                $rolename = 'site_role_'.$user['site_role_id'];
                $output['content'][$i]->subtitle = Registry::load('strings')->$rolename;
            }
        }

        if (!empty($user['deactivated'])) {
            $output['content'][$i]->subtitle = Registry::load('strings')->deactivated;
        }

        if (empty($user['approved']) && !isset($sort_by_name)) {
            $output['content'][$i]->subtitle = Registry::load('strings')->pending_approval;
        }


        $option_index = 1;

        if (isset($permission['edit_users'])) {
            $output['options'][$i][$option_index] = new stdClass();
            $output['options'][$i][$option_index]->option = Registry::load('strings')->edit_profile;
            $output['options'][$i][$option_index]->class = 'load_form';
            $output['options'][$i][$option_index]->attributes['form'] = 'site_users';
            $output['options'][$i][$option_index]->attributes['data-user_id'] = $user['user_id'];
            $option_index++;
        }

        if (role(['permissions' => ['private_conversations' => 'send_message']])) {
            if ((int)$user['user_id'] !== (int)Registry::load('current_user')->id) {
                $output['options'][$i][$option_index] = new stdClass();
                $output['options'][$i][$option_index]->option = Registry::load('strings')->message;
                $output['options'][$i][$option_index]->class = 'load_conversation force_request';
                $output['options'][$i][$option_index]->attributes['user_id'] = $user['user_id'];
                $option_index++;
            }
        }

        if ((int)$user['user_id'] !== (int)Registry::load('current_user')->id) {
            if (isset($permission['approve_users'])) {

                if (empty($user['approved'])) {
                    $output['options'][$i][$option_index] = new stdClass();
                    $output['options'][$i][$option_index]->option = Registry::load('strings')->approve;
                    $output['options'][$i][$option_index]->class = 'ask_confirmation';
                    $output['options'][$i][$option_index]->attributes['data-update'] = 'user_account_status';
                    $output['options'][$i][$option_index]->attributes['data-user_id'] = $user['user_id'];
                    $output['options'][$i][$option_index]->attributes['data-approve'] = true;
                    $output['options'][$i][$option_index]->attributes['confirmation'] = Registry::load('strings')->approve_user_confirmation;
                    $output['options'][$i][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
                    $output['options'][$i][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
                    $option_index++;
                } else {
                    $output['options'][$i][$option_index] = new stdClass();
                    $output['options'][$i][$option_index]->option = Registry::load('strings')->disapprove;
                    $output['options'][$i][$option_index]->class = 'ask_confirmation';
                    $output['options'][$i][$option_index]->attributes['data-update'] = 'user_account_status';
                    $output['options'][$i][$option_index]->attributes['data-user_id'] = $user['user_id'];
                    $output['options'][$i][$option_index]->attributes['data-disapprove'] = true;
                    $output['options'][$i][$option_index]->attributes['confirmation'] = Registry::load('strings')->disapprove_user_confirmation;
                    $output['options'][$i][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
                    $output['options'][$i][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
                    $option_index++;
                }
            }
        }

        $output['options'][$i][$option_index] = new stdClass();
        $output['options'][$i][$option_index]->option = Registry::load('strings')->profile;
        $output['options'][$i][$option_index]->class = 'get_info force_request';
        $output['options'][$i][$option_index]->attributes['user_id'] = $user['user_id'];
        $option_index++;

        if (role(['permissions' => ['site_users' => 'manage_user_access_logs']])) {
            $output['options'][$i][$option_index] = new stdClass();
            $output['options'][$i][$option_index]->option = Registry::load('strings')->access_logs;
            $output['options'][$i][$option_index]->class = 'load_aside';
            $output['options'][$i][$option_index]->attributes['load'] = 'access_logs';
            $output['options'][$i][$option_index]->attributes['data-user_id'] = $user['user_id'];
            $option_index++;
        }


        if (role(['permissions' => ['site_users' => 'block_users']])) {
            if ((int)$user['user_id'] !== (int)Registry::load('current_user')->id) {
                if (!isset($user['block']) || empty($user['block'])) {
                    $output['options'][$i][$option_index] = new stdClass();
                    $output['options'][$i][$option_index]->option = Registry::load('strings')->block_user;
                    $output['options'][$i][$option_index]->class = 'ask_confirmation';
                    $output['options'][$i][$option_index]->attributes['data-update'] = 'site_user_blacklist';
                    $output['options'][$i][$option_index]->attributes['data-block_user_id'] = $user['user_id'];
                    $output['options'][$i][$option_index]->attributes['confirmation'] = Registry::load('strings')->block_user_confirmation;
                    $output['options'][$i][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
                    $output['options'][$i][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
                    $option_index++;
                } else {
                    $output['options'][$i][$option_index] = new stdClass();
                    $output['options'][$i][$option_index]->option = Registry::load('strings')->unblock_user;
                    $output['options'][$i][$option_index]->class = 'ask_confirmation';
                    $output['options'][$i][$option_index]->attributes['data-update'] = 'site_user_blacklist';
                    $output['options'][$i][$option_index]->attributes['data-unblock_user_id'] = $user['user_id'];
                    $output['options'][$i][$option_index]->attributes['confirmation'] = Registry::load('strings')->unblock_user_confirmation;
                    $output['options'][$i][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
                    $output['options'][$i][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
                    $option_index++;
                }
            }
        }

        if (role(['permissions' => ['site_users' => 'ignore_users']])) {
            if ((int)$user['user_id'] !== (int)Registry::load('current_user')->id) {
                if (!isset($user['ignore']) || empty($user['ignore'])) {
                    $output['options'][$i][$option_index] = new stdClass();
                    $output['options'][$i][$option_index]->option = Registry::load('strings')->ignore_user;
                    $output['options'][$i][$option_index]->class = 'ask_confirmation';
                    $output['options'][$i][$option_index]->attributes['data-update'] = 'site_user_blacklist';
                    $output['options'][$i][$option_index]->attributes['data-ignore_user_id'] = $user['user_id'];
                    $output['options'][$i][$option_index]->attributes['confirmation'] = Registry::load('strings')->ignore_user_confirmation;
                    $output['options'][$i][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
                    $output['options'][$i][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
                    $option_index++;
                } else {
                    $output['options'][$i][$option_index] = new stdClass();
                    $output['options'][$i][$option_index]->option = Registry::load('strings')->unignore_user;
                    $output['options'][$i][$option_index]->class = 'ask_confirmation';
                    $output['options'][$i][$option_index]->attributes['data-update'] = 'site_user_blacklist';
                    $output['options'][$i][$option_index]->attributes['data-unignore_user_id'] = $user['user_id'];
                    $output['options'][$i][$option_index]->attributes['confirmation'] = Registry::load('strings')->unignore_user_confirmation;
                    $output['options'][$i][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
                    $output['options'][$i][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
                    $option_index++;
                }
            }
        }

        if (isset($permission['unban_users']) && $user['site_role_attribute'] === 'banned_users') {
            $output['options'][$i][$option_index] = new stdClass();
            $output['options'][$i][$option_index]->class = 'ask_confirmation';
            $output['options'][$i][$option_index]->attributes['data-update'] = 'site_user_role';
            $output['options'][$i][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
            $output['options'][$i][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
            $output['options'][$i][$option_index]->attributes['data-info_box'] = true;
            $output['options'][$i][$option_index]->option = Registry::load('strings')->unban_from_site;
            $output['options'][$i][$option_index]->attributes['data-unban_user_id'] = $user['user_id'];
            $output['options'][$i][$option_index]->attributes['confirmation'] = Registry::load('strings')->unban_from_site_confirmation;
            $option_index++;
        }

        if (isset($permission['ban_users']) && $user['site_role_attribute'] !== 'banned_users') {
            $output['options'][$i][$option_index] = new stdClass();
            $output['options'][$i][$option_index]->class = 'ask_confirmation';
            $output['options'][$i][$option_index]->attributes['data-update'] = 'site_user_role';
            $output['options'][$i][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
            $output['options'][$i][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
            $output['options'][$i][$option_index]->attributes['data-info_box'] = true;
            $output['options'][$i][$option_index]->option = Registry::load('strings')->ban_from_site;
            $output['options'][$i][$option_index]->attributes['data-ban_user_id'] = $user['user_id'];
            $output['options'][$i][$option_index]->attributes['confirmation'] = Registry::load('strings')->ban_from_site_confirmation;
            $option_index++;
        }

        if (role(['permissions' => ['site_users' => 'ban_ip_addresses']])) {
            $output['options'][$i][$option_index] = new stdClass();
            $output['options'][$i][$option_index]->option = Registry::load('strings')->ban_ip_addresses;
            $output['options'][$i][$option_index]->class = 'ask_confirmation';
            $output['options'][$i][$option_index]->attributes['data-update'] = 'firewall';
            $output['options'][$i][$option_index]->attributes['data-ban_user_id'] = $user['user_id'];
            $output['options'][$i][$option_index]->attributes['confirmation'] = Registry::load('strings')->ban_ip_addresses_confirmation;
            $output['options'][$i][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
            $output['options'][$i][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
            $option_index++;
        }

        if (role(['permissions' => ['site_users' => 'unban_ip_addresses']])) {
            $output['options'][$i][$option_index] = new stdClass();
            $output['options'][$i][$option_index]->option = Registry::load('strings')->unban_ip_addresses;
            $output['options'][$i][$option_index]->class = 'ask_confirmation';
            $output['options'][$i][$option_index]->attributes['data-update'] = 'firewall';
            $output['options'][$i][$option_index]->attributes['data-unban_user_id'] = $user['user_id'];
            $output['options'][$i][$option_index]->attributes['confirmation'] = Registry::load('strings')->unban_ip_addresses_confirmation;
            $output['options'][$i][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
            $output['options'][$i][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
            $option_index++;
        }

        if (role(['permissions' => ['complaints' => 'report']])) {
            $output['options'][$i][$option_index] = new stdClass();
            $output['options'][$i][$option_index]->option = Registry::load('strings')->report;
            $output['options'][$i][$option_index]->class = 'load_form';
            $output['options'][$i][$option_index]->attributes['form'] = 'complaint';
            $output['options'][$i][$option_index]->attributes['data-user_id'] = $user['user_id'];
            $option_index++;
        }

        if (role(['permissions' => ['site_users' => 'delete_users']])) {
            $output['options'][$i][$option_index] = new stdClass();
            $output['options'][$i][$option_index]->option = Registry::load('strings')->delete;
            $output['options'][$i][$option_index]->class = 'ask_confirmation';
            $output['options'][$i][$option_index]->attributes['data-info_box'] = true;
            $output['options'][$i][$option_index]->attributes['data-remove'] = 'site_users';
            $output['options'][$i][$option_index]->attributes['data-user_id'] = $user['user_id'];
            $output['options'][$i][$option_index]->attributes['confirmation'] = Registry::load('strings')->confirm_action;
            $output['options'][$i][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
            $output['options'][$i][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
            $option_index++;
        }

        if (role(['permissions' => ['site_users' => 'login_as_another_user']])) {
            $output['options'][$i][$option_index] = new stdClass();
            $output['options'][$i][$option_index]->option = Registry::load('strings')->login_as_user;
            $output['options'][$i][$option_index]->class = 'ask_confirmation';
            $output['options'][$i][$option_index]->attributes['confirmation'] = Registry::load('strings')->confirm_action;
            $output['options'][$i][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
            $output['options'][$i][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
            $output['options'][$i][$option_index]->attributes['data-add'] = 'login_session';
            $output['options'][$i][$option_index]->attributes['data-user'] = $user['username'];
            $option_index++;
        }

        if (role(['permissions' => ['badges' => 'assign']])) {
            $output['options'][$i][$option_index] = new stdClass();
            $output['options'][$i][$option_index]->option = Registry::load('strings')->assign_badges;
            $output['options'][$i][$option_index]->class = 'load_aside';
            $output['options'][$i][$option_index]->attributes['load'] = 'badges';
            $output['options'][$i][$option_index]->attributes['data-user_id'] = $user['user_id'];
            $option_index++;
        }

        $i++;
    }
}
?>