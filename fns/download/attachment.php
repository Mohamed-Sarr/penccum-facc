<?php

$continue = false;
$super_privileges = false;
$current_user_id = Registry::load('current_user')->id;

$output = array();
$output['success'] = false;
$output['error'] = Registry::load('strings')->went_wrong;
$output['error_key'] = 'something_went_wrong';

if (isset($download['group_id'])) {

    $download["group_id"] = filter_var($download["group_id"], FILTER_SANITIZE_NUMBER_INT);

    if (!empty($download["group_id"]) && isset($download['message_id'])) {

        $download["message_id"] = filter_var($download["message_id"], FILTER_SANITIZE_NUMBER_INT);

        if (!empty($download["message_id"]) && isset($download['attachment_index'])) {

            $attachment_index = filter_var($download["attachment_index"], FILTER_SANITIZE_NUMBER_INT);

            if (!empty($attachment_index) || (int)$attachment_index === 0) {

                $column = $join = $where = null;
                $columns = [
                    'group_messages.attachments', 'group_members.group_role_id'
                ];

                $join["[>]group_members"] = ["group_messages.group_id" => "group_id", "AND" => ["group_members.user_id" => Registry::load('current_user')->id]];

                $where["group_messages.group_id"] = $download["group_id"];
                $where["group_messages.group_message_id"] = $download["message_id"];
                $where["LIMIT"] = 1;

                $message_info = DB::connect()->select('group_messages', $join, $columns, $where);

                if (role(['permissions' => ['groups' => 'super_privileges']])) {
                    $super_privileges = true;
                }

                if (isset($message_info[0])) {

                    $message_info = $message_info[0];

                    if (role(['permissions' => ['groups' => 'download_attachments']])) {
                        if ($super_privileges || isset($message_info['group_role_id']) && !empty($message_info['group_role_id'])) {
                            if ($super_privileges || role(['permissions' => ['messages' => 'download_attachments'], 'group_role_id' => $message_info['group_role_id']])) {
                                if (isset($message_info['attachments']) && !empty($message_info['attachments'])) {
                                    $attachments = json_decode($message_info['attachments']);

                                    if (isset($attachments[$attachment_index])) {

                                        $attached_file = $attachments[$attachment_index];

                                        if (isset($attached_file->file) && file_exists($attached_file->file)) {
                                            $attached_file = $attached_file->file;
                                            $continue = true;
                                        } else {
                                            $output['error'] = Registry::load('strings')->file_expired;
                                            $output['error_key'] = 'file_expired';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
} else if (isset($download['private_conversation_id'])) {

    $download["private_conversation_id"] = filter_var($download["private_conversation_id"], FILTER_SANITIZE_NUMBER_INT);

    if (!empty($download["private_conversation_id"]) && isset($download['message_id'])) {

        $download["message_id"] = filter_var($download["message_id"], FILTER_SANITIZE_NUMBER_INT);

        if (!empty($download["message_id"]) && isset($download['attachment_index'])) {

            $attachment_index = filter_var($download["attachment_index"], FILTER_SANITIZE_NUMBER_INT);

            if (!empty($attachment_index) || (int)$attachment_index === 0) {

                $columns = $join = $where = null;
                $columns = [
                    'private_chat_messages.attachments',
                ];
                $where["private_chat_messages.private_conversation_id"] = $download["private_conversation_id"];
                $where["private_chat_messages.private_chat_message_id"] = $download["message_id"];
                $join["[>]private_conversations"] = ["private_chat_messages.private_conversation_id" => "private_conversation_id"];

                if (!role(['permissions' => ['private_conversations' => 'super_privileges']])) {
                    $where["AND"]["OR #first_query"] = [
                        "private_conversations.initiator_user_id" => $current_user_id,
                        "private_conversations.recipient_user_id" => $current_user_id,
                    ];
                }

                $where["LIMIT"] = 1;
                $message_info = DB::connect()->select('private_chat_messages', $join, $columns, $where);

                if (isset($message_info[0])) {

                    $message_info = $message_info[0];

                    if (isset($message_info['attachments']) && !empty($message_info['attachments'])) {
                        $attachments = json_decode($message_info['attachments']);

                        if (isset($attachments[$attachment_index])) {

                            $attached_file = $attachments[$attachment_index];

                            if (isset($attached_file->file) && file_exists($attached_file->file)) {
                                $attached_file = $attached_file->file;
                                $continue = true;
                            } else {
                                $output['error'] = Registry::load('strings')->file_expired;
                                $output['error_key'] = 'file_expired';
                            }
                        }
                    }
                }
            }
        }
    }
}


if ($continue) {
    if (!isset($download['validate'])) {
        $output = array();

        $file_name = basename($attached_file);
        $file_seperator = Registry::load('config')->file_seperator;

        $original_file_name = explode($file_seperator, $file_name, 2);

        if (isset($original_file_name[1])) {
            $original_file_name = $original_file_name[1];
        } else {
            $original_file_name = $file_name;
        }

        $download_file = [
            'download' => $attached_file,
            'download_as' => $original_file_name,
            'real_path' => true
        ];

        files('download', $download_file);
    } else {
        $output = array();
        $output['success'] = true;

        if (isset($download['group_id'])) {
            $output['download_link'] = Registry::load('config')->site_url.'download/attachment/';
            $output['download_link'] .= 'group_id/'.$download['group_id'].'/';
        } else if (isset($download['private_conversation_id'])) {
            $output['download_link'] = Registry::load('config')->site_url.'download/attachment/';
            $output['download_link'] .= 'private_conversation_id/'.$download['private_conversation_id'].'/';
        }
        $output['download_link'] .= 'message_id/'.$download['message_id'].'/';
        $output['download_link'] .= 'attachment_index/'.$download['attachment_index'].'/';
    }
}

?>