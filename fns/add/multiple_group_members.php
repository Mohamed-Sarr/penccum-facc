<?php

$result = array();
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->went_wrong;
$result['error_key'] = 'something_went_wrong';

$current_user_id = $user_id = Registry::load('current_user')->id;
$referrer_user_id = $current_user_id;
$group_id = 0;
$super_privileges = false;

if (role(['permissions' => ['groups' => 'super_privileges']]) && role(['permissions' => ['groups' => 'add_site_members']])) {

    if (isset($data['group_id'])) {
        $group_id = filter_var($data["group_id"], FILTER_SANITIZE_NUMBER_INT);
    }

    if (!is_array($data['user_id'])) {
        $data["user_id"] = filter_var($data["user_id"], FILTER_SANITIZE_NUMBER_INT);
        $user_ids[] = $data["user_id"];
    } else {
        $user_ids = array_filter($data["user_id"], 'ctype_digit');
    }

    if (!empty($group_id) && !empty($user_ids)) {

        $recent_message_id = DB::connect()->select("group_messages", ["group_messages.group_message_id"], [
            "group_messages.group_id" => $group_id, "ORDER" => ["group_messages.group_message_id" => "DESC"], "LIMIT" => 1
        ]);

        if (isset($recent_message_id[0])) {
            $recent_message_id = $recent_message_id[0]['group_message_id'];
        } else {
            $recent_message_id = 0;
        }


        $columns = $join = $where = null;
        $columns = ['group_roles.group_role_id'];
        $where["group_roles.group_role_attribute"] = 'default_group_role';
        $where["LIMIT"] = 1;

        $group_role_id = DB::connect()->select('group_roles', $columns, $where);

        if (!isset($group_role_id[0])) {
            $user_ids = array();
        } else {
            $group_role_id = $group_role_id[0]['group_role_id'];
        }

        $columns = $join = $where = null;
        $columns = ['group_members.user_id'];
        $where["group_members.group_id"] = $group_id;
        $where["group_members.user_id"] = $user_ids;

        $remove_user_ids = DB::connect()->select('group_members', $columns, $where);

        foreach ($remove_user_ids as $remove_user_id) {

            $remove_user_id = $remove_user_id['user_id'];
            $remove_index = array_search($remove_user_id, $user_ids);
            unset($user_ids[$remove_index]);
        }

        foreach ($user_ids as $user_id) {

            if ((int)$referrer_user_id === (int)$user_id) {
                $referrer_user_id = 0;
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


            DB::connect()->update("group_members", ["last_read_message_id" => $recent_message_id], ["group_id" => $group_id, "user_id" => $user_id]);

        }

        $total_members = DB::connect()->count("group_members", ["group_id" => $group_id]);
        DB::connect()->update("groups", ["total_members" => $total_members], ["group_id" => $group_id]);

        $result = array();
        $result['success'] = true;
        $result['todo'] = 'reload';
        $result['reload'] = ['groups', 'non_group_members'];
    }
}

?>