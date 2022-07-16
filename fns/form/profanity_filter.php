<?php

if (role(['permissions' => ['super_privileges' => 'profanity_filter']])) {
    $form = array();
    $form['loaded'] = new stdClass();
    $form['loaded']->title = Registry::load('strings')->profanity_filter;
    $form['loaded']->button = Registry::load('strings')->update;


    $badwords = $whitelist = array();
    include('fns/filters/blacklist.php');
    include('fns/filters/whitelist.php');

    $badwords = implode(PHP_EOL, $badwords);
    $whitelist = implode(PHP_EOL, $whitelist);

    $form['fields'] = new stdClass();

    $form['fields']->process = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "update"
    ];

    $form['fields']->update = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "profanity_filter"
    ];

    $form['fields']->status = [
        "title" => Registry::load('strings')->status, "tag" => 'select', "class" => 'field',
        "value" => Registry::load('settings')->profanity_filter
    ];
    $form['fields']->status['options'] = [
        "enable" => Registry::load('strings')->enable,
        "disable" => Registry::load('strings')->disable,
        "strict_mode" => Registry::load('strings')->strict_mode,
    ];


    $form['fields']->blacklist = [
        "title" => Registry::load('strings')->blacklist, "tag" => 'textarea', "closetag" => true, "class" => 'field',
        "value" => $badwords
    ];

    $form['fields']->blacklist["attributes"] = ["rows" => 8];

    $form['fields']->whitelist = [
        "title" => Registry::load('strings')->whitelist, "tag" => 'textarea', "closetag" => true, "class" => 'field',
        "value" => $whitelist
    ];

    $form['fields']->whitelist["attributes"] = ["rows" => 8];
}
?>