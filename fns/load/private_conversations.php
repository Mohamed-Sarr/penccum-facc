<?php

use Medoo\Medoo;

if (role(['permissions' => ['private_conversations' => 'view_private_chats']])) {

    $current_user_id = Registry::load('current_user')->id;

    $columns = [
        'recipient.display_name(recipient_name)', 'initiator.display_name(initiator_name)',
        'recipient.username(recipient_username)', 'initiator.username(initiator_username)',
        'recipient.online_status(recipient_online_status)', 'initiator.online_status(initiator_online_status)',
        'recipient_settings.offline_mode(recipient_offline_mode)', 'initiator_settings.offline_mode(initiator_offline_mode)',
        'private_conversations.initiator_user_id', 'private_conversations.recipient_user_id',
        'private_conversations.private_conversation_id', 'blacklist_recipient.block(blocked_recipient)', 'blacklist_initiator.block(blocked_initiator)'
    ];


    $columns['unread_messages'] = Medoo::raw('(SELECT count(<private_chat_message_id>) FROM <private_chat_messages> WHERE <user_id> != :current_user_id AND <private_conversation_id> = <private_conversations.private_conversation_id> AND <read_status> = 0)', ['current_user_id' => $current_user_id]);

    $join["[>]site_users(recipient)"] = ["private_conversations.recipient_user_id" => "user_id"];
    $join["[>]site_users(initiator)"] = ["private_conversations.initiator_user_id" => "user_id"];

    $join["[>]site_users_settings(recipient_settings)"] = ["private_conversations.recipient_user_id" => "user_id"];
    $join["[>]site_users_settings(initiator_settings)"] = ["private_conversations.initiator_user_id" => "user_id"];

    $join["[>]site_users_blacklist(blacklist_recipient)"] = ["private_conversations.recipient_user_id" => "user_id", "AND" => ["blacklist_recipient.blacklisted_user_id" => Registry::load('current_user')->id]];
    $join["[>]site_users_blacklist(blacklist_initiator)"] = ["private_conversations.initiator_user_id" => "user_id", "AND" => ["blacklist_initiator.blacklisted_user_id" => Registry::load('current_user')->id]];

    if (!empty($data["offset"])) {
        $data["offset"] = array_map('intval', explode(',', $data["offset"]));
        $where["private_conversations.private_conversation_id[!]"] = $data["offset"];
    }

    if (!empty($data["search"])) {
        $where["AND #search_query"]["OR"] = [
            "recipient.display_name[~]" => $data["search"],
            "recipient.username[~]" => $data["search"],
            "initiator.display_name[~]" => $data["search"],
            "initiator.username[~]" => $data["search"],
        ];
    }

    $where["AND"]["OR #first_query"] = [
        "private_conversations.initiator_user_id" => $current_user_id,
        "private_conversations.recipient_user_id" => $current_user_id,
    ];

    $where["LIMIT"] = Registry::load('settings')->records_per_call;

    $where["ORDER"] = ["private_conversations.updated_on" => "DESC"];
    $conversations = DB::connect()->select('private_conversations', $join, $columns, $where);

    $i = 1;
    $output = array();
    $output['loaded'] = new stdClass();
    $output['loaded']->title = Registry::load('strings')->messages;
    $output['loaded']->offset = array();
    $blocked_by_user = false;

    if (!empty($data["offset"])) {
        $output['loaded']->offset = $data["offset"];
    }

    if (role(['permissions' => ['site_users' => 'view_site_users']])) {
        $output['todo'] = new stdClass();
        $output['todo']->class = 'load_aside';
        $output['todo']->title = Registry::load('strings')->site_users;
        $output['todo']->attributes['load'] = 'site_users';
    } elseif (role(['permissions' => ['site_users' => 'view_online_users']])) {
        $output['todo'] = new stdClass();
        $output['todo']->class = 'load_aside';
        $output['todo']->title = Registry::load('strings')->online;
        $output['todo']->attributes['load'] = 'online';
    }

    foreach ($conversations as $conversation) {

        $output['loaded']->offset[] = $conversation['private_conversation_id'];

        if ((int)$conversation['initiator_user_id'] === (int)$current_user_id) {
            $user_id = $conversation['recipient_user_id'];
            $display_name = $conversation['recipient_name'];
            $user_name = $conversation['recipient_username'];
            $online_status = $conversation['recipient_online_status'];
            $offline_mode = $conversation['recipient_offline_mode'];

            if (isset($conversation['blocked_recipient']) && !empty($conversation['blocked_recipient'])) {
                $blocked_by_user = true;
            }

        } else {
            $user_id = $conversation['initiator_user_id'];
            $display_name = $conversation['initiator_name'];
            $user_name = $conversation['initiator_username'];
            $online_status = $conversation['initiator_online_status'];
            $offline_mode = $conversation['initiator_offline_mode'];

            if (isset($conversation['blocked_initiator']) && !empty($conversation['blocked_initiator'])) {
                $blocked_by_user = true;
            }
        }

        $output['content'][$i] = new stdClass();

        if ($blocked_by_user && !role(['permissions' => ['site_users' => 'edit_users']])) {
            $output['content'][$i]->image = Registry::load('config')->site_url.'assets/files/site_users/profile_pics/default.png';
        } else {
            $output['content'][$i]->image = get_image(['from' => 'site_users/profile_pics', 'search' => $user_id]);
        }

        $output['content'][$i]->title = $display_name;
        $output['content'][$i]->class = "private_conversation load_conversation";
        $output['content'][$i]->icon = 0;
        $output['content'][$i]->unread = 0;
        $output['content'][$i]->subtitle = '@'.$user_name;
        $output['content'][$i]->attributes = ['user_id' => $user_id, 'stopPropagation' => true];

        if (empty($data["filter"]) && empty($data["sortby"]) && empty($data["search"])) {
            if (isset($conversation['unread_messages']) && !empty($conversation['unread_messages'])) {
                $output['content'][$i]->unread = abbreviateNumber($conversation['unread_messages']);
            }
        }

        if (role(['permissions' => ['site_users' => 'view_online_users']])) {


            $output['content'][$i]->subtitle = Registry::load('strings')->offline;

            if ((int)$online_status === 1) {
                $output['content'][$i]->online_status = 'online';
                $output['content'][$i]->subtitle = Registry::load('strings')->online;
            } else if ((int)$online_status === 2) {
                $output['content'][$i]->online_status = 'idle';
                $output['content'][$i]->subtitle = Registry::load('strings')->idle;
            }

            if ((int)$offline_mode === 1) {
                if (!role(['permissions' => ['site_users' => 'view_invisible_users']])) {
                    unset($output['content'][$i]->online_status);
                    $output['content'][$i]->subtitle = Registry::load('strings')->offline;
                }
            }
        }

        $option_index = 1;

        if (role(['permissions' => ['private_conversations' => 'clear_chat_history']])) {
            $output['options'][$i][$option_index] = new stdClass();
            $output['options'][$i][$option_index]->option = Registry::load('strings')->clear_chat;
            $output['options'][$i][$option_index]->class = 'ask_confirmation';
            $output['options'][$i][$option_index]->attributes['data-remove'] = 'private_chat_messages';
            $output['options'][$i][$option_index]->attributes['data-user_id'] = $user_id;
            $output['options'][$i][$option_index]->attributes['data-clear_chat_history'] = true;
            $output['options'][$i][$option_index]->attributes['confirmation'] = Registry::load('strings')->confirm_action;
            $output['options'][$i][$option_index]->attributes['submit_button'] = Registry::load('strings')->yes;
            $output['options'][$i][$option_index]->attributes['cancel_button'] = Registry::load('strings')->no;
            $option_index++;
        }

        if (role(['permissions' => ['private_conversations' => 'export_chat']])) {
            $output['options'][$i][$option_index] = new stdClass();
            $output['options'][$i][$option_index]->option = Registry::load('strings')->export_chat;
            $output['options'][$i][$option_index]->class = 'download_file';
            $output['options'][$i][$option_index]->attributes['download'] = 'messages';
            $output['options'][$i][$option_index]->attributes['data-private_conversation_id'] = $conversation['private_conversation_id'];
            $option_index++;
        }

        $output['options'][$i][$option_index] = new stdClass();
        $output['options'][$i][$option_index]->option = Registry::load('strings')->profile;
        $output['options'][$i][$option_index]->class = 'get_info force_request';
        $output['options'][$i][$option_index]->attributes['user_id'] = $user_id;
        $option_index++;

        $i++;
    }
}
?>