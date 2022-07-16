<?php
$result = false;
if (isset($data['old']) && !empty($data['old']) && isset($data['new']) && !empty($data['new'])) {
    if (isset($data['real_path']) && $data['real_path']) {
        $oldname = $data['old'];
        $newname = $data['new'];
    } else {
        $oldname = 'assets/files/'.$data['old'];
        $newname = 'assets/files/'.$data['new'];
    }

    if (file_exists($oldname) && !file_exists($newname)) {
        rename($oldname, $newname);
        $result = true;
    }
}
?>