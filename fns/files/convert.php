<?php

$result = array();
$result['result'] = false;

if (isset($data['convert']) && !empty($data['convert']) && isset($data['saveas']) && !empty($data['saveas'])) {
    if (isset($data['real_path']) && $data['real_path']) {
        $convert = $data['convert'];
        $saveas = $data['saveas'];
    } else {
        $convert = 'assets/files/'.$data['convert'];
        $saveas = 'assets/files/'.$data['saveas'];
    }

    if (file_exists($convert) && !file_exists($saveas)) {

        $result = array();
        $result['result'] = true;
    }
}
?>