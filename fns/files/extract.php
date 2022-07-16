<?php
$result = false;
if (isset($data['extract']) && !empty($data['extract']) && isset($data['extract_in']) && !empty($data['extract_in'])) {
    if (isset($data['real_path']) && $data['real_path']) {
        $extract = $data['extract'];
        $extract_in = $data['extract_in'];
    } else {
        $extract = 'assets/files/'.$data['extract'];
        $extract_in = 'assets/files/'.$data['extract_in'];
    }

    if (file_exists($extract) && !file_exists($extract_in)) {
        mkdir($extract_in);
        $zip = new ZipArchive;
        if ($zip->open($extract) === TRUE) {
            $zip->extractTo($extract_in);
            $zip->close();
            $result = true;
        }
    }
}
?>