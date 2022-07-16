<?php

if (role(['permissions' => ['complaints' => 'report']])) {

    include 'fns/filters/load.php';
    $result = array();
    $noerror = true;
    $message_id = $user_id = $group_id = null;
    $result['success'] = false;
    $result['error_message'] = Registry::load('strings')->invalid_value;
    $result['error_key'] = 'invalid_value';
    $result['error_variables'] = [];

    $reasons = ['spam', 'abuse', 'inappropriate', 'other'];

    if (!isset($data['group_id']) && !isset($data['user_id'])) {
        $noerror = false;
    }

    if (!isset($data['reason']) || empty(trim($data['reason'])) || !in_array($data['reason'], $reasons)) {
        $result['error_variables'][] = ['reason'];
        $noerror = false;
    }

    if (isset($data['group_id'])) {
        $data['group_id'] = filter_var($data['group_id'], FILTER_SANITIZE_NUMBER_INT);
        if (empty($data['group_id'])) {
            $noerror = false;
        } else {
            $columns = $where = null;
            $columns = ['name'];
            $where['group_id'] = $data['group_id'];
            $group_valid = DB::connect()->select('groups', $columns, $where);

            if (!isset($group_valid[0])) {
                $noerror = false;
            }
        }
    } else if (isset($data['user_id'])) {
        $data['user_id'] = filter_var($data['user_id'], FILTER_SANITIZE_NUMBER_INT);
        if (empty($data['user_id'])) {
            $noerror = false;
        } else {
            $columns = $where = null;
            $columns = ['display_name'];
            $where['user_id'] = $data['user_id'];
            $user_valid = DB::connect()->select('site_users', $columns, $where);

            if (!isset($user_valid[0])) {
                $noerror = false;
            }
        }
    }

    if ($noerror) {

        if (isset($data['comments'])) {
            $data['comments'] = htmlspecialchars($data['comments'], ENT_QUOTES, 'UTF-8');
        } else {
            $data['comments'] = '';
        }

        if (isset($data['group_id'])) {
            $group_id = $data['group_id'];
        } else if (isset($data['user_id'])) {
            $user_id = $data['user_id'];
        }

        if (isset($data['message_id'])) {
            $data['message_id'] = filter_var($data['message_id'], FILTER_SANITIZE_NUMBER_INT);
            if (!empty($data['message_id'])) {
                $message_id = $data['message_id'];
            }
        }

        $insert_data = [
            "reason" => $data['reason'],
            "comments_by_complainant" => $data['comments'],
            "complainant_user_id" => Registry::load('current_user')->id,
            "user_id" => $user_id,
            "group_id" => $group_id,
            "complaint_status" => 0,
            "created_on" => Registry::load('current_user')->time_stamp,
            "updated_on" => Registry::load('current_user')->time_stamp,
        ];

        if (!empty($message_id)) {
            if (!empty($group_id)) {
                $insert_data['group_message_id'] = $message_id;
            } else {
                $insert_data['private_chat_message_id'] = $message_id;
            }
        }

        DB::connect()->insert("complaints", $insert_data);

        if (!DB::connect()->error) {
            $result = array();
            $result['success'] = true;
            $result['todo'] = 'reload';
            $result['reload'] = 'complaints';
        } else {
            $result['error_message'] = Registry::load('strings')->went_wrong;
            $result['error_key'] = 'something_went_wrong';
        }

    }
}
?>