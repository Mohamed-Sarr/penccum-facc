<?php

$result = null;
$seperator = Registry::load('config')->file_seperator;
$search_string = 'assets/files/'.$data['from'].'/'.$data['search'].$seperator.'*.{'.rangeof_chars('jpg,png,gif,jpeg,bmp').'}';
$search = glob($search_string, GLOB_BRACE);
$found = false;
$replace_with_default = true;

if (isset($data['replace_with_default']) && !$data['replace_with_default']) {
    $replace_with_default = false;
}

if (isset($search[0])) {
    $found = true;
    if (isset($data['exists']) && $data['exists']) {
        $result = true;
    } else {
        $result = Registry::load('config')->site_url.$search[0];
    }
} else {
    if (isset($data['exists']) && $data['exists']) {
        $found = true;
        $result = false;
    } else {
        if ($replace_with_default) {
            $result = Registry::load('config')->site_url.'assets/files/'.$data['from'].'/default.png';
        } else {
            $result = null;
        }
    }
}
if (!$found && Registry::load('settings')->gravatar === 'enable') {
    if (isset($data['gravatar']) && filter_var($data['gravatar'], FILTER_VALIDATE_EMAIL)) {
        $result = 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($data['gravatar'])))."?s=150&d=mp&r=g";
    }
}
