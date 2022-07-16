<?php
$result = false;
if (isset($data['name']) && !empty($data['name'])) {
    if (isset($data['real_path']) && $data['real_path']) {
        $newfolder = $data['name'];
    } else {
        $newfolder = 'assets/files/'.$data['name'];
    }

    if (!file_exists($newfolder)) {
        mkdir($newfolder, 0755, true);
        $result = true;
    }
}
?>