<?php
$result = 0;
$convert_to = null;

if (isset($data["getsize_of"]) && !empty($data["getsize_of"])) {
    if (isset($data["real_path"]) && $data["real_path"]) {
        $sizeof = $data["getsize_of"];
    } else {
        $sizeof = 'assets/files/'. $data["getsize_of"];
    }

    if (file_exists($sizeof)) {
        if (is_dir($sizeof)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($sizeof)
            );
            foreach ($iterator as $i) {
                $result += $i->getSize();
            }
        } else {
            $result = filesize($sizeof);
        }
    }
}

if (isset($data["convert_to"]) && !empty($data["convert_to"])) {
    $convert_to = $data["convert_to"];
}


if (!isset($data["original_value"])) {
    if (($result >= 1099511627776 && $arg[2] === null) || $convert_to === "tb") {
        $result = number_format($result / 1099511627776, 2) . " TB";
    } elseif (
        ($result >= 1073741824 && $convert_to === null) || $convert_to === "gb") {
        $result = number_format($result / 1073741824, 2) . " GB";
    } elseif (
        ($result >= 1048576 && $convert_to === null) || $convert_to === "mb") {
        $result = number_format($result / 1048576, 2) . " MB";
    } elseif (($result >= 1024 && $convert_to === null) || $convert_to === "kb") {
        $result = number_format($result / 1024, 2) . " KB";
    } elseif ($result > 1) {
        $result = $result . " bytes";
    } elseif ($result === 1) {
        $result = $result . " byte";
    } else {
        $result = "0 bytes";
    }
}
?>
