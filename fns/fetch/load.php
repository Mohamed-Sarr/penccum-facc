<?php

function fetch($fetch) {

    $output = array();

    if (isset($fetch["fetch"])) {
        $fetch["fetch"] = preg_replace("/[^a-zA-Z0-9_]+/", "", $fetch["fetch"]);
    }

    if (isset($fetch["fetch"]) && !empty($fetch["fetch"])) {
        $fetchfnfile = 'fns/fetch/'.$fetch["fetch"].'.php';
        if (file_exists($fetchfnfile)) {
            include($fetchfnfile);
        }
    }

    if (isset($fetch["json_encode"]) && $fetch["json_encode"]) {
        $output = json_encode($output);
        echo $output;
    } else {
        return $output;
    }

}

?>