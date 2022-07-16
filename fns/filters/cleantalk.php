<?php

function user_spam_check($user_data) {

    $params = array(
        'method_name' => 'spam_check',
        'auth_key' => 'enter_your_key',
        'email' => $user_data['email'],
        'ip' => $user_data['ip'],
    );

    $check = curl_init();
    curl_setopt($check, CURLOPT_URL, 'https://moderate.cleantalk.org/api2.0');
    curl_setopt($check, CURLOPT_TIMEOUT, 10);
    curl_setopt($check, CURLOPT_POST, true);
    curl_setopt($check, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($check, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($check, CURLOPT_HTTPHEADER, array('Expect:'));
    curl_setopt($check, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($check, CURLOPT_SSL_VERIFYHOST, false);
    $result = curl_exec($check);
    curl_close($check);

    echo $result;
    if ($result) {
        $ct_result = json_decode($result);
    }
}
?>