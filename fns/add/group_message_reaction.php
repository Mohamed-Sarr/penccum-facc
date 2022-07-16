<?php

$result = array();
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->went_wrong;
$result['error_key'] = 'something_went_wrong';
$no_error = false;
$reaction_id = 0;
$super_privileges = false;

if (role(['permissions' => ['groups' => 'super_privileges']])) {
    $super_privileges = true;
}

if (role(['permissions' => ['groups' => 'react_messages']])) {
    $user_id = Registry::load('current_user')->id;
    $group_message_id = 0;

    $reactions = [
        'like' => 1, 'love' => 2, 'haha' => 3,
        'wow' => 4, 'sad' => 5, 'angry' => 6
    ];


    if (isset($data['group_message_id'])) {
        $group_message_id = filter_var($data["group_message_id"], FILTER_SANITIZE_NUMBER_INT);
    }

    if (isset($data['group_message_id'])) {
        $group_message_id = filter_var($data["group_message_id"], FILTER_SANITIZE_NUMBER_INT);
    }

    if (isset($data['reaction'])) {
        $user_reaction = preg_replace("/[^a-zA-Z0-9_]+/", "", $data["reaction"]);

        if (!empty($user_reaction) && isset($reactions[$user_reaction])) {
            $reaction_id = $reactions[$user_reaction];
        }
    }

    if (!empty($user_id) && !empty($group_message_id) && !empty($reaction_id)) {
        $no_error = true;
    }

    if ($no_error) {
        $columns = $join = $where = null;
        $columns = [
            'group_messages.group_id', 'group_messages_reactions.reaction_id',
            'group_messages_reactions.group_message_reaction_id', 'group_members.group_role_id',
            'group_roles.group_role_attribute'
        ];

        $join["[>]group_members"] = ["group_messages.group_id" => "group_id", "AND" => ["group_members.user_id" => $user_id]];
        $join["[>]group_roles"] = ["group_members.group_role_id" => "group_role_id"];
        $join["[>]group_messages_reactions"] = ["group_messages.group_message_id" => "group_message_id", "AND" => ["group_messages_reactions.user_id" => $user_id]];

        $where["group_messages.group_message_id"] = $group_message_id;
        $where["LIMIT"] = 1;

        $group_message = DB::connect()->select('group_messages', $join, $columns, $where);

        if (isset($group_message[0])) {

            $group_message = $group_message[0];
            $todo = 'add_reaction';

            if ($super_privileges || isset($group_message['group_role_id']) && !empty($group_message['group_role_id']) && $group_message['group_role_attribute'] !== 'banned_users') {

                if ($super_privileges || role(['permissions' => ['messages' => 'react_messages'], 'group_role_id' => $group_message['group_role_id']])) {

                    if (isset($group_message['reaction_id']) && !empty($group_message['reaction_id'])) {
                        $todo = 'update_reaction';
                        if ((int)$group_message['reaction_id'] === (int)$reaction_id) {
                            $todo = 'remove_reaction';
                        }
                    }

                    if ($todo === 'add_reaction') {
                        DB::connect()->insert("group_messages_reactions", [
                            "user_id" => $user_id,
                            "group_message_id" => $group_message_id,
                            "reaction_id" => $reaction_id,
                            "updated_on" => Registry::load('current_user')->time_stamp,
                        ]);
                    } else if ($todo === 'update_reaction') {
                        DB::connect()->update("group_messages_reactions", [
                            "reaction_id" => $reaction_id,
                            "updated_on" => Registry::load('current_user')->time_stamp,
                        ], [
                            "group_message_reaction_id" => $group_message['group_message_reaction_id'],
                            "user_id" => $user_id
                        ]);
                    } else if ($todo === 'remove_reaction') {
                        $user_reaction = null;
                        DB::connect()->delete("group_messages_reactions", [
                            "group_message_reaction_id" => $group_message['group_message_reaction_id'],
                            "user_id" => $user_id
                        ]);
                    }

                    $all_reactions = DB::connect()->select("group_messages_reactions", ["reaction_id,group_message_reaction_id"], ["group_message_id" => $group_message_id]);
                    $total_reactions = array();
                    $reactions = [
                        1 => 'like', 2 => 'love', 3 => 'haha',
                        4 => 'wow', 5 => 'sad', 6 => 'angry'
                    ];

                    foreach ($all_reactions as $reaction) {
                        $reaction_id = $reaction['reaction_id'];

                        if (isset($reactions[$reaction_id])) {
                            $reaction_index = $reactions[$reaction_id];

                            if (!isset($total_reactions[$reaction_index])) {
                                $total_reactions[$reaction_index] = 0;
                            }

                            $total_reactions[$reaction_index] = ($total_reactions[$reaction_index])+1;
                        }
                    }

                    $update_total_reactions = json_encode($total_reactions);

                    DB::connect()->update("group_messages", ["total_reactions" => $update_total_reactions], ["group_message_id" => $group_message_id]);


                    $realtime_log_data = array();
                    $realtime_log_data["log_type"] = 'message_reaction';
                    $realtime_log_data["related_parameters"] = [
                        "group_id" => $group_message['group_id'],
                        "message_id" => $group_message_id,
                        "total_reactions" => $update_total_reactions,
                    ];
                    $realtime_log_data["related_parameters"] = json_encode($realtime_log_data["related_parameters"]);
                    $realtime_log_data["created_on"] = Registry::load('current_user')->time_stamp;

                    DB::connect()->insert("realtime_logs", $realtime_log_data);

                    $result = array();
                    $result['success'] = true;
                    $result['todo'] = 'update_message_reactions';
                    $result['update_data']['group_id'] = $group_message['group_id'];
                    $result['update_data']['message_id'] = $group_message_id;
                    $result['update_data']['can_react'] = true;

                    if (!empty($total_reactions)) {
                        $result['update_data']['total_reactions'] = $total_reactions;
                    }

                    if (!empty($user_reaction)) {
                        $result['update_data']['user_reaction'] = $user_reaction;
                    }
                }
            }
        }
    }
}

?>