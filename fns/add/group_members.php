<?php

$result = array();
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->went_wrong;
$result['error_key'] = 'something_went_wrong';

$current_user_id = $user_id = Registry::load('current_user')->id;
$group_id = 0;
$referrer_user_id = 0;
$super_privileges = false;
$group_join_limit = 0;

if (role(['permissions' => ['groups' => 'super_privileges']])) {
    $super_privileges = true;
}

$group_join_limit = role(['find' => 'group_join_limit']);

if (empty($group_join_limit)) {
    $group_join_limit = 0;
}

if (isset($data['group_id'])) {
    $group_id = filter_var($data["group_id"], FILTER_SANITIZE_NUMBER_INT);
}

if ($force_request || role(['permissions' => ['groups' => 'add_site_members']])) {
    if (isset($data['user_id'])) {
        $user_id = filter_var($data["user_id"], FILTER_SANITIZE_NUMBER_INT);
    }
} else {
    if (!role(['permissions' => ['groups' => 'join_group']])) {
        return false;
    }
}

if ($force_request) {
    if (isset($data['user'])) {
        $columns = $join = $where = null;

        $columns = ['site_users.user_id'];
        $where["OR"] = ["site_users.username" => $data['user'], "site_users.email_address" => $data['user']];
        $where["LIMIT"] = 1;

        $site_user = DB::connect()->select('site_users', $columns, $where);

        if (isset($site_user[0])) {
            $user_id = $site_user[0]['user_id'];
        } else {
            $result = array();
            $result['success'] = false;
            $result['error_message'] = Registry::load('strings')->account_not_found;
            $result['error_key'] = 'account_not_found';
            $result['error_variables'] = [];
            return;
        }
    }

    if (isset($data['group'])) {
        $columns = $join = $where = null;

        $columns = ['groups.group_id'];
        $where["OR"] = ["groups.group_id" => $data['group'], "groups.slug" => $data['group']];
        $where["LIMIT"] = 1;

        $find_group = DB::connect()->select('groups', $columns, $where);

        if (isset($find_group[0])) {
            $group_id = $find_group[0]['group_id'];
        } else {
            $result = array();
            $result['success'] = false;
            $result['error_message'] = 'Group Not Found';
            $result['error_key'] = 'group_not_found';
            $result['error_variables'] = [];
            return;
        }
    }
}


if (!$force_request && !$super_privileges) {
    $total_joined_groups = DB::connect()->count('group_members', ["group_members.user_id" => $user_id]);
    if ($total_joined_groups > $group_join_limit) {
        $result = array();
        $result['success'] = false;
        $result['error_message'] = Registry::load('strings')->exceeded_group_join_limit;
        $result['error_key'] = 'exceeded_group_join_limit';

        return false;
    }
}

if (!empty($group_id)) {
    $columns = $join = $where = null;
    $columns = [
        'groups.secret_group', 'groups.password', 'currentuser.group_role_id',
        'joinuser.group_member_id', 'group_roles.group_role_attribute',
    ];

    $join["[>]group_members(joinuser)"] = ["groups.group_id" => "group_id", "AND" => ["joinuser.user_id" => $user_id]];
    $join["[>]group_members(currentuser)"] = ["groups.group_id" => "group_id", "AND" => ["currentuser.user_id" => $current_user_id]];
    $join["[>]group_roles"] = ["currentuser.group_role_id" => "group_role_id"];

    $where["groups.group_id"] = $group_id;
    $where["LIMIT"] = 1;

    $group = DB::connect()->select('groups', $join, $columns, $where);

    if (!isset($group[0])) {
        $group_id = 0;
    } else {
        $group = $group[0];

        if (!$force_request && role(['permissions' => ['groups' => 'add_site_members']])) {
            if ($super_privileges || isset($group['group_role_id']) && !empty($group['group_role_id'])) {
                if ($super_privileges || $group['group_role_attribute'] === 'administrators') {
                    $force_request = true;
                } elseif (empty($group['password']) && empty($group['secret_group']) && $group['group_role_attribute'] !== 'banned_users') {
                    $force_request = true;
                }
            }
        }

        $columns = $join = $where = null;
        $columns = ['group_roles.group_role_id'];

        if ($force_request && isset($private_data['administrator']) && $private_data['administrator']) {
            $where["group_roles.group_role_attribute"] = 'administrators';
        } elseif ($force_request && isset($data['group_role']) && !empty($data['group_role'])) {
            $where["group_roles.group_role_id"] = $data['group_role'];
        } else {
            $where["group_roles.group_role_attribute"] = 'default_group_role';
        }

        $where["LIMIT"] = 1;

        $group_role_id = DB::connect()->select('group_roles', $columns, $where);

        if (!isset($group_role_id[0])) {
            $group_id = 0;
            $result['error_key'] = 'invalid_group_role_id';
        } else {
            $group_role_id = $group_role_id[0]['group_role_id'];
        }
    }
}

if (!$force_request && isset($group['password']) && !empty($group['password'])) {
    if (isset($data['password'])) {
        if (!password_verify($data['password'], $group['password'])) {
            $group_id = 0;
            $result['error_message'] = Registry::load('strings')->invalid_group_password;
            $result['error_key'] = 'invalid_group_password';
            $result['error_variables'][] = 'password';
        }
    } else {
        $group_id = 0;
        $result['error_message'] = Registry::load('strings')->invalid_group_password;
        $result['error_key'] = 'invalid_group_password';
        $result['error_variables'][] = 'password';
    }
}

if (!empty($group_id) && !empty($user_id)) {
    if (!isset($group['group_member_id']) || empty($group['group_member_id'])) {
        if (isset($data['referrer_user_id'])) {
            $referrer_user_id = filter_var($data["referrer_user_id"], FILTER_SANITIZE_NUMBER_INT);
            if (!empty($referrer_user_id)) {
                if ((int)$referrer_user_id === (int)$user_id) {
                    $referrer_user_id = 0;
                }
            }
        }


        DB::connect()->insert("group_members", [
            "group_id" => $group_id,
            "user_id" => $user_id,
            "group_role_id" => $group_role_id,
            "referrer_user_id" => $referrer_user_id,
            "previous_group_role_id" => $group_role_id,
            "joined_on" => Registry::load('current_user')->time_stamp,
            "updated_on" => Registry::load('current_user')->time_stamp,
        ]);

        $total_members = DB::connect()->count("group_members", ["group_id" => $group_id]);

        $recent_message_id = DB::connect()->select("group_messages", ["group_messages.group_message_id"], [
            "group_messages.group_id" => $group_id, "ORDER" => ["group_messages.group_message_id" => "DESC"], "LIMIT" => 1
        ]);

        if (isset($recent_message_id[0])) {
            $recent_message_id = $recent_message_id[0]['group_message_id'];
        } else {
            $recent_message_id = 0;
        }

        DB::connect()->update("group_members", ["last_read_message_id" => $recent_message_id], ["group_id" => $group_id, "user_id" => $user_id]);

        DB::connect()->update("groups", ["total_members" => $total_members], ["group_id" => $group_id]);

        if (isset($private_data['owner']) && $private_data['administrator']) {
            if (isset(Registry::load('settings')->system_messages_groups->on_group_creation)) {
                $system_message = [
                    'message' => 'created_group',
                    'user_id' => $user_id
                ];
            }
        } elseif (isset(Registry::load('settings')->system_messages_groups->on_join_group_chat)) {
            $system_message = [
                'message' => 'joined_group',
                'user_id' => $user_id
            ];
        }

        if (isset($system_message) && !empty($system_message)) {
            $system_message = json_encode($system_message);

            DB::connect()->insert("group_messages", [
                "system_message" => 1,
                "original_message" => 'system_message',
                "filtered_message" => $system_message,
                "group_id" => $group_id,
                "user_id" => $user_id,
                "created_on" => Registry::load('current_user')->time_stamp,
                "updated_on" => Registry::load('current_user')->time_stamp,
            ]);

            DB::connect()->update("groups", ["updated_on" => Registry::load('current_user')->time_stamp], ['group_id' => $group_id]);
        }
    }

    $result = array();
    $result['success'] = true;
    if (isset($data['user_id'])) {
        $result = array();
        $result['success'] = true;
        $result['todo'] = 'reload';
        $result['reload'] = ['groups', 'non_group_members'];
    } else {
        $result = array();
        $result['success'] = true;

        if (!$api_request) {
            $result['todo'] = 'load_conversation';
            $result['identifier_type'] = 'group_id';
            $result['identifier'] = $group_id;
            $result['reload_aside'] = true;
        }
    }
}
