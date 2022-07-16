<?php

if (role(['permissions' => ['super_privileges' => 'firewall']])) {
    
    $form = array();
    $form['loaded'] = new stdClass();
    $form['loaded']->title = Registry::load('strings')->firewall;
    $form['loaded']->button = Registry::load('strings')->update;

    $ip_blacklist = array();
    include('assets/cache/ip_blacklist.cache');

    if (!empty($ip_blacklist)) {
        $ip_blacklist = implode(PHP_EOL, $ip_blacklist);
    }

    $form['fields'] = new stdClass();

    $form['fields']->process = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "update"
    ];

    $form['fields']->update = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "firewall"
    ];


    $form['fields']->status = [
        "title" => Registry::load('strings')->status, "tag" => 'select', "class" => 'field',
        "value" => Registry::load('settings')->firewall
    ];
    $form['fields']->status['options'] = [
        "enable" => Registry::load('strings')->enable,
        "disable" => Registry::load('strings')->disable,
    ];


    $form['fields']->blacklist = [
        "title" => Registry::load('strings')->blacklist, "tag" => 'textarea', "class" => 'field',
        "value" => $ip_blacklist,
    ];

    $form['fields']->blacklist["attributes"] = ["rows" => 17];

}

?>