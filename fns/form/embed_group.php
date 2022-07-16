<?php

$todo = 'add';
$group_id = 0;
$no_error = false;
$user_id = Registry::load('current_user')->id;
$super_privileges = false;

$form['loaded'] = new stdClass();
$form['fields'] = new stdClass();

if (role(['permissions' => ['groups' => 'embed_group']])) {

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
            'group_roles.group_role_attribute', 'groups.secret_code'
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
            $group_url = Registry::load('config')->site_url;

            if (isset($group['slug']) && !empty($group['slug'])) {
                $group_url .= $group['slug'].'/';
            } else {
                $group_url .= 'group/'.$group['group_id'].'/';
            }
            
            if (isset($group['secret_group']) && !empty($group['secret_group'])) {
                $group_url .= $group['secret_code'].'/';
            }

            $form['fields']->group_id = [
                "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $group_id
            ];

            $form['loaded']->title = Registry::load('strings')->embed_group;

            $form['fields']->group_name = [
                "title" => Registry::load('strings')->group_name, "tag" => 'input', "type" => "disabled",
                "class" => 'field', "value" => $group['name']
            ];
            $form['fields']->group_name['attributes']['disabled'] = 'disabled';

            $form['fields']->group_url = [
                "title" => Registry::load('strings')->group_url, "tag" => 'input', "type" => 'text', "class" => 'field',
                "value" => $group_url,
            ];

            $form['fields']->group_url['attributes']['class'] = 'copy_to_clipboard';

            $embed_code = '<iframe width="410px" height="650px" allow="camera;microphone" ';
            $embed_code .= 'src="'.$group_url.'" frameborder=0 allowfullscreen></iframe>';

            $embed_code = htmlspecialchars($embed_code, ENT_QUOTES, 'UTF-8');

            $form['fields']->embed_code = [
                "title" => Registry::load('strings')->embed_code, "tag" => 'textarea', "class" => 'field',
                "value" => $embed_code,
            ];
            $form['fields']->embed_code['attributes']['rows'] = 8;
            $form['fields']->embed_code['attributes']['class'] = 'copy_to_clipboard';
        }
    }

}
?>