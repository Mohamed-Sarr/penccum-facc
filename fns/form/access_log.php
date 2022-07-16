<?php

if (role(['permissions' => ['site_users' => 'manage_user_access_logs']])) {

    $form = array();
    $form['loaded'] = new stdClass();
    $form['fields'] = new stdClass();

    if (isset($load["access_log_id"])) {

        $load["access_log_id"] = filter_var($load["access_log_id"], FILTER_SANITIZE_NUMBER_INT);

        if (!empty($load['access_log_id'])) {

            $columns = [
                'site_users_device_logs.access_log_id', 'site_users_device_logs.ip_address',
                'site_users_device_logs.user_agent', 'site_users_device_logs.created_on',
                'site_users.display_name'
            ];

            $join["[>]site_users"] = ["site_users_device_logs.user_id" => "user_id"];

            $where["site_users_device_logs.access_log_id"] = $load["access_log_id"];
            $where["LIMIT"] = 1;

            $device_log = DB::connect()->select('site_users_device_logs', $join, $columns, $where);

            if (isset($device_log[0])) {

                $device_log = $device_log[0];
                $device = unserialize($device_log['user_agent']);

                $form['loaded']->title = Registry::load('strings')->access_logs;
                $form['loaded']->button = Registry::load('strings')->delete;

                $form['fields']->access_log_id = [
                    "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load["access_log_id"]
                ];

                $form['fields']->process = [
                    "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => 'remove'
                ];

                $form['fields']->remove = [
                    "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "access_logs"
                ];

                $form['fields']->full_name = [
                    "title" => Registry::load('strings')->full_name, "tag" => 'input', "type" => "text", "class" => 'field',
                    "value" => $device_log['display_name'], "attributes" => ['disabled' => 'disabled']
                ];

                $form['fields']->ip_address = [
                    "title" => Registry::load('strings')->ip_address, "tag" => 'input', "type" => "text", "class" => 'field',
                    "value" => $device_log['ip_address'], "attributes" => ['disabled' => 'disabled']
                ];


                $access_date = array();
                $access_date['date'] = $device_log['created_on'];
                $access_date['auto_format'] = true;
                $access_date['include_time'] = true;
                $access_date['timezone'] = Registry::load('current_user')->time_zone;
                $access_date = get_date($access_date);

                $form['fields']->access_time = [
                    "title" => Registry::load('strings')->access_time, "tag" => 'input', "type" => "text", "class" => 'field',
                    "value" => $access_date['date'].' '.$access_date['time'], "attributes" => ['disabled' => 'disabled']
                ];

                $form['fields']->platform = [
                    "title" => Registry::load('strings')->platform, "tag" => 'input', "type" => "text", "class" => 'field',
                    "value" => $device['platform'], "attributes" => ['disabled' => 'disabled']
                ];

                $form['fields']->browser = [
                    "title" => Registry::load('strings')->browser, "tag" => 'input', "type" => "text", "class" => 'field',
                    "value" => $device['browser'], "attributes" => ['disabled' => 'disabled']
                ];

                $form['fields']->version = [
                    "title" => Registry::load('strings')->version, "tag" => 'input', "type" => "text", "class" => 'field',
                    "value" => $device['version'], "attributes" => ['disabled' => 'disabled']
                ];

                $form['fields']->user_agent = [
                    "title" => Registry::load('strings')->user_agent, "tag" => 'textarea',
                    "class" => 'field ', "value" => $device['user_agent']
                ];

                $form['fields']->user_agent["attributes"] = ["rows" => 6, "disabled" => true];
            }
        }
    }
}
?>