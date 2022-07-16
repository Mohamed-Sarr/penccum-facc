<?php

$noerror = true;
$result = array();
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->something_went_wrong;
$result['error_key'] = 'something_went_wrong';

if (role(['permissions' => ['super_privileges' => 'customizer']])) {

    $content = '';

    if (isset($data['custom_css']) && !empty($data['custom_css'])) {
        $content = $data['custom_css'];
    }

    $update = fopen("assets/css/common/custom_css.css", "w");
    fwrite($update, $content);
    fclose($update);

    cache(['rebuild' => 'settings']);
    cache(['rebuild' => 'css']);

    $result = array();
    $result['success'] = true;
    $result['todo'] = 'refresh';
}
?>