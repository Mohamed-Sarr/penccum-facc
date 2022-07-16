<?php

$result = array();
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->went_wrong;
$result['error_key'] = 'something_went_wrong';

$group_id = 0;
$badge_id = 0;

if (role(['permissions' => ['badges' => 'assign']])) {

    if (isset($data['badge_id'])) {
        $badge_id = filter_var($data["badge_id"], FILTER_SANITIZE_NUMBER_INT);
    }

    if (isset($data['group_id'])) {
        $group_id = filter_var($data["group_id"], FILTER_SANITIZE_NUMBER_INT);
    }

    if (!empty($badge_id) && !empty($group_id)) {

        $columns = $join = $where = null;
        $columns = [
            'badges.badge_id', 'badges_assigned.badge_assigned_id',
        ];

        $join["[>]badges_assigned"] = ["badges.badge_id" => "badge_id", "AND" => ["group_id" => $group_id]];

        $where["badges.badge_id"] = $badge_id;
        $where["badges.disabled"] = 0;
        $where["badges.badge_category"] = 'group';
        $where["LIMIT"] = 1;

        $badge = DB::connect()->select('badges', $join, $columns, $where);

        if (!isset($badge[0])) {
            $badge_id = 0;
        } else {
            $badge = $badge[0];
        }
    }

    if (!empty($badge_id)) {

        if (!isset($badge['badge_assigned_id']) || empty($badge['badge_assigned_id'])) {
            DB::connect()->insert("badges_assigned", [
                "group_id" => $group_id,
                "badge_id" => $badge_id,
                "assigned_on" => Registry::load('current_user')->time_stamp,
            ]);

            if (!DB::connect()->error) {
                if (!empty(Registry::load('current_user')->id)) {
                    if (isset(Registry::load('settings')->system_messages_groups->on_awarding_group_badges)) {
                        $system_message = [
                            'message' => 'new_badge_awarded',
                            'badge_id' => $badge_id
                        ];

                        $system_message = json_encode($system_message);
                        DB::connect()->insert("group_messages", [
                            "system_message" => 1,
                            "original_message" => 'system_message',
                            "filtered_message" => $system_message,
                            "group_id" => $group_id,
                            "user_id" => Registry::load('current_user')->id,
                            "created_on" => Registry::load('current_user')->time_stamp,
                            "updated_on" => Registry::load('current_user')->time_stamp,
                        ]);

                        DB::connect()->update("groups", ["updated_on" => Registry::load('current_user')->time_stamp], ['group_id' => $group_id]);
                    }
                }
            }
        }
        $result = array();
        $result['success'] = true;
        $result['todo'] = 'reload';
        $result['reload'] = 'badges';
    }
}

?>