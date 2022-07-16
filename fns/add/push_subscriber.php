<?php

$result = array();
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->went_wrong;
$result['error_key'] = 'something_went_wrong';

$providers = ['onesignal', 'webpushr'];
$user_id = Registry::load('current_user')->id;
$device_token = 0;

if (Registry::load('current_user')->logged_in) {
    if (!empty(Registry::load('settings')->push_notifications) && Registry::load('settings')->push_notifications !== 'disable') {

        $service_provider = Registry::load('settings')->push_notifications;

        if (isset($data['service_provider']) && in_array($data['service_provider'], $providers)) {
            $service_provider = $data['service_provider'];
        }

        if (isset($data['device_token'])) {
            $device_token = strip_tags($data["device_token"]);
            $device_token = str_replace('"', "", $device_token);
            $device_token = str_replace("'", "", $device_token);
            $device_token = htmlspecialchars($device_token, ENT_QUOTES);
        }

        $check_token_exists = DB::connect()->select("push_subscriptions", ['push_subscriber_id'], [
            "user_id" => $user_id,
            "device_token" => $device_token,
            "push_notification_service" => $service_provider,
            "LIMIT" => 1
        ]);

        if (!empty($device_token) && !empty($user_id) && !isset($check_token_exists[0])) {

            DB::connect()->insert("push_subscriptions", [
                "user_id" => $user_id,
                "device_token" => $device_token,
                "push_notification_service" => $service_provider,
                "created_on" => Registry::load('current_user')->time_stamp,
                "updated_on" => Registry::load('current_user')->time_stamp,
            ]);

            $result = array();
            $result['success'] = true;
        }
    }
}
?>