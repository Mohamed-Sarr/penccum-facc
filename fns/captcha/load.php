<?php

function validate_captcha($provider, $validate) {
    $result = false;

    $load_fn_file = 'fns/captcha/'.$provider.'.php';
    if (file_exists($load_fn_file) && !empty($validate)) {
        include($load_fn_file);
    }

    return $result;
}

?>