<?php

$result = array();
$noerror = false;
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->went_wrong;
$result['error_key'] = 'something_went_wrong';


if (role(['permissions' => ['super_privileges' => 'slideshows']])) {

    include 'fns/filters/load.php';
    include 'fns/files/load.php';

    if (isset($data["slideshow"])) {
        $data["slideshow"] = sanitize_filename($data['slideshow']);
        $location = 'assets/files/slideshows/'.$data["slideshow"].'/';
    }

    if (isset($data["slideshow"]) && !empty($data["slideshow"]) && file_exists($location)) {
        $noerror = true;
    }

    if ($noerror) {

        if (isset($_FILES['images']['name']) && !empty($_FILES['images']['name'])) {

            $filename = 'slideshow-.jpg';

            $upload_info = [
                'upload' => 'images',
                'folder' => $location,
                'saveas' => $filename,
                'append_timestamp' => true,
                'real_path' => true,
                'multi_upload' => true,
                'only_allow' => ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/x-ms-bmp']
            ];

            $images = files('upload', $upload_info);

            if ($images['result']) {
                if (isset($images['files'])) {
                    foreach ($images['files'] as $index => $file) {

                        $resize = [
                            'resize' => $file['file'],
                            'width' => 1920,
                            'height' => 1080,
                            'crop' => true,
                            'real_path' => true
                        ];

                        files('resize_img', $resize);
                    }
                }
            }

        }

        $result = array();
        $result['success'] = true;
        $result['todo'] = 'reload';
        $result['reload'] = 'slideshow_images';

    }
}
?>