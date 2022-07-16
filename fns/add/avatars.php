<?php

$result = array();
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->went_wrong;
$result['error_key'] = 'something_went_wrong';
$noerror = true;

if (role(['permissions' => ['avatars' => 'upload']])) {

    include 'fns/filters/load.php';
    include 'fns/files/load.php';

    $noerror = true;

    if ($noerror) {

        if (isset($_FILES['avatars']['name']) && !empty($_FILES['avatars']['name'])) {

            $filename = 'avatar-.png';

            $upload_info = [
                'upload' => 'avatars',
                'folder' => 'assets/files/avatars/',
                'saveas' => $filename,
                'real_path' => true,
                'multi_upload' => true,
                'only_allow' => ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/x-ms-bmp']
            ];

            $avatars = files('upload', $upload_info);

            if ($avatars['result']) {
                if (isset($avatars['files'])) {
                    foreach ($avatars['files'] as $index => $file) {

                        $resize = [
                            'resize' => $file['file'],
                            'width' => 150,
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
        $result['reload'] = 'avatars';

    }
}
?>