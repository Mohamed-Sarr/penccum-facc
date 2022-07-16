<?php

if (role(['permissions' => ['super_privileges' => 'cron_jobs']])) {

    $todo = 'add';

    $form['loaded'] = new stdClass();
    $form['fields'] = new stdClass();

    if (isset($load["cron_job_id"])) {

        $load["cron_job_id"] = filter_var($load["cron_job_id"], FILTER_SANITIZE_NUMBER_INT);

        if (empty($load["cron_job_id"])) {
            return;
        }

        $columns = [
            'cron_jobs.cron_job_id', 'cron_jobs.cron_job', 'cron_jobs.cron_job_parameters',
            'cron_jobs.cron_job_access_code',
        ];

        $where["cron_jobs.cron_job_id"] = $load["cron_job_id"];
        $where["LIMIT"] = 1;

        $cron_job = DB::connect()->select('cron_jobs', $columns, $where);

        if (isset($cron_job[0])) {
            $cron_job = $cron_job[0];
        } else {
            return;
        }

        $todo = 'update';
        $form['loaded']->title = Registry::load('strings')->edit_cron_job;
        $form['loaded']->button = Registry::load('strings')->update;
    } else {
        $form['loaded']->title = Registry::load('strings')->add_cron_job;
        $form['loaded']->button = Registry::load('strings')->add;
    }

    $form['fields']->$todo = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "cron_jobs"
    ];

    if (isset($load["cron_job_id"])) {

        $form['fields']->cron_job_id = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load["cron_job_id"]
        ];

        $form['fields']->cron_job_identifier = [
            "title" => Registry::load('strings')->cron_job, "tag" => 'input', "type" => 'text',
            "attributes" => ['disabled' => 'disabled'], "class" => 'field', "value" => 'CRON#'.$load["cron_job_id"],
        ];

        $cron_job_url = Registry::load('config')->site_url;
        $cron_job_url .= 'cron_job/'.$cron_job['cron_job_id'].'/'.$cron_job['cron_job_access_code'].'/';

        $form['fields']->cron_job_url = [
            "title" => Registry::load('strings')->cron_job_url, "tag" => 'input', "type" => 'text',
            "attributes" => ['class' => 'copy_to_clipboard'], "class" => 'field', "value" => $cron_job_url,
        ];

        $command = 'wget -q -O - '.$cron_job_url.' >/dev/null 2>&1';

        $form['fields']->command = [
            "title" => Registry::load('strings')->command, "tag" => 'textarea',
            "attributes" => ['class' => 'copy_to_clipboard'], "class" => 'field', "value" => $command,
        ];

    }

    $form['fields']->cron_job = [
        "title" => Registry::load('strings')->cron_job, "tag" => 'select', "class" => 'field toggle_form_fields'
    ];

    $form['fields']->cron_job["attributes"] = [
        "hide_field" => "cron_job_parameters",
        "common_field" => "common_field"
    ];

    $form['fields']->cron_job["child_fields"] = [
        "delete_group_messages" => "delete_group_messages_parameters",
        "delete_private_messages" => "delete_private_messages_parameters",
        "delete_site_users" => "delete_site_users_parameters",
        "delete_user_files" => "delete_user_files_parameters",
    ];

    $form['fields']->cron_job['options'] = [
        "delete_group_messages" => Registry::load('strings')->delete_group_messages,
        "delete_private_messages" => Registry::load('strings')->delete_private_messages,
        "delete_site_users" => Registry::load('strings')->delete_site_users,
        "delete_user_files" => Registry::load('strings')->delete_user_files,
    ];


    $form['fields']->delete_shared_files = [
        "title" => Registry::load('strings')->delete_shared_files, "tag" => 'select',
        "class" => 'field cron_job_parameters delete_group_messages_parameters delete_private_messages_parameters'
    ];
    $form['fields']->delete_shared_files['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];



    $site_roles = DB::connect()->select('site_roles', ['site_roles.site_role_id', 'site_roles.string_constant'], ['site_roles.disabled' => 0]);
    $site_roles = array_column($site_roles, 'string_constant', 'site_role_id');
    array_walk($site_roles, function(&$value, $key) {
        $value = Registry::load('strings')->$value;
    });

    $form['fields']->site_role_ids = [
        "title" => Registry::load('strings')->site_roles, "tag" => 'checkbox',
        "class" => 'field cron_job_parameters delete_site_users_parameters delete_user_files_parameters'
    ];
    $form['fields']->site_role_ids['options'] = $site_roles;

    $form['fields']->delete_only_offline_users = [
        "title" => Registry::load('strings')->delete_only_offline_users, "tag" => 'select',
        "class" => 'field cron_job_parameters delete_site_users_parameters'
    ];
    $form['fields']->delete_only_offline_users['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];


    $form['fields']->delete_older_than = [
        "title" => Registry::load('strings')->delete_older_than, "tag" => 'input', "type" => "number",
        "class" => 'field cron_job_parameters common_field', "value" => 60
    ];

    $form['fields']->entries_per_call = [
        "title" => Registry::load('strings')->entries_per_call, "tag" => 'input', "type" => "number",
        "class" => 'field cron_job_parameters common_field', "value" => 25
    ];

    if (isset($load["cron_job_id"])) {
        $form['fields']->cron_job["value"] = $cron_job['cron_job'];
        $parameters = json_decode($cron_job['cron_job_parameters']);

        if (!empty($parameters)) {
            foreach ($parameters as $index => $parameter) {
                if (isset($form['fields']->$index)) {
                    $form['fields']->$index["value"] = $parameter;
                }
            }
        }

    }
}
?>