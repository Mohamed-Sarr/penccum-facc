<?php

$messages = array();
$conversation = '';
$super_privileges = false;
$current_user_id = Registry::load('current_user')->id;
$skip_check = false;

if (role(['permissions' => ['groups' => 'export_chat', 'private_conversations' => 'export_chat'], 'condition' => 'OR'])) {
    if (isset($download['validate'])) {
        $output = array();
        $output['success'] = true;

        if (isset($download['group_id'])) {
            $download["group_id"] = filter_var($download["group_id"], FILTER_SANITIZE_NUMBER_INT);
            if (!empty($download["group_id"])) {
                $output['download_link'] = Registry::load('config')->site_url.'download/messages/';
                $output['download_link'] .= 'group_id/'.$download['group_id'].'/';
            }
        } else if (isset($download['private_conversation_id'])) {
            $download["private_conversation_id"] = filter_var($download["private_conversation_id"], FILTER_SANITIZE_NUMBER_INT);
            if (!empty($download["private_conversation_id"])) {
                $output['download_link'] = Registry::load('config')->site_url.'download/messages/';
                $output['download_link'] .= 'private_conversation_id/'.$download['private_conversation_id'].'/';
            }
        }

    } else {
        if (isset($download["group_id"])) {

            $download["group_id"] = filter_var($download["group_id"], FILTER_SANITIZE_NUMBER_INT);

            if (!empty($download["group_id"])) {
                $columns = $join = $where = null;
                $columns = [
                    'groups.name', 'group_members.group_role_id',
                    'group_roles.group_role_attribute'
                ];

                $join["[>]group_members"] = ["groups.group_id" => "group_id", "AND" => ["user_id" => Registry::load('current_user')->id]];
                $join["[>]group_roles"] = ["group_members.group_role_id" => "group_role_id"];

                $where["groups.group_id"] = $download["group_id"];
                $where["LIMIT"] = 1;
                $group_info = DB::connect()->select('groups', $join, $columns, $where);

                if (isset($group_info[0])) {

                    $group_info = $group_info[0];

                    if (role(['permissions' => ['groups' => 'super_privileges']])) {
                        $super_privileges = true;
                    }

                    if (role(['permissions' => ['groups' => 'export_chat']])) {
                        if ($super_privileges || isset($group_info['group_role_id']) && !empty($group_info['group_role_id'])) {
                            if ($super_privileges || $group_info['group_role_attribute'] !== 'banned_users') {

                                $conversation = $group_info['name'];
                                $column = $join = $where = null;
                                $skip_check = true;
                                $columns = [
                                    'site_users.display_name', 'group_messages.group_message_id(message_id)', 'group_messages.filtered_message',
                                    'group_messages.system_message', 'group_messages.parent_message_id', 'group_messages.attachments',
                                    'group_messages.link_preview', 'group_messages.created_on', 'group_messages.updated_on',
                                    'group_messages.user_id', 'groups.name(group_name)', 'group_messages.attachment_type',
                                    'site_users.username', 'group_messages.group_id',
                                ];

                                $join["[>]site_users"] = ["group_messages.user_id" => "user_id"];
                                $join["[><]groups"] = ["group_messages.group_id" => "group_id"];

                                $where["group_messages.group_id"] = $download["group_id"];
                                $where["group_messages.system_message[!]"] = 1;

                                $where["ORDER"] = ['group_messages.group_message_id' => 'ASC'];


                                $messages = DB::connect()->select('group_messages', $join, $columns, $where);
                            }
                        }
                    }
                }
            }
        } else if (isset($download["private_conversation_id"])) {
            if (role(['permissions' => ['private_conversations' => 'export_chat']])) {

                $download["private_conversation_id"] = filter_var($download["private_conversation_id"], FILTER_SANITIZE_NUMBER_INT);

                if (!empty($download["private_conversation_id"])) {
                    $column = $join = $where = null;
                    $columns = [
                        'recipient.display_name(recipient_name)', 'initiator.display_name(initiator_name)',
                        'recipient.username(recipient_username)', 'initiator.username(initiator_username)',
                        'private_conversations.initiator_user_id', 'private_conversations.recipient_user_id',
                        'private_conversations.private_conversation_id'
                    ];

                    $join["[>]site_users(recipient)"] = ["private_conversations.recipient_user_id" => "user_id"];
                    $join["[>]site_users(initiator)"] = ["private_conversations.initiator_user_id" => "user_id"];
                    $where["private_conversations.private_conversation_id"] = $download["private_conversation_id"];
                    $where["AND"]["OR #first_query"] = [
                        "private_conversations.initiator_user_id" => $current_user_id,
                        "private_conversations.recipient_user_id" => $current_user_id,
                    ];

                    $where["LIMIT"] = 1;

                    $private_conversation = DB::connect()->select('private_conversations', $join, $columns, $where);

                    if (isset($private_conversation[0])) {

                        if (role(['permissions' => ['private_conversations' => 'export_chat']])) {

                            $conversation = '['.$private_conversation[0]['initiator_name'].'] ['.$private_conversation[0]['recipient_username'].']';

                            $column = $join = $where = null;
                            $columns = [
                                'site_users.display_name', 'private_chat_messages.private_chat_message_id(message_id)', 'private_chat_messages.filtered_message',
                                'private_chat_messages.system_message', 'private_chat_messages.parent_message_id', 'private_chat_messages.attachments',
                                'private_chat_messages.link_preview', 'private_chat_messages.created_on', 'private_chat_messages.updated_on',
                                'private_chat_messages.user_id', 'private_chat_messages.attachment_type',
                                'site_users.username',
                            ];

                            $join["[>]site_users"] = ["private_chat_messages.user_id" => "user_id"];

                            $where["private_chat_messages.private_conversation_id"] = $download["private_conversation_id"];
                            $where["private_chat_messages.system_message[!]"] = 1;

                            $where["ORDER"] = ['private_chat_messages.private_chat_message_id' => 'ASC'];


                            $messages = DB::connect()->select('private_chat_messages', $join, $columns, $where);
                        }
                    }
                }
            }
        }

        if (!empty($messages) || $skip_check) {

            $filename = 'conversation.html';
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private', false);
            header('Content-type: text/html');

            header('Content-Disposition: attachment; filename="'. basename($filename) . '";');
            header('Content-Transfer-Encoding: binary');
            include('fns/download/template_messages_table.php');
        } else {
            $output = array();
            $output['success'] = false;
            $output['error'] = Registry::load('strings')->went_wrong;
            $output['error_key'] = 'something_went_wrong';
        }
    }
} else {
    $output = array();
    $output['success'] = false;
    $output['error'] = Registry::load('strings')->permission_denied;
    $output['error_key'] = 'permission_denied';
}

?>