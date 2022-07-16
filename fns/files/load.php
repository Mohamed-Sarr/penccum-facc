<?php
function files($todo, $data) {
    $result = array();

    if (isset($todo)) {
        $todo = preg_replace("/[^a-zA-Z0-9_]+/", "", $todo);
    }

    if (isset($todo) && !empty($todo)) {
        $loadfnfile = 'fns/files/'.$todo.'.php';
        if (file_exists($loadfnfile)) {
            include($loadfnfile);
        }
    }
    return $result;
}

?>