<?php

$todo = 'add';
$group_id = 0;
$no_error = false;
$user_id = Registry::load('current_user')->id;
$super_privileges = false;

$form['loaded'] = new stdClass();
$form['fields'] = new stdClass();

if (role(['permissions' => ['groups' => 'invite_users']])) {

    if (role(['permissions' => ['groups' => 'super_privileges']])) {
        $super_privileges = true;
    }

    if (isset($load['group_id'])) {
        $load["group_id"] = filter_var($load["group_id"], FILTER_SANITIZE_NUMBER_INT);
        if (!empty($load['group_id'])) {
            $group_id = $load["group_id"];
        }
    }

    if (!empty($group_id)) {

        $columns = $where = null;
        $columns = [
            'groups.group_id', 'groups.name', 'groups.secret_group', 'groups.password',
            'groups.slug', 'group_members.group_role_id', 'group_members.group_member_id',
            'group_roles.group_role_attribute',
        ];

        $join["[>]group_members"] = ["groups.group_id" => "group_id", "AND" => ["user_id" => $user_id]];
        $join["[>]group_roles"] = ["group_members.group_role_id" => "group_role_id"];

        $where["groups.group_id"] = $group_id;
        $where["LIMIT"] = 1;

        $group = DB::connect()->select('groups', $join, $columns, $where);

        if (!isset($group[0])) {
            return false;
        } else {
            $group = $group[0];
        }


        if ($super_privileges || isset($group['group_role_id']) && !empty($group['group_role_id'])) {
            if ($super_privileges || $group['group_role_attribute'] === 'administrators') {
                $no_error = true;
            } else if (empty($group['password']) && empty($group['secret_group']) && $group['group_role_attribute'] !== 'banned_users') {
                $no_error = true;
            }
        }

        if ($no_error) {
            $invite_link = Registry::load('config')->site_url;

            if (isset($group['slug']) && !empty($group['slug'])) {
                $invite_link .= $group['slug'].'/join/referrer_id/'.$user_id;
            } else {
                $invite_link .= 'group/'.$group['group_id'].'/join/referrer_id/'.$user_id;
            }

            $form['fields']->group_id = [
                "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $group_id
            ];

            $form['loaded']->title = Registry::load('strings')->invite_users;
            $form['loaded']->button = Registry::load('strings')->invite;

            $form['fields']->process = [
                "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "add"
            ];

            $form['fields']->add = [
                "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "group_invitations"
            ];

            $form['fields']->group_name = [
                "title" => Registry::load('strings')->group_name, "tag" => 'input', "type" => "disabled",
                "class" => 'field', "value" => $group['name']
            ];
            $form['fields']->group_name['attributes']['disabled'] = 'disabled';



            if (empty($group['secret_group']) && empty($group['password'])) {
                $form['fields']->invite_link = [
                    "title" => Registry::load('strings')->invite_link, "tag" => 'input', "type" => 'text', "class" => 'field',
                    "value" => $invite_link,
                ];
            }

            $form['fields']->email_username = [
                "title" => Registry::load('strings')->email_username, "tag" => 'textarea', "class" => 'field',
                "placeholder" => Registry::load('strings')->line_break_delimiter,
            ];
            $form['fields']->email_username['attributes']['rows'] = 8;
        }
    }

}
?>