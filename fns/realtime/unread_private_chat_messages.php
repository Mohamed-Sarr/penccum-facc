<?php
use Medoo\Medoo;

$data["unread_private_chat_messages"] = filter_var($data["unread_private_chat_messages"], FILTER_SANITIZE_NUMBER_INT);

if (empty($data["unread_private_chat_messages"])) {
    $data["unread_private_chat_messages"] = 0;
}


$current_user_id = Registry::load('current_user')->id;

$columns = $join = $where = null;

$columns = ["private_conversations.private_conversation_id"];

$columns['unread_messages'] = Medoo::raw('(SELECT count(<private_chat_message_id>) FROM <private_chat_messages> WHERE <user_id> != :current_user_id AND <private_conversation_id> = <private_conversations.private_conversation_id> AND <read_status> = 0)', ['current_user_id' => $current_user_id]);

$where["AND"]["OR #first_query"] = [
    "private_conversations.initiator_user_id" => $current_user_id,
    "private_conversations.recipient_user_id" => $current_user_id,
];

$where["LIMIT"] = Registry::load('settings')->records_per_call;

$conversations = DB::connect()->select('private_conversations', $columns, $where);

$unread_private_chat_messages = 0;

foreach ($conversations as $conversation) {

    if (!empty($conversation['unread_messages'])) {
        $unread_private_chat_messages = $conversation['unread_messages']+$unread_private_chat_messages;
    }
}

if ((int)$unread_private_chat_messages !== (int)$data["unread_private_chat_messages"]) {
    $result['unread_private_chat_messages'] = $unread_private_chat_messages;

    if (isset(Registry::load('settings')->play_notification_sound->on_private_conversation_unread_count_change)) {
        if ($unread_private_chat_messages > $data["unread_private_chat_messages"]) {
            $result['play_sound_notification'] = true;
        }
    }

    $escape = true;
}