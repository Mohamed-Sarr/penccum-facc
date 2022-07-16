<?php
$result = false;
if (isset($data['name']) && !empty($data['name'])) {
    if (isset($data['real_path']) && $data['real_path']) {
        $newfile = $data['name'];
    } else {
        $newfile = 'assets/files/'.$data['name'];
    }

    if (!file_exists($newfile)) {
        $contents = '';

        if (isset($data['contents']) && !empty($data['contents'])) {
            $contents = $data['contents'];
        }

        $file = fopen($newfile, "w");
        fwrite($file, $contents);
        fclose($file);
        $result = true;
    }
}
?>