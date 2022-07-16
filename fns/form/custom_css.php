<?php

if (role(['permissions' => ['super_privileges' => 'customizer']])) {

    $form = array();
    $form['loaded'] = new stdClass();
    $form['loaded']->title = Registry::load('strings')->custom_css;
    $form['loaded']->button = Registry::load('strings')->update;

    $custom_css = file_get_contents("assets/css/common/custom_css.css");
    $custom_css = htmlspecialchars(trim($custom_css), ENT_QUOTES, 'UTF-8');

    $form['fields'] = new stdClass();

    $form['fields']->update = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "custom_css"
    ];

    $form['fields']->custom_css = [
        "title" => Registry::load('strings')->css_code, "tag" => 'textarea', "closetag" => true, "class" => 'field',
        "value" => $custom_css
    ];

    $form['fields']->custom_css["attributes"] = ["rows" => 8];

}
?>