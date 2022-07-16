<?php
$result = false;
if (isset($data['delete']) && !empty($data['delete'])) {
    if (isset($data['real_path']) && $data['real_path']) {
        $delete = $data['delete'];
    } else {
        $delete = 'assets/files/'.$data['delete'];
    }

    if (file_exists($delete)) {
        if (is_dir($delete)) {
            $objects = scandir($delete);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($delete. DIRECTORY_SEPARATOR .$object) && !is_link($delete."/".$object)) {
                        $newdata['real_path'] = true;
                        $newdata['delete'] = $delete. DIRECTORY_SEPARATOR .$object;
                        files('delete', $newdata);
                    } else {
                        unlink($delete. DIRECTORY_SEPARATOR .$object);
                    }
                }
            }
            rmdir($delete);
        } else {
            unlink($delete);
        }
        $result = true;
    }
}
?>