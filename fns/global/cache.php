<?php

$result = false;

if (isset($data["rebuild"])) {
    $data["rebuild"] = preg_replace("/[^a-zA-Z0-9_]+/", "", $data["rebuild"]);
}

if (isset($data["rebuild"]) && !empty($data["rebuild"])) {
    $loadfnfile = 'fns/global/cache-'.$data["rebuild"].'.php';
    if (file_exists($loadfnfile)) {
        include($loadfnfile);
    }
}