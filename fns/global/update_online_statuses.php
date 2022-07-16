<?php
$idle_time = Registry::load('settings')->change_to_idle_status_after;
$offline_time = Registry::load('settings')->change_to_offline_status_after;

if (empty($idle_time)) {
    $idle_time = 5;
}

if (empty($offline_time)) {
    $offline_time = 10;
}

if ($offline_time < $idle_time) {
    $offline_time = $idle_time+$offline_time;
}

$time_from = get_date();
$time_from = strtotime($time_from);
$time_from = $time_from - ($idle_time * 60);
$time_from = date("Y-m-d H:i:s", $time_from);

$where = [
    "last_seen_on[<]" => $time_from,
    "online_status" => 1
];

if (Registry::load('current_user')->logged_in) {
    $where["user_id[!]"] = Registry::load('current_user')->id;
}

DB::connect()->update("site_users", ["online_status" => 2], $where);

$time_from = get_date();
$time_from = strtotime($time_from);
$time_from = $time_from - ($offline_time * 60);
$time_from = date("Y-m-d H:i:s", $time_from);

$where = [
    "last_seen_on[<]" => $time_from,
    "OR" => ["online_status(online)" => 1, "online_status(idle)" => 2]
];

if (Registry::load('current_user')->logged_in) {
    $where["user_id[!]"] = Registry::load('current_user')->id;
}

DB::connect()->update("site_users", ["online_status" => 0], $where);
?>