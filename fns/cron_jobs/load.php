<?php

function cron_job($data) {
    $output = array();
    $output['success'] = false;
    $cron_job = null;

    if (isset($data["cron_job_id"]) && isset($data["access_code"])) {
        $data["cron_job_id"] = filter_var($data["cron_job_id"], FILTER_SANITIZE_NUMBER_INT);
        $data['access_code'] = htmlspecialchars($data['access_code'], ENT_QUOTES, 'UTF-8');

        $columns = [
            'cron_jobs.cron_job_id', 'cron_jobs.cron_job', 'cron_jobs.cron_job_parameters',
        ];

        $where["cron_jobs.cron_job_id"] = $data["cron_job_id"];
        $where["cron_jobs.cron_job_access_code"] = $data["access_code"];
        $where["LIMIT"] = 1;

        $cron_job = DB::connect()->select('cron_jobs', $columns, $where);

        if (isset($cron_job[0])) {
            $cron_job = $cron_job[0];
        }
    }

    if (!empty($cron_job)) {
        $function_file = 'fns/cron_jobs/'.$cron_job['cron_job'].'.php';
        if (file_exists($function_file)) {
            include($function_file);
        }
    }

    if (isset($data["return"]) && $data["return"]) {
        return $output;
    } else {
        $output = json_encode($output);
        echo $output;
    }

}

?>