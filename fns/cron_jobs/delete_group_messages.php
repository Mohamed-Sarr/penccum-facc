<?php

$parameters = json_decode($cron_job['cron_job_parameters']);

if (!empty($parameters)) {

    $entries_per_call = 25;
    $delete_older_than = 0;
    $delete_shared_files = false;
    $group_message_ids = $delete_files = array();

    if (isset($parameters->entries_per_call)) {
        $parameters->entries_per_call = filter_var($parameters->entries_per_call, FILTER_SANITIZE_NUMBER_INT);
        if (!empty($parameters->entries_per_call)) {
            $entries_per_call = $parameters->entries_per_call;
        }
    }

    if (isset($parameters->delete_older_than)) {
        $parameters->delete_older_than = filter_var($parameters->delete_older_than, FILTER_SANITIZE_NUMBER_INT);
        if (!empty($parameters->delete_older_than)) {
            $delete_older_than = $parameters->delete_older_than;
        }
    }

    if (isset($parameters->delete_shared_files) && $parameters->delete_shared_files === 'yes') {
        $delete_shared_files = true;
    }

    $columns = $where = $join = null;
    $columns = [
        'group_messages.group_message_id', 'group_messages.attachment_type', 'group_messages.attachments',
    ];

    if (!empty($delete_older_than)) {
        $delete_older_than = '-'.$delete_older_than.' minutes';

        $dateTimeObj = DateTime::createFromFormat('Y-m-d H:i:s', Registry::load('current_user')->time_stamp);
        $dateTimeObj->modify($delete_older_than);
        $delete_older_than = $dateTimeObj->format('Y-m-d H:i:s');

        $where["group_messages.created_on[<]"] = $delete_older_than;
    }

    $where["ORDER"] = ['group_messages.group_message_id' => 'ASC'];
    $where["LIMIT"] = $entries_per_call;

    $group_messages = DB::connect()->select('group_messages', $columns, $where);

    foreach ($group_messages as $group_message) {

        $group_message_ids[] = $group_message['group_message_id'];

        if (isset($group_message['attachment_type']) && isset($group_message['attachments'])) {
            $attachment_type = $group_message['attachment_type'];
            if (!empty($attachment_type) && !empty($group_message['attachments'])) {
                $attachments = json_decode($group_message['attachments']);
                if (!empty($attachments)) {

                    foreach ($attachments as $attachment_index => $attachment) {

                        if ($delete_shared_files) {
                            if (isset($attachment->file)) {
                                $delete_files[] = $attachment->file;
                            }

                            if ($attachment_index === 'screenshot' || $attachment_index === 'thumbnail') {
                                $delete_files[] = $attachments->$attachment_index;
                            }

                            if (isset($attachment->screenshot)) {
                                $delete_files[] = $attachment->screenshot;
                            }

                            if (isset($attachment->thumbnail)) {
                                $delete_files[] = $attachment->thumbnail;
                            }
                        }

                        if ($attachment_index === 'audio_message') {
                            $delete_files[] = $attachments->$attachment_index;
                        }

                        if (isset($attachment->audio_message)) {
                            $delete_files[] = $attachment->audio_message;
                        }

                    }
                }
            }
        }
    }

    $skip_files = ['assets/files/default/video_thumb.jpg', 'assets/files/default/image_thumb.jpg'];

    foreach ($delete_files as $delete_file) {
        if (!in_array($delete_file, $skip_files)) {
            if (file_exists($delete_file)) {
                unlink($delete_file);
            }
        }
    }

    if (!empty($group_message_ids)) {
        DB::connect()->delete('group_messages', ['group_message_id' => $group_message_ids]);
    }

    DB::connect()->update("cron_jobs", ["last_run_time" => Registry::load('current_user')->time_stamp], ['cron_job_id' => $cron_job['cron_job_id']]);

    $output = array();
    $output['success'] = true;

}

?>
