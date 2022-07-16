<?php
$verification_URL = 'https://hcaptcha.com/siteverify';
$post_data = http_build_query(
    array(
        'secret' => Registry::load('settings')->captcha_secret_key,
        'response' => $validate,
        'remoteip' => (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER['REMOTE_ADDR'])
    )
);
if (function_exists('curl_init') && function_exists('curl_setopt') && function_exists('curl_exec')) {
    $curl_request = curl_init($verification_URL);
    curl_setopt($curl_request, CURLOPT_POST, 1);
    curl_setopt($curl_request, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl_request, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl_request, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($curl_request, CURLOPT_TIMEOUT, 5);
    curl_setopt($curl_request, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-type: application/x-www-form-urlencoded'));
    $response = curl_exec($curl_request);
    curl_close($curl_request);
} else {
    $opts = array('http' =>
        array(
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => $post_data
        )
    );
    $context = stream_context_create($opts);
    $response = file_get_contents($verification_URL, false, $context);
}
if ($response) {
    $result = json_decode($response);
    if ($result->success === true) {
        $result = true;
    }
}