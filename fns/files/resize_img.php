<?php
$result = false;
if (isset($data['resize']) && !empty($data['resize'])) {

    if (!is_array($data['resize'])) {
        $data['resize'] = array($data['resize']);
    }

    foreach ($data['resize'] as $resize_img) {
        if (isset($data['real_path']) && $data['real_path']) {
            $image = $saveas = $resize_img;
            if (isset($data['saveas']) && !empty($data['saveas'])) {
                $saveas = $data['saveas'];
            }
        } else {
            $image = $saveas = 'assets/files/'.$resize_img;
            if (isset($data['saveas']) && !empty($data['saveas'])) {
                $saveas = 'assets/files/'.$data['saveas'];
            }
        }

        if (file_exists($image)) {
            $image_type = mime_content_type($image);
            $width = $height = $data['width'];
            $crop = 0;

            if (isset($data['height']) && !empty($data['height'])) {
                $height = $data['height'];
            }

            if (isset($data['crop']) && $data['crop']) {
                $crop = 1;
            }

            if ($image_type === 'image/jpeg' || $image_type === 'image/webp' || $image_type === 'image/png' || $image_type === 'image/gif' || $image_type === 'image/bmp' || $image_type === 'image/x-ms-bmp') {
                if (list($orginal_width, $orginal_height) = getimagesize($image)) {

                    if ($crop) {
                        if ($orginal_width > $width && $orginal_height > $height) {
                            $ratio = max($width/$orginal_width, $height/$orginal_height);
                            $orginal_height = $height / $ratio;
                            $x = ($orginal_width - $width / $ratio) / 2;
                            $y = ($orginal_width - $width / $ratio) / 2;
                            $orginal_width = $width / $ratio;
                        }
                    } else {
                        if ($orginal_width > $width && $orginal_height > $height) {
                            $ratio = min($width/$orginal_width, $height/$orginal_height);
                            $width = $orginal_width * $ratio;
                            $height = $orginal_height * $ratio;
                            $x = $y = 0;
                        }
                    }


                    if ($orginal_width > $width && $orginal_height > $height) {
                        if (extension_loaded('imagick') && $image_type === 'image/gif') {
                            $file_src = str_replace('\\', '/', realpath($image));
                            $image_load = new Imagick($file_src);
                            $image_load = $image_load->coalesceimages();
                            $original = new \Imagick($file_src);
                            $new = new \Imagick();

                            $i = 0;
                            $frameStep = ceil($original->getNumberImages() / 25);
                            foreach ($original as $frame) {
                                if ($i % $frameStep === 0) {
                                    $delay = $frame->getImageDelay();
                                    $frame->cropImage($orginal_width, $orginal_height, $x, $y);
                                    $frame->thumbnailImage($width, $height);
                                    $frame->setImagePage($width, $height, 0, 0);
                                    $frame->setImageDelay($delay * $frameStep);
                                    $new->addImage($frame->getImage());
                                }

                                $i++;
                            }
                            file_put_contents($saveas, $new->getImagesBlob());
                            $new->clear();
                            $new->destroy();
                            $original->clear();
                            $original->destroy();
                        } else {
                            switch ($image_type) {
                                case 'image/bmp': $img = @imagecreatefrombmp($image); break;
                                case 'image/x-ms-bmp': $img = @imageCreateFromBmp($image); break;
                                case 'image/gif': $img = @imagecreatefromgif($image); break;
                                case 'image/jpeg': $img = @imagecreatefromjpeg($image); break;
                                case 'image/webp': $img = @imagecreatefromwebp($image); break;
                                case 'image/png': $img = @imagecreatefrompng($image); break;
                                default : return false;
                                }

                                if (!$img) {
                                    copy($image, $saveas);
                                } else {
                                    $new = imagecreatetruecolor($width, $height);

                                    if ($image_type === "image/gif" || $image_type === "image/png") {
                                        imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
                                        imagealphablending($new, false);
                                        imagesavealpha($new, true);
                                    }
                                    
                                    imagecopyresampled($new, $img, 0, 0, (int)$x, 0, $width, $height, (int)$orginal_width, (int)$orginal_height);

                                    switch ($image_type) {
                                        case 'image/bmp': imagejpeg($new, $saveas); break;
                                        case 'image/x-ms-bmp': imagejpeg($new, $saveas); break;
                                        case 'image/gif': imagegif($new, $saveas); break;
                                        case 'image/jpeg': imagejpeg($new, $saveas); break;
                                        case 'image/webp': imagewebp($new, $saveas); break;
                                        case 'image/png': imagepng($new, $saveas); break;
                                    }
                                }
                            }
                        } else {
                            copy($image, $saveas);
                        }

                        $result = true;
                    }
                }
            }
        }
    }
    ?>