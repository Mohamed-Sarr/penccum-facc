<?php

include 'fns/filters/load.php';
include 'fns/files/load.php';

$parameters = json_decode($cron_job['cron_job_parameters']);

if (!empty($parameters)) {

    $entries_per_call = 25;
    $delete_older_than = 0;
    $user_ids = array();
    $site_role_ids = array();

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
            $delete_older_than = $delete_older_than*60;
        }
    }


    if (isset($parameters->site_role_ids)) {
        $parameters->site_role_ids = array_filter($parameters->site_role_ids, 'ctype_digit');
        if (!empty($parameters->site_role_ids)) {
            $site_role_ids = $parameters->site_role_ids;
        }
    }

    if (!empty($site_role_ids)) {
        $columns = $where = $join = null;
        $columns = ['site_users.user_id', 'site_users.site_role_id'];

        $where["site_users.site_role_id"] = $site_role_ids;
        $where["ORDER"] = ['site_users.user_id' => 'ASC'];
        $where["LIMIT"] = $entries_per_call;

        $site_users = DB::connect()->select('site_users', $columns, $where);

        foreach ($site_users as $site_user) {
            $user_ids[] = $site_user['user_id'];
        }
    }

    if (!empty($user_ids)) {

        foreach ($user_ids as $user_id) {

            $files_directory = 'assets/files/storage/'.$user_id.'/files/';
            $thumbnail_directory = 'assets/files/storage/'.$user_id.'/thumbnails/';

            foreach (glob($files_directory."*") as $user_file) {

                if (!empty($delete_older_than)) {
                    if (time() - filectime($user_file) > $delete_older_than) {
                        $thumbnail = $thumbnail_directory.basename($user_file);

                        if (file_exists($thumbnail)) {
                            unlink($thumbnail);
                        }

                        unlink($user_file);
                    }
                } else {
                    
                    $thumbnail = $thumbnail_directory.basename($user_file);

                    if (file_exists($thumbnail)) {
                        unlink($thumbnail);
                    }

                    unlink($user_file);
                }
            }
        }
    }

    DB::connect()->update("cron_jobs", ["last_run_time" => Registry::load('current_user')->time_stamp], ['cron_job_id' => $cron_job['cron_job_id']]);

    $output = array();
    $output['success'] = true;

}

?>