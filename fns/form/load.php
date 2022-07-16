<?php

function form($load) {
    $form = array();

    if (isset($load["form"])) {
        $load["form"] = preg_replace("/[^a-zA-Z0-9_]+/", "", $load["form"]);
    }

    if (isset($load["form"]) && !empty($load["form"])) {
        $loadfnfile = 'fns/form/'.$load["form"].'.php';
        if (file_exists($loadfnfile)) {
            include($loadfnfile);
        }
    }

    $result = json_encode($form);
    echo $result;
}

?>