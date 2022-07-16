<?php

$user_id = Registry::load('current_user')->id;
$group_id = 0;
$columns = $join = $where = null;

$output = array();
$output['error'] = new stdClass();
$output['error']->title = Registry::load('strings')->not_found;
$output['error']->message = Registry::load('strings')->account_not_found;

if (isset($data['user_id'])) {
    $data['user_id'] = filter_var($data['user_id'], FILTER_SANITIZE_NUMBER_INT);
    if (!empty($data['user_id'])) {
        $user_id = $data['user_id'];
    }
}

if (isset($data['group_identifier'])) {
    $data['group_identifier'] = filter_var($data['group_identifier'], FILTER_SANITIZE_NUMBER_INT);
    if (!empty($data['group_identifier'])) {
        $group_id = $data['group_identifier'];
    }
}


$columns = [
    'site_users.user_id', 'site_users.display_name', 'site_users.username', 'site_users.site_role_id',
    'site_users_settings.deactivated', 'site_users.online_status', 'site_users_settings.offline_mode', 'site_users.approved',
    'site_users.email_address',
];


$columns[] = 'site_roles.site_role_attribute';
$join["[>]site_roles"] = ["site_users.site_role_id" => "site_role_id"];
$join["[>]site_users_settings"] = ["site_users.user_id" => "user_id"];


if ((int)$user_id !== (int)Registry::load('current_user')->id) {
    $columns[] = 'blacklist.ignore';
    $columns[] = 'blacklist.block';

    $join["[>]site_users_blacklist(blacklist)"] = ["site_users.user_id" => "blacklisted_user_id", "AND" => ["blacklist.user_id" => Registry::load('current_user')->id]];
    $join["[>]site_users_blacklist(blocked)"] = ["site_users.user_id" => "user_id", "AND" => ["blocked.blacklisted_user_id" => Registry::load('current_user')->id]];

    if (!role(['permissions' => ['site_users' => 'edit_users']])) {
        $where["AND"]["OR #blocked"] = ["blocked.block" => NULL, "blocked.block(blocked)" => 0];
    }

}


$where["site_users.user_id"] = $user_id;
$where["LIMIT"] = 1;


$user = DB::connect()->select('site_users', $join, $columns, $where);

if (isset($user[0])) {
    $user = $user[0];
    $option_index = 1;

    if (!role(['permissions' => ['site_users' => 'edit_users']])) {
        if (isset($user['deactivated']) && !empty($user['deactivated'])) {
            return;
        }
    }

    unset($output['error']);

    $output['loaded'] = new stdClass();
    $output['loaded']->heading = $user['display_name'];
    $output['loaded']->subheading = '@'.$user['username'];
    $output['loaded']->cover_pic = get_image(['from' => 'site_users/cover_pics', 'search' => $user['user_id']]);
    $output['loaded']->image = get_image(['from' => 'site_users/profile_pics', 'search' => $user['user_id']]);

    if (role(['permissions' => ['site_users' => 'view_online_users']])) {
        if ((int)$user['online_status'] === 1) {
            $output['loaded']->online_status = 'online';
        } else if ((int)$user['online_status'] === 2) {
            $output['loaded']->online_status = 'idle';
        }


        if (!role(['permissions' => ['site_users' => 'view_invisible_users']])) {
            if ((int)$user['offline_mode'] === 1) {
                unset($output['loaded']->online_status);
            }
        }
    }

    if ((int)$user_id !== (int)Registry::load('current_user')->id) {
        if (role(['permissions' => ['private_conversations' => 'send_message']])) {
            $output['button'] = new stdClass();
            $output['button']->class = 'button';
            $output['button']->title = Registry::load('strings')->message;
            $output['button']->attributes['class'] = 'load_conversation';
            $output['button']->attributes['user_id'] = $user['user_id'];
        }
    } else if (role(['permissions' => ['profile' => 'edit_profile']])) {
        $output['button'] = new stdClass();
        $output['button']->class = 'button';
        $output['button']->title = Registry::load('strings')->edit_profile;
        $output['button']->attributes['class'] = 'load_form';
        $output['button']->attributes['form'] = 'site_users';
        $output['button']->attributes['data-user_id'] = $user['user_id'];
    }

    if (!empty($group_id)) {


        $super_privileges = false;

        if (role(['permissions' => ['groups' => 'super_privileges']])) {
            $super_privileges = true;
        }

        $columns = $where = $join = null;
        $columns = [
            'groups.group_id', 'groups.secret_group', 'groups.password', 'group_members.group_role_id',
            'group_roles.group_role_attribute'
        ];

        $join["[>]group_members"] = ["groups.group_id" => "group_id", "AND" => ["user_id" => Registry::load('current_user')->id]];
        $join["[>]group_roles"] = ["group_members.group_role_id" => "group_role_id"];

        $where['groups.group_id'] = $group_id;

        $group_info = DB::connect()->select('groups', $join, $columns, $where);

        if (isset($group_info[0])) {

            $group_info = $group_info[0];

            $columns = $where = $join = null;

            $columns = ['group_roles.group_role_attribute', 'group_members.group_role_id'];
            $where['AND'] = ['group_members.group_id' => $group_id, 'group_members.user_id' => $user['user_id']];

            $join["[>]group_roles"] = ['group_members.group_role_id' => 'group_role_id'];

            $group_member_info = DB::connect()->select('group_members', $join, $columns, $where);

            if (isset($group_member_info[0])) {
                if ($super_privileges || isset($group_info['group_role_id']) && !empty($group_info['group_role_id'])) {

                    if ($super_privileges || role(['permissions' => ['group_members' => 'manage_user_roles'], 'group_role_id' => $group_info['group_role_id']])) {

                        $output['options'][$option_index] = new stdClass();
                        $output['options'][$option_index]->title = Registry::load('strings')->edit_group_role;
                        $output['options'][$option_index]->class = 'load_form';
                        $output['options'][$option_index]->attributes['form'] = 'group_user_role';
                        $output['options'][$option_index]->attributes['data-group_id'] = $group_id;
                        $output['options'][$option_index]->attributes['data-user_id'] = $user['user_id'];
                        $option_index++;
                    }

                    if ($group_member_info[0]['group_role_attribute'] !== 'banned_users') {
                        if ($super_privileges || role(['permissions' => ['group_members' => 'ban_users_from_group'], 'group_role_id' => $group_info['group_role_id']])) {

                            $output['options'][$option_index] = new stdClass();
                            $output['options'][$option_index]->title = Registry::load('strings')->temporary_ban_from_group;
                            $output['options'][$option_index]->class = 'load_form';
                            $output['options'][$option_index]->attributes['form'] = 'temporary_ban_from_group';
                            $output['options'][$option_index]->attributes['data-group_id'] = $group_id;
                            $output['options'][$option_index]->attributes['data-user_id'] = $user['user_id'];
                            $option_index++;

                            $output['options'][$option_index] = new stdClass();
                            $output['options'][$option_index]->title = Registry::load('strings')->ban_from_group;
                            $output['options'][$option_index]->class = 'ask_confirmation';
                            $output['options'][$option_index]->attributes['data-update'] = 'group_user_role';
                            $output['options'][$option_index]->attributes['data-group_id'] = $group_id;
                            $output['options'][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
                            $output['options'][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
                            $output['options'][$option_index]->attributes['column'] = 'fourth';
                            $output['options'][$option_index]->attributes['data-info_box'] = true;
                            $output['options'][$option_index]->attributes['data-ban_user_id'] = $user['user_id'];
                            $output['options'][$option_index]->attributes['confirmation'] = Registry::load('strings')->ban_from_group_confirmation;
                            $option_index++;
                        }
                    }

                    if ($group_member_info[0]['group_role_attribute'] === 'banned_users') {
                        if ($super_privileges || role(['permissions' => ['group_members' => 'unban_users_from_group'], 'group_role_id' => $group_info['group_role_id']])) {
                            $output['options'][$option_index] = new stdClass();
                            $output['options'][$option_index]->class = 'ask_confirmation';
                            $output['options'][$option_index]->attributes['data-update'] = 'group_user_role';
                            $output['options'][$option_index]->attributes['data-group_id'] = $group_id;
                            $output['options'][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
                            $output['options'][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
                            $output['options'][$option_index]->attributes['column'] = 'fourth';
                            $output['options'][$option_index]->attributes['data-info_box'] = true;
                            $output['options'][$option_index]->title = Registry::load('strings')->unban_from_group;
                            $output['options'][$option_index]->attributes['data-unban_user_id'] = $user['user_id'];
                            $output['options'][$option_index]->attributes['confirmation'] = Registry::load('strings')->unban_from_group_confirmation;
                            $option_index++;
                        }
                    }

                    if ($super_privileges || role(['permissions' => ['group_members' => 'remove_group_members'], 'group_role_id' => $group_info['group_role_id']])) {
                        $output['options'][$option_index] = new stdClass();
                        $output['options'][$option_index]->class = 'ask_confirmation';
                        $output['options'][$option_index]->attributes['data-remove'] = 'group_members';
                        $output['options'][$option_index]->attributes['data-group_id'] = $group_id;
                        $output['options'][$option_index]->attributes['data-user_id'] = $user['user_id'];
                        $output['options'][$option_index]->attributes['confirmation'] = Registry::load('strings')->remove_from_group_confirmation;
                        $output['options'][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
                        $output['options'][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
                        $output['options'][$option_index]->attributes['column'] = 'fourth';
                        $output['options'][$option_index]->attributes['data-info_box'] = true;
                        $output['options'][$option_index]->title = Registry::load('strings')->remove_from_group;
                        $option_index++;
                    }
                }
            }
        }
    }

    if ((int)$user_id !== (int)Registry::load('current_user')->id) {

        if (role(['permissions' => ['site_users' => 'edit_users']])) {
            $output['options'][$option_index] = new stdClass();
            $output['options'][$option_index]->title = Registry::load('strings')->edit_profile;
            $output['options'][$option_index]->class = 'load_form';
            $output['options'][$option_index]->attributes['form'] = 'site_users';
            $output['options'][$option_index]->attributes['data-user_id'] = $user['user_id'];
            $option_index++;
        }

        if (role(['permissions' => ['site_users' => 'approve_users']])) {

            if (empty($user['approved'])) {
                $output['options'][$option_index] = new stdClass();
                $output['options'][$option_index]->option = Registry::load('strings')->approve;
                $output['options'][$option_index]->class = 'ask_confirmation';
                $output['options'][$option_index]->attributes['data-update'] = 'user_account_status';
                $output['options'][$option_index]->attributes['data-user_id'] = $user['user_id'];
                $output['options'][$option_index]->attributes['data-approve'] = true;
                $output['options'][$option_index]->attributes['data-info_box'] = true;
                $output['options'][$option_index]->attributes['confirmation'] = Registry::load('strings')->approve_user_confirmation;
                $output['options'][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
                $output['options'][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
                $output['options'][$option_index]->attributes['column'] = 'fourth';
                $option_index++;
            } else {
                $output['options'][$option_index] = new stdClass();
                $output['options'][$option_index]->option = Registry::load('strings')->disapprove;
                $output['options'][$option_index]->class = 'ask_confirmation';
                $output['options'][$option_index]->attributes['data-update'] = 'user_account_status';
                $output['options'][$option_index]->attributes['data-user_id'] = $user['user_id'];
                $output['options'][$option_index]->attributes['data-disapprove'] = true;
                $output['options'][$option_index]->attributes['data-info_box'] = true;
                $output['options'][$option_index]->attributes['confirmation'] = Registry::load('strings')->disapprove_user_confirmation;
                $output['options'][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
                $output['options'][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
                $output['options'][$option_index]->attributes['column'] = 'fourth';
                $option_index++;
            }
        }

        if ($user['site_role_attribute'] !== 'administrators') {

            if (role(['permissions' => ['site_users' => 'ignore_users']])) {
                if (!isset($user['ignore']) || empty($user['ignore'])) {
                    $output['options'][$option_index] = new stdClass();
                    $output['options'][$option_index]->title = Registry::load('strings')->ignore_user;
                    $output['options'][$option_index]->class = 'ask_confirmation';
                    $output['options'][$option_index]->attributes['data-update'] = 'site_user_blacklist';
                    $output['options'][$option_index]->attributes['data-ignore_user_id'] = $user['user_id'];
                    $output['options'][$option_index]->attributes['confirmation'] = Registry::load('strings')->ignore_user_confirmation;
                    $output['options'][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
                    $output['options'][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
                    $output['options'][$option_index]->attributes['column'] = 'fourth';
                    $option_index++;
                } else {
                    $output['options'][$option_index] = new stdClass();
                    $output['options'][$option_index]->title = Registry::load('strings')->unignore_user;
                    $output['options'][$option_index]->class = 'ask_confirmation';
                    $output['options'][$option_index]->attributes['data-update'] = 'site_user_blacklist';
                    $output['options'][$option_index]->attributes['data-unignore_user_id'] = $user['user_id'];
                    $output['options'][$option_index]->attributes['confirmation'] = Registry::load('strings')->unignore_user_confirmation;
                    $output['options'][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
                    $output['options'][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
                    $output['options'][$option_index]->attributes['column'] = 'fourth';
                    $option_index++;
                }
            }

            if (role(['permissions' => ['site_users' => 'block_users']])) {
                if (!isset($user['block']) || empty($user['block'])) {
                    $output['options'][$option_index] = new stdClass();
                    $output['options'][$option_index]->title = Registry::load('strings')->block_user;
                    $output['options'][$option_index]->class = 'ask_confirmation';
                    $output['options'][$option_index]->attributes['data-update'] = 'site_user_blacklist';
                    $output['options'][$option_index]->attributes['data-block_user_id'] = $user['user_id'];
                    $output['options'][$option_index]->attributes['confirmation'] = Registry::load('strings')->block_user_confirmation;
                    $output['options'][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
                    $output['options'][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
                    $output['options'][$option_index]->attributes['column'] = 'fourth';
                    $option_index++;
                } else {
                    $output['options'][$option_index] = new stdClass();
                    $output['options'][$option_index]->title = Registry::load('strings')->unblock_user;
                    $output['options'][$option_index]->class = 'ask_confirmation';
                    $output['options'][$option_index]->attributes['data-update'] = 'site_user_blacklist';
                    $output['options'][$option_index]->attributes['data-unblock_user_id'] = $user['user_id'];
                    $output['options'][$option_index]->attributes['confirmation'] = Registry::load('strings')->unblock_user_confirmation;
                    $output['options'][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
                    $output['options'][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
                    $output['options'][$option_index]->attributes['column'] = 'fourth';
                    $option_index++;
                }
            }
        }

        if (role(['permissions' => ['site_users' => 'login_as_another_user']])) {
            $output['options'][$option_index] = new stdClass();
            $output['options'][$option_index]->option = Registry::load('strings')->login_as_user;
            $output['options'][$option_index]->class = 'ask_confirmation';
            $output['options'][$option_index]->attributes['confirmation'] = Registry::load('strings')->confirm_action;
            $output['options'][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
            $output['options'][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
            $output['options'][$option_index]->attributes['column'] = 'fourth';
            $output['options'][$option_index]->attributes['data-add'] = 'login_session';
            $output['options'][$option_index]->attributes['data-user'] = $user['username'];
            $option_index++;
        }


        if (role(['permissions' => ['complaints' => 'report']])) {
            $output['options'][$option_index] = new stdClass();
            $output['options'][$option_index]->title = Registry::load('strings')->report;
            $output['options'][$option_index]->class = 'load_form';
            $output['options'][$option_index]->attributes['form'] = 'complaint';
            $output['options'][$option_index]->attributes['data-user_id'] = $user['user_id'];
            $option_index++;
        }


        if (role(['permissions' => ['site_users' => 'unban_users_from_site']])) {
            if ($user['site_role_attribute'] === 'banned_users') {
                $output['options'][$option_index] = new stdClass();
                $output['options'][$option_index]->class = 'ask_confirmation';
                $output['options'][$option_index]->attributes['data-update'] = 'site_user_role';
                $output['options'][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
                $output['options'][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
                $output['options'][$option_index]->attributes['column'] = 'fourth';
                $output['options'][$option_index]->attributes['data-info_box'] = true;
                $output['options'][$option_index]->title = Registry::load('strings')->unban_from_site;
                $output['options'][$option_index]->attributes['data-unban_user_id'] = $user['user_id'];
                $output['options'][$option_index]->attributes['confirmation'] = Registry::load('strings')->unban_from_site_confirmation;
                $option_index++;
            }
        }

        if (role(['permissions' => ['site_users' => 'ban_users_from_site']])) {
            if ($user['site_role_attribute'] !== 'banned_users') {
                $output['options'][$option_index] = new stdClass();
                $output['options'][$option_index]->class = 'ask_confirmation';
                $output['options'][$option_index]->attributes['data-update'] = 'site_user_role';
                $output['options'][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
                $output['options'][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
                $output['options'][$option_index]->attributes['column'] = 'fourth';
                $output['options'][$option_index]->attributes['data-info_box'] = true;
                $output['options'][$option_index]->title = Registry::load('strings')->ban_from_site;
                $output['options'][$option_index]->attributes['data-ban_user_id'] = $user['user_id'];
                $output['options'][$option_index]->attributes['confirmation'] = Registry::load('strings')->ban_from_site_confirmation;
                $option_index++;
            }
        }

        if (role(['permissions' => ['site_users' => 'ban_ip_addresses']])) {
            $output['options'][$option_index] = new stdClass();
            $output['options'][$option_index]->title = Registry::load('strings')->ban_ip_addresses;
            $output['options'][$option_index]->class = 'ask_confirmation';
            $output['options'][$option_index]->attributes['data-update'] = 'firewall';
            $output['options'][$option_index]->attributes['data-ban_user_id'] = $user['user_id'];
            $output['options'][$option_index]->attributes['confirmation'] = Registry::load('strings')->ban_ip_addresses_confirmation;
            $output['options'][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
            $output['options'][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
            $output['options'][$option_index]->attributes['column'] = 'fourth';
            $option_index++;
        }

        if (role(['permissions' => ['site_users' => 'unban_ip_addresses']])) {
            $output['options'][$option_index] = new stdClass();
            $output['options'][$option_index]->title = Registry::load('strings')->unban_ip_addresses;
            $output['options'][$option_index]->class = 'ask_confirmation';
            $output['options'][$option_index]->attributes['data-update'] = 'firewall';
            $output['options'][$option_index]->attributes['data-unban_user_id'] = $user['user_id'];
            $output['options'][$option_index]->attributes['confirmation'] = Registry::load('strings')->unban_ip_addresses_confirmation;
            $output['options'][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
            $output['options'][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
            $output['options'][$option_index]->attributes['column'] = 'fourth';
            $option_index++;
        }

        if (role(['permissions' => ['site_users' => 'delete_users']])) {
            $output['options'][$option_index] = new stdClass();
            $output['options'][$option_index]->title = Registry::load('strings')->delete;
            $output['options'][$option_index]->class = 'ask_confirmation';
            $output['options'][$option_index]->attributes['data-info_box'] = true;
            $output['options'][$option_index]->attributes['data-remove'] = 'site_users';
            $output['options'][$option_index]->attributes['data-user_id'] = $user['user_id'];
            $output['options'][$option_index]->attributes['confirmation'] = Registry::load('strings')->confirm_action;
            $output['options'][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
            $output['options'][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
            $output['options'][$option_index]->attributes['column'] = 'fourth';
            $option_index++;
        }
    }

    if (role(['permissions' => ['badges' => 'assign']])) {
        $output['options'][$option_index] = new stdClass();
        $output['options'][$option_index]->option = Registry::load('strings')->assign_badges;
        $output['options'][$option_index]->class = 'load_aside';
        $output['options'][$option_index]->attributes['load'] = 'badges';
        $output['options'][$option_index]->attributes['data-user_id'] = $user['user_id'];
        $option_index++;
    }

    if (role(['permissions' => ['site_users' => 'manage_user_access_logs']])) {
        $output['options'][$option_index] = new stdClass();
        $output['options'][$option_index]->title = Registry::load('strings')->access_logs;
        $output['options'][$option_index]->class = 'load_aside';
        $output['options'][$option_index]->attributes['load'] = 'access_logs';
        $output['options'][$option_index]->attributes['data-user_id'] = $user['user_id'];
        $option_index++;
    }

    $columns = $where = $join = null;
    $columns = ['custom_fields.string_constant(field_name)', 'custom_fields.field_type', 'custom_fields.required', 'custom_fields_values.field_value'];
    $join["[>]custom_fields_values"] = ["custom_fields.field_id" => "field_id", "AND" => ["user_id" => $user['user_id']]];

    $where['AND #first_query'] = ['custom_fields.field_category' => 'profile', 'custom_fields.disabled' => 0];

    if (!role(['permissions' => ['site_users' => 'edit_users']])) {
        $where['AND #second_query'] = ['custom_fields.show_on_info_page' => 1];
    }

    $where["ORDER"] = ["custom_fields.field_id" => "ASC"];

    $custom_fields = DB::connect()->select('custom_fields', $join, $columns, $where);

    $i = 4;
    $show_country_badge = false;

    foreach ($custom_fields as $custom_field) {
        $field_name = $custom_field['field_name'];

        if (isset($custom_field['field_value']) && !empty($custom_field['field_value'])) {

            $output['content'][$i] = new stdClass();

            if ($custom_field['field_name'] === 'custom_field_6') {
                $show_country_badge = true;
                $user['flag'] = $custom_field['field_value'];
            }

            if ($custom_field['field_name'] === 'custom_field_1') {

                $output['loaded']->subheading = $custom_field['field_value'];

                if (role(['permissions' => ['site_users' => 'edit_users']])) {
                    $output['content'][$i]->field['title'] = Registry::load('strings')->username;
                    $output['content'][$i]->field['value'] = '@'.$user['username'];
                }

            } else {
                $output['content'][$i]->field['title'] = Registry::load('strings')->$field_name;

                if ($custom_field['field_type'] === 'dropdown') {
                    $dropdownoptions = $field_name.'_options';

                    if (isset(Registry::load('strings')->$dropdownoptions)) {

                        $field_options = json_decode(Registry::load('strings')->$dropdownoptions);
                        if (!empty($field_options)) {
                            $find = $custom_field['field_value'];
                            if (isset($field_options->$find)) {
                                $output['content'][$i]->field['value'] = $field_options->$find;
                            }
                        }

                    }
                } else if ($custom_field['field_type'] === 'date') {

                    if (Registry::load('settings')->dateformat === 'mdy_format') {
                        $output['content'][$i]->field['value'] = date("M-d-Y", strtotime($custom_field['field_value']));
                    } else if (Registry::load('settings')->dateformat === 'ymd_format') {
                        $output['content'][$i]->field['value'] = date("Y-M-d", strtotime($custom_field['field_value']));
                    } else {
                        $output['content'][$i]->field['value'] = date("d-M-Y", strtotime($custom_field['field_value']));
                    }
                } else if ($custom_field['field_type'] === 'link') {

                    $field_value = $custom_field['field_value'];

                    if (mb_strlen($field_value) > 50) {
                        $field_value = parse_url($field_value);
                        $field_value = $field_value['scheme']."://".$field_value['host'];
                    }

                    $custom_field['field_value'] = '<a href="'.$custom_field['field_value'].'" rel="nofollow noreferrer noopener" target="_blank">'.$field_value.'</a>';
                    $output['content'][$i]->field['value'] = $custom_field['field_value'];
                } else {
                    $output['content'][$i]->field['value'] = $custom_field['field_value'];
                }
            }
            $i++;
        }
    }

    $badges = array();
    $rolename = 'site_role_'.$user['site_role_id'];

    $badges[0]['title'] = Registry::load('strings')->$rolename;
    $badges[0]['image'] = get_image(['from' => 'site_roles', 'search' => $user['site_role_id']]);

    if ($show_country_badge) {
        if (isset($user['flag']) && !empty($user['flag'])) {
            if (isset(Registry::load('strings')->custom_field_6_options)) {

                $flag = $user['flag'];
                $countries = json_decode(Registry::load('strings')->custom_field_6_options);

                if (!empty($countries)) {
                    if (isset($countries->$flag)) {
                        $country_flag = 'assets/files/flags/'.mb_strtolower($flag).'.png';
                        if (file_exists($country_flag)) {
                            $badges[1]['title'] = $countries->$flag;
                            $badges[1]['image'] = Registry::load('config')->site_url.$country_flag;
                        }
                    }
                }

            }
        }
    }

    if (isset($group_member_info[0])) {
        if (isset($group_member_info[0]['group_role_id']) && !empty($group_member_info[0]['group_role_id'])) {
            $group_role_name = 'group_role_'.$group_member_info[0]['group_role_id'];
            $badges[2]['title'] = Registry::load('strings')->$group_role_name;
            $badges[2]['image'] = get_image(['from' => 'group_roles', 'search' => $group_member_info[0]['group_role_id']]);
        }
    }

    $columns = $join = $where = null;
    $columns = [
        'badges.string_constant', 'badges_assigned.badge_id',
    ];

    $join["[>]badges"] = ["badges_assigned.badge_id" => "badge_id"];

    $where["badges_assigned.user_id"] = $user['user_id'];
    $where["badges.disabled"] = 0;
    $where["badges.badge_category"] = 'profile';

    $user_badges = DB::connect()->select('badges_assigned', $join, $columns, $where);
    $badge_index = 3;

    foreach ($user_badges as $user_badge) {
        $badge_string_constant = $user_badge['string_constant'];
        $badges[$badge_index]['title'] = Registry::load('strings')->$badge_string_constant;
        $badges[$badge_index]['image'] = get_image(['from' => 'badges', 'search' => $user_badge['badge_id']]);
        $badge_index++;
    }

    $output['content'][1] = new stdClass();
    $output['content'][1]->field['title'] = Registry::load('strings')->badges;
    $output['content'][1]->field['images'] = $badges;


    if (role(['permissions' => ['site_users' => 'edit_users']])) {
        $output['content'][3] = new stdClass();
        $output['content'][3]->field['title'] = Registry::load('strings')->email_address;
        $output['content'][3]->field['value'] = $user['email_address'];
    }

}


?>