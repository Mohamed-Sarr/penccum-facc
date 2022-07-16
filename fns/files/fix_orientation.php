<?php
$result = false;
if (isset($data['image']) && !empty($data['image'])) {
    if (isset($data['real_path']) && $data['real_path']) {
        $image = $data['image'];
    } else {
        $image = 'assets/files/'.$data['image'];
    }

    if (file_exists($image)) {
        if (function_exists('exif_read_data')) {;
            $exif = @exif_read_data($image);
            if ($exif && isset($exif['Orientation'])) {
                $orientation = $exif['Orientation'];
                if ($orientation != 1) {
                    $info = getimagesize($image);
                    if ($info['mime'] === 'image/jpeg') {
                        $img = imagecreatefromjpeg($image);
                    } else if ($info['mime'] === 'image/gif') {
                        $img = imagecreatefromgif($image);
                    } elseif ($info['mime'] === 'image/png') {
                        $img = imagecreatefrompng($image);
                    }
                    $deg = 0;
                    switch ($orientation) {
                        case 3:
                            $deg = 180;
                            break;
                        case 6:
                            $deg = 270;
                            break;
                        case 8:
                            $deg = 90;
                            break;
                    }
                    if ($deg) {
                        $img = imagerotate($img, $deg, 0);
                    }
                    imagejpeg($img, $image, 95);
                    $result = true;
                }
            }
        }
    }
}
?>
