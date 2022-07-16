<?php

$form = array();
$form['loaded'] = new stdClass();
$form['loaded']->title = Registry::load('strings')->temporary_ban_from_group;
$form['loaded']->button = Registry::load('strings')->ban;

$super_privileges = false;

if (role(['permissions' => ['groups' => 'super_privileges']])) {
    $super_privileges = true;
}

$form['fields'] = new stdClass();

$form['fields']->process = [
    "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "update"
];

$form['fields']->update = [
    "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "group_user_role"
];

if (isset($load['group_id']) && isset($load['user_id'])) {

    $load["group_id"] = filter_var($load["group_id"], FILTER_SANITIZE_NUMBER_INT);
    $load["user_id"] = filter_var($load["user_id"], FILTER_SANITIZE_NUMBER_INT);

    if (!empty($load['group_id']) && !empty($load['user_id'])) {

        $columns = $where = $join = null;

        $columns = [
            'groups.name(group_name)', 'site_users.display_name(member_name)',
            'group_roles.string_constant(group_role)',
            'current_user.group_role_id(current_user_group_role_id)'
        ];
        $where['AND'] = ['group_members.group_id' => $load['group_id'], 'group_members.user_id' => $load['user_id']];

        $join["[>]groups"] = ['group_members.group_id' => 'group_id'];
        $join["[>]site_users"] = ['group_members.user_id' => 'user_id'];
        $join["[>]group_roles"] = ['group_members.group_role_id' => 'group_role_id'];
        $join["[>]group_members(current_user)"] = ["groups.group_id" => "group_id", "AND" => ["current_user.user_id" => Registry::load('current_user')->id]];

        $group_info = DB::connect()->select('group_members', $join, $columns, $where);

        if (isset($group_info[0])) {

            $group_info = $group_info[0];

            if ($super_privileges || isset($group_info['current_user_group_role_id']) && !empty($group_info['current_user_group_role_id'])) {

                if (!$super_privileges && !role(['permissions' => ['group_members' => 'ban_users_from_group'], 'group_role_id' => $group_info['current_user_group_role_id']])) {
                    return false;
                }

                $form['fields']->group_id = [
                    "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load['group_id']
                ];

                $form['fields']->group_name = [
                    "title" => Registry::load('strings')->group_name, "tag" => 'input', "type" => 'text',
                    "attributes" => ['disabled' => 'disabled'], "class" => 'field', "value" => $group_info['group_name'],
                ];

                $form['fields']->temporary_ban_user_id = [
                    "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load['user_id']
                ];

                $form['fields']->member_name = [
                    "title" => Registry::load('strings')->member, "tag" => 'input', "type" => 'text',
                    "attributes" => ['disabled' => 'disabled'], "class" => 'field', "value" => $group_info['member_name'],
                ];

                $current_role = $group_info['group_role'];
                $current_role = Registry::load('strings')->$current_role;

                $form['fields']->current_role = [
                    "title" => Registry::load('strings')->current_role, "tag" => 'input', "type" => 'text',
                    "attributes" => ['disabled' => 'disabled'], "class" => 'field', "value" => $current_role,
                ];

                $tomorrow = date("Y-m-d", strtotime("+1 day"));

                $form['fields']->ban_till = [
                    "title" => Registry::load('strings')->banned_till, "tag" => 'input', "type" => 'date',
                    "class" => 'field', "attributes" => ['min' => $tomorrow]
                ];
            }
        }
    }
}



?>