<?php

if (role(['permissions' => ['super_privileges' => 'core_settings']])) {

    $columns = $join = $where = null;
    $heading = $message = $footer_text = '';
    $language_id = Registry::load('current_user')->language;

    $columns = [
        'languages.name', 'languages.language_id'
    ];

    $where["languages.disabled[!]"] = 1;

    $languages = DB::connect()->select('languages', $columns, $where);

    if (isset($load["language_id"])) {
        $load["language_id"] = filter_var($load["language_id"], FILTER_SANITIZE_NUMBER_INT);

        if (!empty($load["language_id"])) {
            $language_id = $load["language_id"];
        }
    }

    $columns = $join = $where = null;

    $columns = ['language_strings.string_constant', 'language_strings.string_value'];

    $where["language_strings.language_id"] = $language_id;

    $where["AND"]["OR"] = [
        "language_strings.string_constant #first_condition" => 'welcome_screen_heading',
        "language_strings.string_constant #second_condition" => 'welcome_screen_message',
        "language_strings.string_constant #third_condition" => 'welcome_screen_footer_text',
    ];

    $welcome_screen = DB::connect()->select('language_strings', $columns, $where);

    foreach ($welcome_screen as $welcome) {
        if ($welcome['string_constant'] === 'welcome_screen_heading') {
            $heading = $welcome['string_value'];
        } else if ($welcome['string_constant'] === 'welcome_screen_message') {
            $message = $welcome['string_value'];
        } else if ($welcome['string_constant'] === 'welcome_screen_footer_text') {
            $footer_text = $welcome['string_value'];
        }
    }

    $form = array();
    $form['loaded'] = new stdClass();
    $form['loaded']->title = Registry::load('strings')->welcome_screen;
    $form['loaded']->button = Registry::load('strings')->update;

    $form['fields'] = new stdClass();

    $form['fields']->update = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "welcome_screen"
    ];

    $form['fields']->language_id = [
        "title" => Registry::load('strings')->language, "tag" => 'select', "class" => 'field'
    ];

    if (isset($load["language_id"]) && !empty($load["language_id"])) {
        $form['fields']->language_id['value'] = $load["language_id"];
    }

    $form['fields']->language_id["class"] = 'field switch_form';
    $form['fields']->language_id["parent_attributes"] = [
        "form" => "welcome_screen",
    ];

    foreach ($languages as $language) {
        $language_identifier = $language['language_id'];
        $form['fields']->language_id['options'][$language_identifier] = $language['name'];
    }

    $form['fields']->image = [
        "title" => Registry::load('strings')->image, "tag" => 'input', "type" => 'file', "class" => 'field filebrowse',
        "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
    ];

    $form['fields']->heading = [
        "title" => Registry::load('strings')->heading, "tag" => 'textarea', "closetag" => true, "class" => 'field',
        "value" => $heading,
    ];

    $form['fields']->heading["attributes"] = ["rows" => 4];

    $form['fields']->message = [
        "title" => Registry::load('strings')->welcome_message, "tag" => 'textarea', "closetag" => true, "class" => 'field',
        "value" => $message
    ];

    $form['fields']->message["attributes"] = ["rows" => 6];

    $form['fields']->footer_text = [
        "title" => Registry::load('strings')->footer_text, "tag" => 'textarea', "closetag" => true, "class" => 'field',
        "value" => $footer_text
    ];

    $form['fields']->footer_text["attributes"] = ["rows" => 6];
}
?>