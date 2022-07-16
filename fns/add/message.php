<?php

include('fns/filters/profanity.php');
include('fns/url_highlight/load.php');

use VStelmakh\UrlHighlight\UrlHighlight;
use Snipe\BanBuilder\CensorWords;

$create = false;
$ascii_emoji = true;
$result = array();
$skip_message = false;
$uploaded_files = false;
$empty_message = false;
$super_privileges = false;
$message = $link_preview = $attachment_type = '';
$parent_message_id = null;
$attachments = [0 => ''];
$current_user_id = Registry::load('current_user')->id;
$gap_between_messages = role(['find' => 'flood_control_time_difference']);
$customURLHighlighter = new CustomURLHighlighter();
$urlHighlight = new UrlHighlight(null, $customURLHighlighter);

if (!empty($gap_between_messages)) {
    $gap_between_messages = filter_var($gap_between_messages, FILTER_SANITIZE_NUMBER_INT);
}

$permission = [
    'send_message' => false,
    'attach_stickers' => false,
    'attach_gifs' => false,
    'share_screenshot' => false,
    'send_audio_message' => false,
    'attach_files' => false,
    'attach_from_storage' => false,
    'mention_users' => false,
    'reply_messages' => false,
    'send_as_another_user' => false,
    'generate_link_preview' => false,
    'allow_sharing_links' => false,
];



if ($force_request) {
    if (isset($data['sender'])) {
        $columns = $join = $where = null;

        $columns = ['site_users.user_id'];
        $where["OR"] = ["site_users.username" => $data['sender'], "site_users.email_address" => $data['sender']];
        $where["LIMIT"] = 1;

        $site_user = DB::connect()->select('site_users', $columns, $where);

        if (isset($site_user[0])) {
            Registry::load('current_user')->id = $current_user_id = $site_user[0]['user_id'];
        } else {
            $result = array();
            $result['success'] = false;
            $result['error_message'] = Registry::load('strings')->account_not_found;
            $result['error_key'] = 'account_not_found';
            $result['error_variables'] = [];
            return;
        }
    }

    if (isset($data['group'])) {
        $columns = $join = $where = null;

        $columns = ['groups.group_id'];
        $where["OR"] = ["groups.group_id" => $data['group'], "groups.slug" => $data['group']];
        $where["LIMIT"] = 1;

        $find_group = DB::connect()->select('groups', $columns, $where);

        if (isset($find_group[0])) {
            $data['group_id'] = $find_group[0]['group_id'];
        } else {
            $result = array();
            $result['success'] = false;
            $result['error_message'] = 'Group Not Found';
            $result['error_key'] = 'group_not_found';
            $result['error_variables'] = [];
            return;
        }
    }

    if (isset($data['user'])) {
        $columns = $join = $where = null;

        $columns = ['site_users.user_id'];
        $where["OR"] = ["site_users.username" => $data['user'], "site_users.email_address" => $data['user']];
        $where["LIMIT"] = 1;

        $site_user = DB::connect()->select('site_users', $columns, $where);

        if (isset($site_user[0])) {
            $data['user_id'] = $site_user[0]['user_id'];
        } else {
            $result = array();
            $result['success'] = false;
            $result['error_message'] = Registry::load('strings')->account_not_found;
            $result['error_key'] = 'account_not_found';
            $result['error_variables'] = [];
            return;
        }
    }
}

if (isset($data['group_id'])) {
    $data['group_id'] = filter_var($data['group_id'], FILTER_SANITIZE_NUMBER_INT);
} elseif (isset($data['user_id'])) {
    $data['user_id'] = filter_var($data['user_id'], FILTER_SANITIZE_NUMBER_INT);


    if ((int)$data['user_id'] === (int)$current_user_id) {
        return false;
    } else {
        if ($force_request || role(['permissions' => ['private_conversations' => 'super_privileges']])) {
            $super_privileges = true;
        }

        $columns = $join = $where = null;
        $columns = [
            'site_users.online_status', 'site_roles.site_role_attribute',
            'site_users_settings.deactivated', 'site_users_settings.disable_private_messages'
        ];

        $join["[>]site_roles"] = ["site_users.site_role_id" => "site_role_id"];
        $join["[>]site_users_settings"] = ["site_users.user_id" => "user_id"];

        $where = [
            "site_users.user_id" => $data["user_id"],
        ];

        $where["LIMIT"] = 1;
        $user_info = DB::connect()->select('site_users', $join, $columns, $where);

        if (isset($user_info[0])) {
            $user_info = $user_info[0];

            if (isset($user_info['deactivated']) && !empty($user_info['deactivated']) && !$super_privileges) {
                return;
            }

            if (isset($user_info['disable_private_messages']) && !empty($user_info['disable_private_messages']) && !$super_privileges) {
                return;
            }
        } else {
            $data['user_id'] = null;
        }
    }

    if (!empty($data['user_id'])) {
        $columns = $join = $where = null;
        $columns = [
            'private_conversations.private_conversation_id'
        ];

        $where["OR"]["AND #first_query"] = [
            "private_conversations.initiator_user_id" => $data["user_id"],
            "private_conversations.recipient_user_id" => $current_user_id,
        ];
        $where["OR"]["AND #second_query"] = [
            "private_conversations.initiator_user_id" => $current_user_id,
            "private_conversations.recipient_user_id" => $data["user_id"],
        ];

        $where["LIMIT"] = 1;
        $conversation_id = DB::connect()->select('private_conversations', $columns, $where);

        if (isset($conversation_id[0]['private_conversation_id'])) {
            $conversation_id = $conversation_id[0]['private_conversation_id'];

            if (!$force_request && !empty($gap_between_messages)) {
                $recent_message_time_stamp = DB::connect()->select('private_chat_messages', ['created_on'], [
                    'user_id' => Registry::load('current_user')->id,
                    'system_message[!]' => 1,
                    'ORDER' => ['private_chat_message_id' => 'DESC'],
                    'LIMIT' => 1
                ]);


                if (isset($recent_message_time_stamp[0])) {
                    $recent_message_time_stamp = $recent_message_time_stamp[0]['created_on'];

                    $to_time = strtotime($recent_message_time_stamp);
                    $from_time = strtotime("now");
                    $time_difference = round(abs($to_time - $from_time), 2);

                    if ($time_difference < $gap_between_messages) {
                        $wait_for = $gap_between_messages-$time_difference;

                        $alert_message = Registry::load('strings')->flood_control_error_message.' '.$wait_for.' '.Registry::load('strings')->seconds;
                        $result['alert'] = ['message' => $alert_message];
                        return;
                    }
                }
            }

            $daily_send_limit = role(['find' => 'daily_send_limit_private_messages']);

            if (!empty($daily_send_limit)) {
                $daily_send_limit = filter_var($daily_send_limit, FILTER_SANITIZE_NUMBER_INT);
            }

            if (!$force_request && !empty($daily_send_limit)) {
                $total_chat_messages = DB::connect()->count('private_chat_messages', ['created_on'], [
                    'user_id' => Registry::load('current_user')->id,
                    "created_on[~]" => date('Y-m-d')
                ]);


                if (!empty($total_chat_messages) && (int)$total_chat_messages >= (int)$daily_send_limit) {
                    $alert_message = Registry::load('strings')->maximum_sending_rate_exceeded;
                    $result['alert'] = ['message' => $alert_message];
                    return;
                }
            }

            if ($force_request || role(['permissions' => ['private_conversations' => 'send_message']])) {
                $permission['send_message'] = true;
            }
        } else {
            if ($force_request || role(['permissions' => ['private_conversations' => ['send_message', 'initiate_private_chat']]])) {
                DB::connect()->insert("private_conversations", [
                    "initiator_user_id" => $current_user_id,
                    "recipient_user_id" => $data["user_id"],
                    "created_on" => Registry::load('current_user')->time_stamp,
                    "updated_on" => Registry::load('current_user')->time_stamp,
                ]);
                $conversation_id = DB::connect()->id();
                $permission['send_message'] = true;
            }
        }
    }

    if ($force_request || Registry::load('settings')->gif_search_engine !== 'disable' && role(['permissions' => ['private_conversations' => 'attach_gifs']])) {
        $permission['attach_gifs'] = true;
    }

    if ($force_request || role(['permissions' => ['private_conversations' => 'attach_stickers']])) {
        $permission['attach_stickers'] = true;
    }

    if ($force_request || role(['permissions' => ['private_conversations' => 'share_screenshot']])) {
        $permission['share_screenshot'] = true;
    }

    if ($force_request || role(['permissions' => ['private_conversations' => 'send_audio_message']])) {
        $permission['send_audio_message'] = true;
    }

    if ($force_request || role(['permissions' => ['private_conversations' => 'attach_files']])) {
        $permission['attach_files'] = true;
    }

    if ($force_request || role(['permissions' => ['private_conversations' => 'attach_from_storage']])) {
        $permission['attach_from_storage'] = true;
    }

    if ($force_request || role(['permissions' => ['private_conversations' => 'reply_messages']])) {
        $permission['reply_messages'] = true;
    }

    if ($force_request || role(['permissions' => ['private_conversations' => 'generate_link_preview']])) {
        $permission['generate_link_preview'] = true;
    }

    if ($force_request || role(['permissions' => ['private_conversations' => 'allow_sharing_links']])) {
        $permission['allow_sharing_links'] = true;
    }
}


if (isset($data['group_id']) && !empty($data['group_id'])) {
    if ($force_request || role(['permissions' => ['groups' => 'super_privileges']])) {
        $super_privileges = true;
    }

    $columns = $join = $where = null;
    $columns = [
        'groups.name(group_name)', 'group_roles.group_role_attribute', 'groups.suspended',
        'groups.slug', 'groups.secret_group', 'groups.password', 'groups.suspended', 'groups.updated_on',
        'group_members.group_role_id', 'group_members.banned_till', 'groups.who_all_can_send_messages',
    ];

    $join["[>]group_members"] = ["groups.group_id" => "group_id", "AND" => ["user_id" => Registry::load('current_user')->id]];
    $join["[>]group_roles"] = ["group_members.group_role_id" => "group_role_id"];
    $where["groups.group_id"] = $data["group_id"];
    $where["LIMIT"] = 1;
    $group_info = DB::connect()->select('groups', $join, $columns, $where);

    if (isset($group_info[0])) {
        $group_info = $group_info[0];
    } else {
        return;
    }

    if (!$force_request && !empty($gap_between_messages)) {
        $recent_message_time_stamp = DB::connect()->select('group_messages', ['created_on'], [
            'user_id' => Registry::load('current_user')->id,
            'system_message[!]' => 1,
            'ORDER' => ['group_message_id' => 'DESC'],
            'LIMIT' => 1
        ]);


        if (isset($recent_message_time_stamp[0])) {
            $recent_message_time_stamp = $recent_message_time_stamp[0]['created_on'];

            $to_time = strtotime($recent_message_time_stamp);
            $from_time = strtotime("now");
            $time_difference = round(abs($to_time - $from_time), 2);

            if ($time_difference < $gap_between_messages) {
                $wait_for = $gap_between_messages-$time_difference;

                $alert_message = Registry::load('strings')->flood_control_error_message.' '.$wait_for.' '.Registry::load('strings')->seconds;
                $result['alert'] = ['message' => $alert_message];
                return;
            }
        }
    }

    $daily_send_limit = role(['find' => 'daily_send_limit_group_messages']);

    if (!empty($daily_send_limit)) {
        $daily_send_limit = filter_var($daily_send_limit, FILTER_SANITIZE_NUMBER_INT);
    }

    if (!$force_request && !empty($daily_send_limit)) {
        $total_chat_messages = DB::connect()->count('group_messages', ['created_on'], [
            'user_id' => Registry::load('current_user')->id,
            'system_message[!]' => 1,
            "created_on[~]" => date('Y-m-d')
        ]);


        if (!empty($total_chat_messages) && (int)$total_chat_messages >= (int)$daily_send_limit) {
            $alert_message = Registry::load('strings')->maximum_sending_rate_exceeded;
            $result['alert'] = ['message' => $alert_message];
            return;
        }
    }

    if (!$super_privileges && isset($group_info['suspended']) && !empty($group_info['suspended'])) {
        $result['refresh'] = true;
        return;
    }

    if ($force_request || role(['permissions' => ['groups' => 'send_message']])) {
        if ($super_privileges || isset($group_info['group_role_id']) && !empty($group_info['group_role_id'])) {
            if ($super_privileges || role(['permissions' => ['messages' => 'send_message'], 'group_role_id' => $group_info['group_role_id']])) {
                if ($super_privileges || !empty($group_info['who_all_can_send_messages'])) {
                    if ($super_privileges || $group_info['who_all_can_send_messages'] === 'all') {
                        $permission['send_message'] = true;
                    } else {
                        $who_all_can_send_messages = json_decode($group_info['who_all_can_send_messages']);
                        if (!empty($who_all_can_send_messages)) {
                            if (in_array($group_info['group_role_id'], $who_all_can_send_messages)) {
                                $permission['send_message'] = true;
                            }
                        }
                    }
                }
            }
        }
    }

    if ($force_request || role(['permissions' => ['groups' => 'send_as_another_user']])) {
        $permission['send_as_another_user'] = true;
    }

    if ($force_request || Registry::load('settings')->gif_search_engine !== 'disable' && role(['permissions' => ['groups' => 'attach_gifs']])) {
        if ($super_privileges || isset($group_info['group_role_id']) && !empty($group_info['group_role_id'])) {
            if ($super_privileges || role(['permissions' => ['messages' => 'attach_gifs'], 'group_role_id' => $group_info['group_role_id']])) {
                $permission['attach_gifs'] = true;
            }
        }
    }

    if ($force_request || role(['permissions' => ['groups' => 'send_audio_message']])) {
        if ($super_privileges || isset($group_info['group_role_id']) && !empty($group_info['group_role_id'])) {
            if ($super_privileges || role(['permissions' => ['messages' => 'send_audio_message'], 'group_role_id' => $group_info['group_role_id']])) {
                $permission['send_audio_message'] = true;
            }
        }
    }

    if ($force_request || role(['permissions' => ['groups' => 'attach_stickers']])) {
        if ($super_privileges || isset($group_info['group_role_id']) && !empty($group_info['group_role_id'])) {
            if ($super_privileges || role(['permissions' => ['messages' => 'attach_stickers'], 'group_role_id' => $group_info['group_role_id']])) {
                $permission['attach_stickers'] = true;
            }
        }
    }

    if ($force_request || role(['permissions' => ['groups' => 'attach_files']])) {
        if ($super_privileges || isset($group_info['group_role_id']) && !empty($group_info['group_role_id'])) {
            if ($super_privileges || role(['permissions' => ['messages' => 'attach_files'], 'group_role_id' => $group_info['group_role_id']])) {
                $permission['attach_files'] = true;
            }
        }
    }

    if ($force_request || role(['permissions' => ['groups' => 'attach_from_storage']])) {
        if ($super_privileges || isset($group_info['group_role_id']) && !empty($group_info['group_role_id'])) {
            if ($super_privileges || role(['permissions' => ['messages' => 'attach_from_storage'], 'group_role_id' => $group_info['group_role_id']])) {
                $permission['attach_from_storage'] = true;
            }
        }
    }

    if ($force_request || role(['permissions' => ['groups' => 'share_screenshot']])) {
        if ($super_privileges || isset($group_info['group_role_id']) && !empty($group_info['group_role_id'])) {
            if ($super_privileges || role(['permissions' => ['messages' => 'share_screenshot'], 'group_role_id' => $group_info['group_role_id']])) {
                $permission['share_screenshot'] = true;
            }
        }
    }

    if ($force_request || role(['permissions' => ['groups' => 'mention_users']])) {
        if ($super_privileges || isset($group_info['group_role_id']) && !empty($group_info['group_role_id'])) {
            if ($super_privileges || role(['permissions' => ['messages' => 'mention_users'], 'group_role_id' => $group_info['group_role_id']])) {
                $permission['mention_users'] = true;
            }
        }
    }

    if ($force_request || role(['permissions' => ['groups' => 'reply_messages']])) {
        if ($super_privileges || isset($group_info['group_role_id']) && !empty($group_info['group_role_id'])) {
            if ($super_privileges || role(['permissions' => ['messages' => 'reply_messages'], 'group_role_id' => $group_info['group_role_id']])) {
                $permission['reply_messages'] = true;
            }
        }
    }

    if ($force_request || role(['permissions' => ['groups' => 'allow_sharing_links']])) {
        if ($super_privileges || isset($group_info['group_role_id']) && !empty($group_info['group_role_id'])) {
            if ($super_privileges || role(['permissions' => ['messages' => 'allow_sharing_links'], 'group_role_id' => $group_info['group_role_id']])) {
                $permission['allow_sharing_links'] = true;
            }
        }
    }

    if ($force_request || role(['permissions' => ['groups' => 'generate_link_preview']])) {
        if ($super_privileges || isset($group_info['group_role_id']) && !empty($group_info['group_role_id'])) {
            if ($super_privileges || role(['permissions' => ['messages' => 'generate_link_preview'], 'group_role_id' => $group_info['group_role_id']])) {
                $permission['generate_link_preview'] = true;
            }
        }
    }

    if (isset($data['send_as_user_id']) && $permission['send_as_another_user']) {
        $data['send_as_user_id'] = filter_var($data['send_as_user_id'], FILTER_SANITIZE_NUMBER_INT);
        if (!empty($data['send_as_user_id'])) {
            $current_user_id = $data['send_as_user_id'];
        }
    }
}

if (!$permission['send_message']) {
    $result['refresh'] = true;
    return;
}

if ($permission['send_message']) {
    if (isset($data['message']) && !empty($data['message'])) {
        $create = true;
    }

    if (isset($data['sticker']) && isset($data['sticker_pack']) && $permission['attach_stickers']) {
        include('fns/filters/load.php');

        $data["sticker_pack"] = sanitize_filename($data['sticker_pack']);
        $data["sticker"] = sanitize_filename($data['sticker']);

        if (!empty($data['sticker']) && !empty($data['sticker_pack'])) {
            $sticker_location = 'assets/files/stickers/'.$data["sticker_pack"].'/'.$data["sticker"];

            if (file_exists($sticker_location)) {
                $attachments = ['sticker' => ['sticker' => $sticker_location]];
                $create = true;
                $skip_message = true;
                $empty_message = true;
            }
        }
    } elseif (isset($data['screenshot']) && $permission['share_screenshot']) {
        include('fns/filters/load.php');
        include('fns/files/load.php');

        $screenshot = str_replace('data:image/png;base64,', '', $data['screenshot']);
        $screenshot = str_replace(' ', '+', $screenshot);
        $screenshot = base64_decode($screenshot);

        $validate_screenshot = @imagecreatefromstring($screenshot);

        if (!$validate_screenshot) {
            return false;
        }

        $storage_location = 'assets/files/storage/'.$current_user_id.'/files/';
        $thumbnails_folder = 'assets/files/storage/'.$current_user_id.'/thumbnails/';

        if (!file_exists($storage_location)) {
            mkdir($storage_location, 0755, true);
        }

        if (!file_exists($thumbnails_folder)) {
            mkdir($thumbnails_folder, 0755, true);
        }

        $screenshot_identifier = random_string(['length' => 6]).Registry::load('config')->file_seperator;
        $screenshot_identifier .= 'Screenshot.png';
        $location = 'assets/files/storage/'.$current_user_id.'/files/'.$screenshot_identifier;
        $thumbnail_location = 'assets/files/storage/'.$current_user_id.'/thumbnails/'.$screenshot_identifier;
        file_put_contents($location, $screenshot);

        $resize = [
            'resize' => $location,
            'width' => 250,
            'crop' => true,
            'real_path' => true,
            'saveas' => $thumbnail_location
        ];

        if (files('resize_img', $resize)) {
            $attachments = ['screenshot' => ['screenshot' => $location, 'thumbnail' => $thumbnail_location]];
            $create = true;
            $skip_message = true;
            $empty_message = true;
        } else {
            return false;
        }
    } elseif (isset($data['share_file']) && !empty($data['share_file']) && $permission['attach_from_storage']) {
        include('fns/filters/load.php');
        include('fns/files/load.php');

        $storage_user_id = Registry::load('current_user')->id;
        $data["share_file"] = sanitize_filename($data['share_file']);

        if (!empty($data['share_file'])) {
            $file_location = 'assets/files/storage/'.$storage_user_id.'/files/'.$data["share_file"];

            if (file_exists($file_location)) {
                $file_name = basename($data["share_file"]);
                $file_name = explode(Registry::load('config')->file_seperator, $file_name, 2);
                $file_mime_type = mime_content_type($file_location);

                $shared_file_type = 'other_files';
                $audio_file_formats = ['audio/wav', 'audio/mpeg', 'audio/mp4', 'audio/webm', 'audio/ogg', 'audio/x-wav'];
                $image_file_formats = ['image/jpeg', 'image/png', 'image/x-png', 'image/gif', 'image/bmp', 'image/x-ms-bmp'];
                $video_file_formats = ['video/3gpp', 'video/mp4', 'video/mpeg', 'video/ogg', 'video/quicktime', 'video/webm', 'video/x-m4v',
                    'video/ms-asf', 'video/x-ms-wmv', 'video/x-msvideo'];

                if (in_array($file_mime_type, $image_file_formats)) {
                    $shared_file_type = 'image_files';
                } elseif (in_array($file_mime_type, $video_file_formats)) {
                    $shared_file_type = 'video_files';
                } elseif (in_array($file_mime_type, $audio_file_formats)) {
                    $shared_file_type = 'audio_files';
                }

                if (isset($file_name[1])) {
                    $file_name = $file_name[1];
                } else {
                    $file_name = $file_name[0];
                }

                $attachments = array();
                $attachments[$shared_file_type][0]['name'] = $file_name;
                $attachments[$shared_file_type][0]['file'] = $file_location;
                $attachments[$shared_file_type][0]['file_type'] = $file_mime_type;
                $attachments[$shared_file_type][0]['file_size'] = files('getsize', ['getsize_of' => $file_location, 'real_path' => true]);

                if (in_array($file_mime_type, $image_file_formats)) {
                    $attachments[$shared_file_type][0]['thumbnail'] = 'assets/files/storage/'.$storage_user_id.'/thumbnails/'.$data["share_file"];
                } elseif (in_array($file_mime_type, $video_file_formats)) {
                    $attachments[$shared_file_type][0]['thumbnail'] = 'assets/files/storage/'.$storage_user_id.'/thumbnails/'.pathinfo($data["share_file"], PATHINFO_FILENAME).'.jpg';
                }

                if (strlen($file_name) > 15) {
                    $attachments[$shared_file_type][0]['trimmed_name'] = trim(mb_substr($file_name, 0, 8)).'...'.mb_substr($file_name, -8);
                } else {
                    $attachments[$shared_file_type][0]['trimmed_name'] = $file_name;
                }

                $create = true;
                $skip_message = true;
                $empty_message = true;
            }
        }
    } elseif (isset($_FILES['audio_message']) && $permission['send_audio_message']) {
        include 'fns/filters/load.php';
        include 'fns/files/load.php';

        if (isset($data['group_id'])) {
            $location = 'assets/files/audio_messages/group_chat/'.$data['group_id'].'/';
        } elseif (isset($data['user_id'])) {
            $location = 'assets/files/audio_messages/private_chat/'.$conversation_id.'/';
        }
        $filename = 'audio_message.webm';
        $upload_info = [
            'upload' => 'audio_message',
            'append_random_string' => true,
            'folder' => $location,
            'saveas' => $filename,
            'create_folder' => true,
            'real_path' => true,
            'only_allow' => ['audio/wav', 'audio/mpeg', 'audio/mp4', 'video/mp4', 'video/webm', 'audio/webm', 'audio/ogg', 'audio/x-wav']
        ];

        $audio_message = files('upload', $upload_info);

        if ($audio_message['result']) {
            $mime_type = $audio_message['files'][0]['file_type'];
            $audio_message = $audio_message['files'][0]['file'];

            if ($mime_type !== 'audio/mpeg') {
                if (isset(Registry::load('settings')->ffmpeg) && Registry::load('settings')->ffmpeg === 'enable') {
                    include('fns/FFMpeg/load.php');

                    $save_in = $location.pathinfo($audio_message, PATHINFO_FILENAME).'.mp3';
                    $audio = $ffmpeg->open($audio_message);
                    $audio->save(new FFMpeg\Format\Audio\Mp3(), $save_in);

                    unlink($audio_message);

                    $audio_message = $save_in;
                    $mime_type = 'audio/mpeg';
                }
            }

            $attachments = ['audio_message' => ['audio_message' => $audio_message, 'mime_type' => $mime_type]];
            $create = true;
            $skip_message = true;
            $empty_message = true;
        }
    } elseif (isset($data['gif_url']) && Registry::load('settings')->gif_search_engine !== 'disable' && $permission['attach_gifs']) {
        $data['gif_url'] = htmlspecialchars($data['gif_url']);
        $data['gif_url'] = filter_var($data['gif_url'], FILTER_SANITIZE_URL);
        $validate_gif_url = false;

        if (filter_var($data['gif_url'], FILTER_VALIDATE_URL) !== false) {
            $gif_url = parse_url($data['gif_url']);
            $gif_hostname = implode('.', array_slice(explode('.', $gif_url['host']), -2));
            if ($gif_hostname === 'tenor.com' || $gif_hostname === 'gfycat.com' || $gif_hostname === 'giphy.com') {
                if ($validate_gif_url) {
                    $gif_size = @getimagesize($data['gif_url']);
                    if (!isset($gif_size['mime']) || strtolower(substr($gif_size['mime'], 0, 5)) !== 'image') {
                        return false;
                    }
                }
                $attachments = ['gif' => ['gif_url' => $data['gif_url']]];
                $create = true;
                $empty_message = true;
            }
        }
    } elseif (isset($_FILES['file_attachments']) && $permission['attach_files']) {
        include 'fns/upload/load.php';
        $upload_files = upload(['upload' => 'storage', 'user_id' => $current_user_id, 'return' => true], ['force_request' => $force_request]);

        if ($upload_files['success']) {
            if (!empty($upload_files['files'])) {
                $attachments = $upload_files['files'];
                $uploaded_files = true;
                $empty_message = true;
                $create = true;
            }
        }
    }

    if ($create) {
        if (!$skip_message) {
            if (!empty(Registry::load('settings')->maximum_message_length)) {
                $total_characters = mb_strlen(strip_tags(trim($data['message'])));
                if ($total_characters > Registry::load('settings')->maximum_message_length) {
                    $data['message'] = mb_substr($data['message'], 0, Registry::load('settings')->maximum_message_length, 'utf8');
                }
            }

            if (!empty($data['message']) && !$permission['allow_sharing_links']) {
                $link_pattern = '#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#';
                $data['message'] = preg_replace($link_pattern, '', $data['message']);
                $data['message'] = preg_replace('/<a href=\"(.*?)\">(.*?)<\/a>/', "\\2", $data['message']);
                $data['message'] = str_replace("&nbsp;", '', $data['message']);
                $data['message'] = trim($data['message']);
            } else {
                $email_pattern = '"<a[^>]+>.+?</a>(*SKIP)(*FAIL)|(\S+@\S+\.\S+?)(?=[.,!?]?(\s|$))"';
                $data['message'] = $urlHighlight->highlightUrls($data['message']);

            }

            if (!empty($data['message']) && $ascii_emoji) {
                $emoticons = [
                    '#(?<!\S)('.preg_quote(':)', "#").')(?!\S)#iu',
                    '#(?<!\S)('.preg_quote(';)', "#").')(?!\S)#iu',
                    '#(?<!\S)('.preg_quote(':(', "#").')(?!\S)#iu',
                    '#(?<!\S)('.preg_quote(':D', "#").')(?!\S)#iu',
                    '#(?<!\S)('.preg_quote(':P', "#").')(?!\S)#iu',
                    '#(?<!\S)('.preg_quote('XD', "#").')(?!\S)#iu',
                    '#(?<!\S)('.preg_quote('&lt;3', "#").')(?!\S)#iu',
                ];

                $replacements = [
                    "<span class='emoji_icon emoji-slightly_smiling_face'>&nbsp;</span>",
                    "<span class='emoji_icon emoji-wink'>&nbsp;</span>",
                    "<span class='emoji_icon emoji-frowning'>&nbsp;</span>",
                    "<span class='emoji_icon emoji-smile'>&nbsp;</span>",
                    "<span class='emoji_icon emoji-stuck_out_tongue_winking_eye'>&nbsp;</span>",
                    "<span class='emoji_icon emoji-joy'>&nbsp;</span>",
                    "<span class='emoji_icon emoji-heart'>&nbsp;</span>",
                ];
                $data['message'] = preg_replace($emoticons, $replacements, $data['message']);
            }

            if (!empty($data['message'])) {
                $data['message'] = preg_replace('/<([^>\s]+)[^>]*>(?:\s*(?:<br>|<br\/|<br \/>)\s*)*<\/\1>/im', '', $data['message']);
                $data['message'] = preg_replace('/(?:\s*<br[^>]*>\s*){3,}/s', "<br><br>", $data['message']);
                $data['message'] = preg_replace('#(\s*<br\s*/?>)*\s*$#i', '', $data['message']);
            }

            $replace_phone_number = false;

            if ($replace_phone_number && !empty($data['message'])) {
                $phone_number_pattern = '/(\+\d+)?\s*(\(\d+\))?([\s-]?\d+)+/';

                preg_match_all($phone_number_pattern, $data['message'], $phone_numbers);

                if (isset($phone_numbers[0])) {
                    foreach ($phone_numbers[0] as $phone_number) {
                        $phone_number = trim($phone_number);
                        $replace_with = '<a href="tel:'.$phone_number.'">'.$phone_number.'</a>';
                        $data['message'] = str_replace($phone_number, $replace_with, $data['message']);
                    }
                }
            }

            if (!empty($data['message'])) {
                $regex = '#<img.+?class="([^"]*)".*?/?>#i';
                $replace = '<span class="$1">&nbsp;</span>';
                $data['message'] = preg_replace($regex, $replace, $data['message']);
            }

            if (empty($data['message']) && !$empty_message) {
                return;
            }


            include('fns/HTMLPurifier/load.php');
            $allowed_tags = 'p,span[class],b,em,i,u,strong,s,';
            $allowed_tags .= 'a[href],ol,ul,li,br';

            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', $allowed_tags);
            $config->set('Attr.AllowedClasses', array());
            $config->set('HTML.Nofollow', true);
            $config->set('HTML.TargetBlank', true);
            $config->set('AutoFormat.RemoveEmpty', true);

            $define = $config->getHTMLDefinition(true);
            $define->addAttribute('span', 'class', new CustomClassDef(array('emoji_icon'), array('emoji-')));

            $purifier = new HTMLPurifier($config);

            $message = $purifier->purify(trim($data['message']));

            if (isset($attachments[0]) && $permission['generate_link_preview']) {

                $links = $urlHighlight->getUrls($message);

                if (isset($links[0])) {
                    include('fns/url_metadata/load.php');
                    $url_meta_data = url_metadata($links[0]);
                    if ($url_meta_data['success']) {
                        unset($url_meta_data['success']);
                        $attachments = ['url_meta' => $url_meta_data];
                    }
                }
            }

            if (!empty($message)) {
                if (Registry::load('settings')->profanity_filter !== 'disable') {
                    $safe_mode = true;

                    if (Registry::load('settings')->profanity_filter === 'strict_mode') {
                        $safe_mode = false;
                    }

                    $censor = new CensorWords();
                    $message = $censor->censorString($message, $safe_mode);
                    $message = $message['clean'];
                }

                if (isset($data['group_id']) && !empty($data['group_id']) && $permission['mention_users']) {
                    if (!empty($message)) {
                        $advanced_mention_system = true;

                        $mention_pattern = "/\@\[[^\]]*\]/";

                        if ($advanced_mention_system) {
                            $mention_pattern = "/(\@\[[^\]]*\])|(@\w+)/";
                        }

                        $mentions = [];
                        preg_match_all($mention_pattern, $message, $mention_matches);
                        $mention_matches = $mention_matches[0];

                        foreach ($mention_matches as $mention) {
                            $mention = str_replace(array('\'', '"', ',', '@', ';', '(', ')', '[', ']', '<', '>', '{', '}'), '', $mention);
                            $mentions[] = trim($mention);
                        }

                        if (!empty($mentions)) {
                            $db_columns = ['site_users.user_id', 'site_users.username', 'group_members.group_member_id'];
                            $notify_users = [];

                            $db_join["[>]group_members"] = [
                                "site_users.user_id" => "user_id",
                                "AND" => ["group_id" => $data['group_id']]
                            ];

                            $db_join["[>]site_users_settings"] = ["site_users.user_id" => "user_id"];

                            $db_where = ['site_users.username' => $mentions, 'site_users_settings.deactivated' => 0, 'LIMIT' => 10];

                            $mentioned_users = DB::connect()->select("site_users", $db_join, $db_columns, $db_where);

                            foreach ($mentioned_users as $mention) {
                                if (isset($mention['group_member_id']) && !empty($mention['group_member_id'])) {
                                    $replace_with = '<span class="get_info mention" user_id="'.$mention['user_id'].'">@'.$mention['username'].'</span>';

                                    if ($advanced_mention_system) {
                                        $message = str_replace('@'.$mention['username'], $replace_with, $message);
                                    }

                                    $message = str_replace('@['.$mention['username'].']', $replace_with, $message);
                                }
                            }
                        }
                    }
                }

            }

            if (isset($data['attach_message']) && !empty($data['attach_message']) && $permission['reply_messages']) {
                $data['attach_message'] = filter_var($data['attach_message'], FILTER_SANITIZE_NUMBER_INT);
                if (!empty($data['attach_message'])) {
                    if (isset($data['group_id'])) {
                        $verify_attached_message = DB::connect()->select(
                            "group_messages",
                            ["group_message_id", "user_id"],
                            ['group_message_id' => $data['attach_message'], 'group_id' => $data['group_id']]
                        );
                    } elseif (isset($data['user_id'])) {
                        $verify_attached_message = DB::connect()->select(
                            "private_chat_messages",
                            ["private_chat_message_id", "user_id"],
                            ['private_chat_message_id' => $data['attach_message'], 'private_conversation_id' => $conversation_id]
                        );
                    }


                    if (isset($verify_attached_message[0])) {
                        $parent_message_id = $data['attach_message'];
                    }
                }
            }
        }

        $message_criteria = true;
        $message = preg_replace('/^\p{Z}+|\p{Z}+$/u', '', $message);
        $total_characters = mb_strlen(strip_tags($message));

        if (empty(Registry::load('settings')->minimum_message_length)) {
            Registry::load('settings')->minimum_message_length = 1;
        }

        if ($total_characters < Registry::load('settings')->minimum_message_length) {
            $message_criteria = false;
        }

        if ($empty_message || $message_criteria) {
            $loop_count = 1;

            foreach ($attachments as $index => $attachment) {
                if (!empty($index)) {
                    $attachment_type = $index;
                } else {
                    $attachment_type = '';
                }

                if (!empty($attachment)) {
                    if ($uploaded_files) {
                        $attachment = array_values($attachment);
                    }

                    $attachment = json_encode($attachment);
                }

                if ($loop_count > 1) {
                    $message = '';
                }

                if (!isset($data['message'])) {
                    $data['message'] = '';
                }

                if (isset($data['group_id'])) {
                    DB::connect()->insert("group_messages", [
                        "original_message" => $data['message'],
                        "filtered_message" => $message,
                        "group_id" => $data['group_id'],
                        "user_id" => $current_user_id,
                        "parent_message_id" => $parent_message_id,
                        "attachment_type" => $attachment_type,
                        "attachments" => $attachment,
                        "link_preview" => $link_preview,
                        "created_on" => Registry::load('current_user')->time_stamp,
                        "updated_on" => Registry::load('current_user')->time_stamp,
                    ]);
                } elseif (isset($data['user_id'])) {
                    DB::connect()->insert("private_chat_messages", [
                        "original_message" => $data['message'],
                        "filtered_message" => $message,
                        "private_conversation_id" => $conversation_id,
                        "user_id" => $current_user_id,
                        "parent_message_id" => $parent_message_id,
                        "attachment_type" => $attachment_type,
                        "attachments" => $attachment,
                        "link_preview" => $link_preview,
                        "created_on" => Registry::load('current_user')->time_stamp,
                        "updated_on" => Registry::load('current_user')->time_stamp,
                    ]);
                }

                if ((int)$loop_count === 1) {
                    if (!DB::connect()->error) {
                        $message_id = DB::connect()->id();
                    }
                }

                $loop_count = $loop_count+1;
            }

            if (!DB::connect()->error) {
                if (!isset($message_id)) {
                    $message_id = DB::connect()->id();
                }


                if (isset($data['group_id'])) {
                    $update_time_stamp = date('Y-m-d H:i:s', strtotime("2022-01-01"));
                    DB::connect()->update(
                        "typing_status",
                        ["updated_on" => $update_time_stamp],
                        ["group_id" => $data['group_id'], "user_id" => $current_user_id]
                    );
                } elseif (isset($data['user_id']) && isset($conversation_id)) {
                    $update_time_stamp = date('Y-m-d H:i:s', strtotime("2022-01-01"));
                    DB::connect()->update(
                        "typing_status",
                        ["updated_on" => $update_time_stamp],
                        ["private_conversation_id" => $conversation_id, "user_id" => $current_user_id]
                    );
                }

                if (isset($verify_attached_message[0])) {
                    if (isset(Registry::load('settings')->site_notifications->on_reply_group_messages)) {
                        if (isset($data['group_id']) && (int)$verify_attached_message[0]['user_id'] !== (int)$current_user_id) {
                            DB::connect()->insert("site_notifications", [
                                "user_id" => $verify_attached_message[0]['user_id'],
                                "notification_type" => 'replied_group_message',
                                "related_group_id" => $data['group_id'],
                                "related_message_id" => $message_id,
                                "related_user_id" => $current_user_id,
                                "created_on" => Registry::load('current_user')->time_stamp,
                                "updated_on" => Registry::load('current_user')->time_stamp,
                            ]);
                        }
                    }

                    if (isset(Registry::load('settings')->send_push_notification->on_reply_group_messages)) {
                        if (isset($data['group_id']) && (int)$verify_attached_message[0]['user_id'] !== (int)$current_user_id) {
                            include('fns/push_notification/load.php');

                            $web_push = [
                                'user_id' => $verify_attached_message[0]['user_id'],
                                'title' => Registry::load('strings')->someone,
                                'message' => Registry::load('strings')->web_push_sent_reply_message,
                            ];

                            if (isset(Registry::load('current_user')->name)) {
                                $web_push['title'] = Registry::load('current_user')->name;
                            }

                            if (!empty($message)) {
                                $web_push_message = preg_replace('/<span\b[^>]*>(.*?)<\/span>/i', '', $message);
                                $web_push_message = strip_tags($web_push_message);

                                if (!empty($web_push_message)) {
                                    $web_push['message'] = $web_push_message;
                                }
                            }

                            push_notification($web_push);
                        }
                    }
                }
                if (isset($data['group_id'])) {
                    if (isset($mentioned_users)) {
                        if (isset(Registry::load('settings')->site_notifications->on_user_mention_group_chat) || isset(Registry::load('settings')->send_push_notification->on_user_mention_group_chat)) {
                            $add_site_notification = array();
                            $notify_user_ids = array();
                            foreach ($mentioned_users as $mention) {
                                if (isset($mention['user_id']) && !empty($mention['user_id'])) {
                                    if ((int)$mention['user_id'] !== (int)$current_user_id) {
                                        $notify_user_ids[] = $mention['user_id'];
                                        $add_site_notification[] = [
                                            "user_id" => $mention['user_id'],
                                            "notification_type" => 'mentioned_group_chat',
                                            "related_group_id" => $data['group_id'],
                                            "related_message_id" => $message_id,
                                            "related_user_id" => $current_user_id,
                                            "created_on" => Registry::load('current_user')->time_stamp,
                                            "updated_on" => Registry::load('current_user')->time_stamp,
                                        ];
                                    }
                                }
                            }

                            if (isset(Registry::load('settings')->site_notifications->on_user_mention_group_chat) && !empty($add_site_notification)) {
                                DB::connect()->insert("site_notifications", $add_site_notification);
                            }

                            if (isset(Registry::load('settings')->send_push_notification->on_user_mention_group_chat) && !empty($notify_user_ids)) {
                                include('fns/push_notification/load.php');

                                $web_push = [
                                    'user_id' => $notify_user_ids,
                                    'title' => Registry::load('strings')->someone,
                                    'message' => Registry::load('strings')->web_push_mentioned_user_message,
                                ];

                                if (isset(Registry::load('current_user')->name)) {
                                    $web_push['title'] = Registry::load('current_user')->name;
                                }
                                if (!empty($message)) {
                                    $web_push_message = preg_replace('/<span\b[^>]*>(.*?)<\/span>/i', '', $message);
                                    $web_push_message = strip_tags($web_push_message);

                                    if (!empty($web_push_message)) {
                                        $web_push['message'] = $web_push_message;
                                    }
                                }

                                push_notification($web_push);
                            }
                        }
                    }
                }

                if (isset($data['group_id'])) {
                    DB::connect()->update("groups", ["updated_on" => Registry::load('current_user')->time_stamp], ['group_id' => $data['group_id']]);
                } elseif (isset($data['user_id'])) {
                    DB::connect()->update("private_conversations", ["updated_on" => Registry::load('current_user')->time_stamp], ['private_conversation_id' => $conversation_id]);

                    if (isset(Registry::load('settings')->send_push_notification->on_private_message)) {
                        if ((int)$user_info['online_status'] !== 1 && $user_info['site_role_attribute'] !== 'banned_users') {
                            include('fns/push_notification/load.php');

                            $web_push = [
                                'user_id' => $data['user_id'],
                                'title' => Registry::load('strings')->someone,
                                'message' => Registry::load('strings')->web_push_new_pm_message,
                            ];

                            if (isset(Registry::load('current_user')->name)) {
                                $web_push['title'] = Registry::load('current_user')->name;
                            }
                            if (!empty($message)) {
                                $web_push_message = preg_replace('/<span\b[^>]*>(.*?)<\/span>/i', '', $message);
                                $web_push_message = strip_tags($web_push_message);

                                if (!empty($web_push_message)) {
                                    $web_push['message'] = $web_push_message;
                                }
                            }

                            push_notification($web_push);
                        }
                    }
                }

                include('fns/load/load.php');
                $result['success'] = true;

                if (!$api_request) {
                    if (isset($data['group_id'])) {
                        $result['message'] = load(['load' => 'group_messages', 'group_id' => $data['group_id'], 'return' => true, 'message_id_from' => $message_id]);
                    } elseif (isset($data['user_id'])) {
                        $result['message'] = load(['load' => 'private_chat_messages', 'user_id' => $data['user_id'], 'return' => true, 'message_id_from' => $message_id]);
                    }
                }
            } else {
                $result['error_message'] = Registry::load('strings')->went_wrong;
                $result['error_key'] = 'something_went_wrong';
            }
        }
    }
}