<?php

if (role(['permissions' => ['super_privileges' => 'header_footer']])) {
    $form = array();
    $form['loaded'] = new stdClass();
    $form['loaded']->title = Registry::load('strings')->headers_footers;
    $form['loaded']->button = Registry::load('strings')->update;

    $form['fields'] = new stdClass();

    $form['fields']->process = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "update"
    ];

    $form['fields']->update = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "headers_footers"
    ];

    $form['fields']->page = [
        "title" => Registry::load('strings')->select_a_page, "tag" => 'select', "class" => 'field toggle_form_fields'
    ];
    $form['fields']->page["attributes"] = [
        "hide_field" => "page_elements",
        "show_fields" => "chat_page|chat_page_elements,entry_page|entry_page_elements,landing_page|landing_page_elements"
    ];

    $form['fields']->page['options'] = [
        "chat_page" => Registry::load('strings')->chat_page,
        "entry_page" => Registry::load('strings')->entry_page,
        "landing_page" => Registry::load('strings')->landing_page,
    ];

    $chat_page_header = htmlspecialchars(file_get_contents('assets/headers_footers/chat_page/header.php'), ENT_QUOTES, 'UTF-8');
    $chat_page_footer = htmlspecialchars(file_get_contents('assets/headers_footers/chat_page/footer.php'), ENT_QUOTES, 'UTF-8');
    $chat_page_body = htmlspecialchars(file_get_contents('assets/headers_footers/chat_page/body.php'), ENT_QUOTES, 'UTF-8');

    $form['fields']->chat_page_header = [
        "title" => Registry::load('strings')->header, "tag" => 'textarea',
        "class" => 'field base_encode d-none page_elements chat_page_elements', "value" => $chat_page_header,
        "infotip" => Registry::load('strings')->infotip_header_tag
    ];

    $form['fields']->chat_page_header["attributes"] = ["rows" => 5];


    $form['fields']->chat_page_body = [
        "title" => Registry::load('strings')->body, "tag" => 'textarea',
        "class" => 'field base_encode d-none page_elements chat_page_elements', "value" => $chat_page_body,
        "infotip" => Registry::load('strings')->infotip_body_tag
    ];

    $form['fields']->chat_page_body["attributes"] = ["rows" => 5];

    $form['fields']->chat_page_footer = [
        "title" => Registry::load('strings')->footer, "tag" => 'textarea',
        "class" => 'field base_encode d-none page_elements chat_page_elements', "value" => $chat_page_footer,
        "infotip" => Registry::load('strings')->infotip_footer_tag
    ];

    $form['fields']->chat_page_footer["attributes"] = ["rows" => 5];


    $entry_page_header = htmlspecialchars(file_get_contents('assets/headers_footers/entry_page/header.php'), ENT_QUOTES, 'UTF-8');
    $entry_page_footer = htmlspecialchars(file_get_contents('assets/headers_footers/entry_page/footer.php'), ENT_QUOTES, 'UTF-8');
    $entry_page_body = htmlspecialchars(file_get_contents('assets/headers_footers/entry_page/body.php'), ENT_QUOTES, 'UTF-8');

    $form['fields']->entry_page_header = [
        "title" => Registry::load('strings')->header, "tag" => 'textarea',
        "class" => 'field base_encode d-none page_elements entry_page_elements', "value" => $entry_page_header,
        "infotip" => Registry::load('strings')->infotip_header_tag
    ];

    $form['fields']->entry_page_header["attributes"] = ["rows" => 5];


    $form['fields']->entry_page_body = [
        "title" => Registry::load('strings')->body, "tag" => 'textarea',
        "class" => 'field base_encode d-none page_elements entry_page_elements', "value" => $entry_page_body,
        "infotip" => Registry::load('strings')->infotip_body_tag
    ];

    $form['fields']->entry_page_body["attributes"] = ["rows" => 5];

    $form['fields']->entry_page_footer = [
        "title" => Registry::load('strings')->footer, "tag" => 'textarea',
        "class" => 'field base_encode d-none page_elements entry_page_elements', "value" => $entry_page_footer,
        "infotip" => Registry::load('strings')->infotip_footer_tag
    ];

    $form['fields']->entry_page_footer["attributes"] = ["rows" => 5];


    $landing_page_header = htmlspecialchars(file_get_contents('assets/headers_footers/landing_page/header.php'), ENT_QUOTES, 'UTF-8');
    $landing_page_footer = htmlspecialchars(file_get_contents('assets/headers_footers/landing_page/footer.php'), ENT_QUOTES, 'UTF-8');
    $landing_page_body = htmlspecialchars(file_get_contents('assets/headers_footers/landing_page/body.php'), ENT_QUOTES, 'UTF-8');

    $form['fields']->landing_page_header = [
        "title" => Registry::load('strings')->header, "tag" => 'textarea',
        "class" => 'field base_encode d-none page_elements landing_page_elements', "value" => $landing_page_header,
        "infotip" => Registry::load('strings')->infotip_header_tag
    ];

    $form['fields']->landing_page_header["attributes"] = ["rows" => 5];


    $form['fields']->landing_page_body = [
        "title" => Registry::load('strings')->body, "tag" => 'textarea',
        "class" => 'field base_encode d-none page_elements landing_page_elements', "value" => $landing_page_body,
        "infotip" => Registry::load('strings')->infotip_body_tag
    ];

    $form['fields']->landing_page_body["attributes"] = ["rows" => 5];

    $form['fields']->landing_page_footer = [
        "title" => Registry::load('strings')->footer, "tag" => 'textarea',
        "class" => 'field base_encode d-none page_elements landing_page_elements', "value" => $landing_page_footer,
        "infotip" => Registry::load('strings')->infotip_footer_tag
    ];

    $form['fields']->landing_page_footer["attributes"] = ["rows" => 5];
}

?>