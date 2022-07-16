<?php

$result = array();
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->went_wrong;
$result['error_key'] = 'something_went_wrong';

$user_id = Registry::load('current_user')->id;
$badge_id = 0;

if (role(['permissions' => ['badges' => 'assign']])) {

    if (isset($data['badge_id'])) {
        $badge_id = filter_var($data["badge_id"], FILTER_SANITIZE_NUMBER_INT);
    }

    if (isset($data['user_id'])) {
        $user_id = filter_var($data["user_id"], FILTER_SANITIZE_NUMBER_INT);
    }

    if (!empty($badge_id) && !empty($user_id)) {

        $columns = $join = $where = null;
        $columns = [
            'badges.badge_id', 'badges_assigned.badge_assigned_id',
        ];

        $join["[>]badges_assigned"] = ["badges.badge_id" => "badge_id", "AND" => ["user_id" => $user_id]];

        $where["badges.badge_id"] = $badge_id;
        $where["badges.disabled"] = 0;
        $where["badges.badge_category"] = 'profile';
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
                "user_id" => $user_id,
                "badge_id" => $badge_id,
                "assigned_on" => Registry::load('current_user')->time_stamp,
            ]);
            if (!DB::connect()->error) {
                if (isset(Registry::load('settings')->site_notifications->on_new_site_badges)) {
                    $related_parameters = ['badge_id' => $badge_id];
                    $related_parameters = json_encode($related_parameters);

                    DB::connect()->insert("site_notifications", [
                        "user_id" => $user_id,
                        "notification_type" => 'new_badge_awarded',
                        "related_user_id" => $user_id,
                        "related_parameters" => $related_parameters,
                        "created_on" => Registry::load('current_user')->time_stamp,
                        "updated_on" => Registry::load('current_user')->time_stamp,
                    ]);
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