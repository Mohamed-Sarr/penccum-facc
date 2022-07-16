<?php
if (role(['permissions' => ['complaints' => ['track_status', 'review_complaints']], 'condition' => 'OR'])) {

    if (isset($load['complaint_id'])) {

        $load["complaint_id"] = filter_var($load["complaint_id"], FILTER_SANITIZE_NUMBER_INT);

        if (!empty($load['complaint_id'])) {



            $columns = [
                'complaints.complaint_id', 'complaints.user_id', 'complaints.group_id',
                'related_user_id.display_name(related_user)', 'groups.name(group_name)', 'complaints.complaint_status',
                'complaints.reason', 'complaints.group_message_id', 'complaints.comments_by_complainant',
                'complaints.comments_by_reviewer', 'complaints.created_on'
            ];

            $join["[>]site_users(related_user_id)"] = ["complaints.user_id" => "user_id"];
            $join["[>]groups"] = ["complaints.group_id" => "group_id"];

            if (role(['permissions' => ['complaints' => 'review_complaints']])) {
                $where["complaints.complaint_id[!]"] = 0;
            } else if (role(['permissions' => ['complaints' => 'track_status']])) {
                $where["complaints.complainant_user_id"] = Registry::load('current_user')->id;
            }

            $where["complaints.complaint_id"] = $load["complaint_id"];
            $where["LIMIT"] = 1;

            $where["ORDER"] = ["complaints.complaint_status" => "ASC", "complaints.complaint_id" => "DESC"];

            $complaint = DB::connect()->select('complaints', $join, $columns, $where);



            if (isset($complaint[0])) {

                $complaint = $complaint[0];

                $complaint_status = 'under_review';

                if ((int)$complaint['complaint_status'] === 1) {
                    $complaint_status = 'action_taken';
                } else if ((int)$complaint['complaint_status'] === 2) {
                    $complaint_status = 'rejected';
                }

                $form = array();
                $form['loaded'] = new stdClass();
                $form['loaded']->title = 'COMP#'.$complaint['complaint_id'];


                $form['fields'] = new stdClass();

                $form['fields']->update = [
                    "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "complaint"
                ];

                if (isset($complaint['group_id']) && !empty($complaint['group_id'])) {

                    $columns = $where = null;
                    $columns = ['name'];
                    $where['group_id'] = $complaint['group_id'];
                    $group_info = DB::connect()->select('groups', $columns, $where);

                    if (isset($group_info[0])) {

                        $form['fields']->group_id = [
                            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $complaint['group_id']
                        ];

                        $form['fields']->group_name = [
                            "title" => Registry::load('strings')->group_name, "tag" => 'input', "type" => 'text',
                            "attributes" => ['disabled' => 'disabled'], "class" => 'field', "value" => $group_info[0]['name'],
                        ];

                        if (isset($complaint['group_message_id']) && !empty($complaint['group_message_id'])) {

                            $columns = $where = $join = null;
                            $columns = ['site_users.display_name', 'group_messages.filtered_message'];
                            $where['group_id'] = $complaint['group_id'];
                            $where['group_message_id'] = $complaint['group_message_id'];
                            $join["[>]site_users"] = ["group_messages.user_id" => "user_id"];
                            $message_info = DB::connect()->select('group_messages', $join, $columns, $where);

                            $form['fields']->message_identifier = [
                                "title" => Registry::load('strings')->message_id, "tag" => 'input', "type" => 'text',
                                "attributes" => ['disabled' => 'disabled'], "class" => 'field', "value" => $complaint['group_message_id'],
                            ];

                            if (isset($message_info[0])) {

                                $form['fields']->message_id = [
                                    "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $complaint['group_message_id']
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
                } else if (isset($complaint['user_id']) && !empty($complaint['user_id'])) {
                    $columns = $where = null;
                    $columns = ['display_name', 'username'];
                    $where['user_id'] = $complaint['user_id'];
                    $user_info = DB::connect()->select('site_users', $columns, $where);

                    if (isset($user_info[0])) {
                        $form['fields']->user_id = [
                            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $complaint['user_id']
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


                $form['fields']->reason = [
                    "title" => Registry::load('strings')->whats_wrong, "tag" => 'select', "class" => 'field',
                    "value" => $complaint['reason']
                ];
                $form['fields']->reason['options'] = [
                    "spam" => Registry::load('strings')->spam, "abuse" => Registry::load('strings')->abuse,
                    "inappropriate" => Registry::load('strings')->inappropriate, "other" => Registry::load('strings')->other,
                ];
                $form['fields']->reason["attributes"] = ["disabled" => "disabled"];

                if (!empty($complaint['comments_by_complainant'])) {
                    $form['fields']->comments_by_complainant = [
                        "title" => Registry::load('strings')->comments_by_complainant, "tag" => 'textarea', "closetag" => true,
                        "class" => 'field', "value" => $complaint['comments_by_complainant']
                    ];

                    $form['fields']->comments_by_complainant["attributes"] = ["rows" => 5, "disabled" => "disabled"];
                }

                $form['fields']->comments = [
                    "title" => Registry::load('strings')->comments_by_reviewer, "tag" => 'textarea', "closetag" => true,
                    "class" => 'field', "value" => $complaint['comments_by_reviewer']
                ];

                $form['fields']->comments["attributes"] = ["rows" => 5];

                $form['fields']->complaint_status = [
                    "title" => Registry::load('strings')->complaint_status, "tag" => 'select', "class" => 'field',
                    "value" => $complaint_status
                ];
                $form['fields']->complaint_status['options'] = [
                    "under_review" => Registry::load('strings')->under_review, "action_taken" => Registry::load('strings')->action_taken,
                    "rejected" => Registry::load('strings')->rejected
                ];

                $form['fields']->complaint_id = [
                    "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $complaint['complaint_id']
                ];


                if (!role(['permissions' => ['complaints' => 'review_complaints']])) {

                    if (!empty($complaint['comments_by_reviewer'])) {
                        $form['fields']->comments["attributes"] = ["disabled" => "disabled"];
                    } else {
                        unset($form['fields']->comments);
                    }

                    $form['fields']->complaint_status["attributes"] = ["disabled" => "disabled"];
                } else {
                    $form['loaded']->button = Registry::load('strings')->update;
                }
            }
        }
    }
}
?>