<?php

$result = array();
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->went_wrong;
$result['error_key'] = 'something_went_wrong';

if (role(['permissions' => ['badges' => 'create']])) {

    $noerror = true;
    $disabled = 0;
    $result['success'] = false;
    $result['error_message'] = Registry::load('strings')->invalid_value;
    $result['error_key'] = 'invalid_value';
    $result['error_variables'] = [];

    if (!isset($data['badge_title']) || empty($data['badge_title'])) {
        $result['error_variables'][] = ['badge_title'];
        $noerror = false;
    }
    if (!isset($data['badge_category']) || empty($data['badge_category'])) {
        $result['error_variables'][] = ['badge_category'];
        $noerror = false;
    }

    if ($noerror) {

        $badge_categories = ['profile', 'group'];

        $data['badge_title'] = htmlspecialchars(trim($data['badge_title']), ENT_QUOTES, 'UTF-8');

        if (!in_array($data['badge_category'], $badge_categories)) {
            $data['badge_category'] = 'profile';
        }

        if (isset($data['disabled']) && $data['disabled'] === 'yes') {
            $disabled = 1;
        }



        DB::connect()->insert("badges", [
            "string_constant" => $data['badge_title'],
            "badge_category" => $data['badge_category'],
            "disabled" => $disabled,
            "created_on" => Registry::load('current_user')->time_stamp,
            "updated_on" => Registry::load('current_user')->time_stamp,
        ]);

        if (!DB::connect()->error) {

            $badge_id = DB::connect()->id();
            $string_constant = 'badge_'.$badge_id;
            DB::connect()->update("badges", ["string_constant" => $string_constant], ["badge_id" => $badge_id]);
            language(['add_string' => $string_constant, 'value' => $data['badge_title']]);

            if (isset($_FILES['badge_image']['name']) && !empty($_FILES['badge_image']['name'])) {

                include 'fns/filters/load.php';
                include 'fns/files/load.php';

                if (isImage($_FILES['badge_image']['tmp_name'])) {

                    $extension = pathinfo($_FILES['badge_image']['name'])['extension'];
                    $filename = $badge_id.Registry::load('config')->file_seperator.random_string(['length' => 6]).'.'.$extension;

                    if (files('upload', ['upload' => 'badge_image', 'folder' => 'badges', 'saveas' => $filename])['result']) {
                        files('resize_img', ['resize' => 'badges/'.$filename, 'width' => 150, 'height' => 150, 'crop' => true]);
                    }
                }
            }

            $result = array();
            $result['success'] = true;
            $result['todo'] = 'reload';
            $result['reload'] = 'badges';
        } else {
            $result['error_message'] = Registry::load('strings')->went_wrong;
            $result['error_key'] = 'something_went_wrong';
        }

    }
}

?>