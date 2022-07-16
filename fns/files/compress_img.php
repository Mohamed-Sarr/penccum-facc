<?php
$result = false;
if (isset($data['compress']) && !empty($data['compress']) && isset($data['quality']) && !empty($data['quality'])) {

    if (!is_array($data['compress'])) {
        $data['compress'] = array($data['compress']);
    }

    foreach ($data['compress'] as $compress_img) {
        if (isset($data['real_path']) && $data['real_path']) {
            $image = $saveas = $compress_img;
            if (isset($data['saveas']) && !empty($data['saveas'])) {
                $saveas = $data['saveas'];
            }
        } else {
            $image = $saveas = 'assets/files/'.$compress_img;
            if (isset($data['saveas']) && !empty($data['saveas'])) {
                $saveas = 'assets/files/'.$data['saveas'];
            }
        }

        if (file_exists($image)) {
            $quality = $data['quality'];
            $image_type = mime_content_type($image);
            if ($image_type === 'image/jpeg' || $image_type === 'image/png' || $image_type === 'image/gif' || $image_type === 'image/bmp' || $image_type === 'image/x-ms-bmp') {
                list($width, $height) = getimagesize($image);
                switch ($image_type) {
                    case 'image/bmp': $img = imagecreatefromwbmp($image); break;
                    case 'image/x-ms-bmp': $img = imagecreatefromwbmp($image); break;
                    case 'image/gif': $img = imagecreatefromgif($image); break;
                    case 'image/jpeg': $img = imagecreatefromjpeg($image); break;
                    case 'image/png': $img = imagecreatefrompng($image); break;
                    default : return false;
                    }

                    $new = imagecreatetruecolor($width, $height);

                    if ($image_type === "image/gif" || $image_type === "image/png") {
                        imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
                        imagealphablending($new, false);
                        imagesavealpha($new, true);
                    }

                    imagecopyresampled($new, $img, 0, 0, 0, 0, $width, $height, $width, $height);

                    switch ($image_type) {
                        case 'image/bmp': imagewbmp($new, $saveas, $quality); break;
                        case 'image/x-ms-bmp': imagebmp($new, $saveas, $quality); break;
                        case 'image/gif': imagejpeg($new, $saveas, $quality); break;
                        case 'image/jpeg': imagejpeg($new, $saveas, $quality); break;
                        case 'image/png': imagepng($new, $saveas, $quality); break;
                    }
                    $result = true;
                }

            }
        }
    }
    ?>
