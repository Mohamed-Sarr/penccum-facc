<?php

include 'fns/filters/load.php';
include 'fns/files/load.php';

function download($download) {
    $output = array();

    if (isset($download["download"])) {
        $download["download"] = preg_replace("/[^a-zA-Z0-9_]+/", "", $download["download"]);
        $download["download"] = str_replace('template_', '', $download["download"]);
    }

    if (isset($download["download"]) && !empty($download["download"])) {
        $downloadfnfile = 'fns/download/'.$download["download"].'.php';
        if (file_exists($downloadfnfile)) {
            include($downloadfnfile);
        }
    }

    if (isset($download["return"]) && $download["return"]) {
        return $output;
    } else {
        $output = json_encode($output);
        echo $output;
    }

}

?>