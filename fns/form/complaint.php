<?php

if (role(['permissions' => ['complaints' => 'report']])) {
    $form = array();
    $form['loaded'] = new stdClass();
    $form['loaded']->title = Registry::load('strings')->report;
    $form['loaded']->button = Registry::load('strings')->report;


    $form['fields'] = new stdClass();

    $form['fields']->process = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "add"
    ];

    $form['fields']->add = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "complaint"
    ];

    if (isset($load['group_id'])) {
        $load["group_id"] = filter_var($load["group_id"], FILTER_SANITIZE_NUMBER_INT);
        if (!empty($load['group_id'])) {

            $columns = $where = null;
            $columns = ['name'];
            $where['group_id'] = $load['group_id'];
            $group_info = DB::connect()->select('groups', $columns, $where);

            if (isset($group_info[0])) {

                $form['fields']->group_id = [
                    "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load['group_id']
                ];

                $form['fields']->group_name = [
                    "title" => Registry::load('strings')->group_name, "tag" => 'input', "type" => 'text',
                    "attributes" => ['disabled' => 'disabled'], "class" => 'field', "value" => $group_info[0]['name'],
                ];

                if (isset($load['message_id'])) {
                    $load["message_id"] = filter_var($load["message_id"], FILTER_SANITIZE_NUMBER_INT);
                    if (!empty($load['message_id'])) {

                        $columns = $where = $join = null;
                        $columns = ['site_users.display_name', 'group_messages.filtered_message'];
                        $where['group_id'] = $load['group_id'];
                        $where['group_message_id'] = $load['message_id'];
                        $join["[>]site_users"] = ["group_messages.user_id" => "user_id"];
                        $message_info = DB::connect()->select('group_messages', $join, $columns, $where);

                        $form['fields']->message_identifier = [
                            "title" => Registry::load('strings')->message_id, "tag" => 'input', "type" => 'text',
                            "attributes" => ['disabled' => 'disabled'], "class" => 'field', "value" => $load['message_id'],
                        ];

                        if (isset($message_info[0])) {

                            $form['fields']->message_id = [
                                "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load['message_id']
                            ];

                            $form['fields']->posted_by = [
                                "title" => Registry::load('strings')->posted_by, "tag" => 'input', "type" => 'text',
                                "attributes" => ['disabled' => 'disabled'], "class" => 'field', "value" => $message_info[0]['display_name'],
                            ];

                            $message = trim(strip_tags($message_info[0]['filtered_message']));
                            $message = str_replace(['\'', '"'], "", $message);
                            $message = preg_replace('/^\p{Z}+|\p{Z}+$/u', '', $message);
                            
                            if (!empty($message)) {
                                $form['fields']->message = [
                                    "title" => Registry::load('strings')->message, "tag" => 'input', "type" => 'text',
                                    "attributes" => ['disabled' => 'disabled'], "class" => 'field',
                                    "value" => $message,
                                ];
                            }
                        }

                    }
                }
            }
        }
    } else if (isset($load['user_id'])) {

        $load["user_id"] = filter_var($load["user_id"], FILTER_SANITIZE_NUMBER_INT);

        if (!empty($load['user_id'])) {
            $columns = $where = null;
            $columns = ['display_name', 'username'];
            $where['user_id'] = $load['user_id'];
            $user_info = DB::connect()->select('site_users', $columns, $where);

            if (isset($user_info[0])) {
                $form['fields']->user_id = [
                    "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load['user_id']
                ];

                $form['fields']->full_name = [
                    "title" => Registry::load('strings')->full_name, "tag" => 'input', "type" => 'text',
                    "attributes" => ['disabled' => 'disabled'], "class" => 'field', "value" => $user_info[0]['display_name'],
                ];

                $form['fields']->username = [
                    "title" => Registry::load('strings')->username, "tag" => 'input', "type" => 'text',
                    "attributes" => ['disabled' => 'disabled'], "class" => 'field', "value" => $user_info[0]['username'],
                ];
            }
        }
    }


    $form['fields']->reason = [
        "title" => Registry::load('strings')->whats_wrong, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->reason['options'] = [
        "spam" => Registry::load('strings')->spam, "abuse" => Registry::load('strings')->abuse,
        "inappropriate" => Registry::load('strings')->inappropriate, "other" => Registry::load('strings')->other,
    ];


    $form['fields']->comments = [
        "title" => Registry::load('strings')->comments_if_any, "tag" => 'textarea', "closetag" => true,
        "class" => 'field', "placeholder" => Registry::load('strings')->comments_if_any,
    ];

    $form['fields']->comments["attributes"] = ["rows" => 5];
}
?>