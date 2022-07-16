<?php
$result = false;
if (isset($data['from']) && !empty($data['from']) && isset($data['to']) && !empty($data['to'])) {
    if (isset($data['real_path']) && $data['real_path']) {
        $copyfrom = $data['from'];
        $copyto = $data['to'];
    } else {
        $copyfrom = 'assets/files/'. $data['from'];
        $copyto = 'assets/files/'. $data['to'];
    }

    if (file_exists($copyfrom)) {
        if (is_dir($copyfrom)) {
            $dir_handle = opendir($copyfrom);
            while ($file = readdir($dir_handle)) {
                if ($file != "." && $file != "..") {
                    if (is_dir($copyfrom . "/" . $file)) {
                        if (!is_dir($copyto . "/" . $file)) {
                            mkdir($copyto . "/" . $file);
                        }
                        $newdata['real_path'] = true;
                        $newdata['from'] = rtrim($copyfrom, '/').'/'.$file;
                        $newdata['to'] = rtrim($copyto, '/').'/'.$file;
                        files('copy', $newdata);
                    } else {
                        copy($copyfrom . "/" . $file, $copyto . "/" . $file);
                    }
                }
            }
            closedir($dir_handle);
        } else {
            copy($copyfrom, $copyto);
        }
        $result = true;
    }
}

?>