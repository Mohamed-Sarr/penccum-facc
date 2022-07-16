<?php

$form = array();
if (role(['permissions' => ['custom_menu' => ['create', 'edit']], 'condition' => 'OR'])) {


    $form['loaded'] = new stdClass();
    $todo = 'add';

    $language_id = Registry::load('current_user')->language;

    $form['fields'] = new stdClass();

    if (isset($load["menu_item_id"]) && role(['permissions' => ['custom_menu' => 'edit']])) {

        $todo = 'update';
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

        $columns = [
            'custom_menu_items.menu_item_id', 'custom_menu_items.menu_icon_class', 'string.string_value(menu_title)',
            'custom_menu_items.page_id', 'custom_menu_items.web_address', 'custom_menu_items.link_target', 'custom_menu_items.show_on_landing_page_header',
            'custom_menu_items.show_on_landing_page_footer', 'custom_menu_items.show_on_entry_page', 'custom_menu_items.show_on_chat_page',
            'custom_menu_items.menu_item_order', 'custom_menu_items.disabled', 'custom_menu_items.menu_item_visibility'
        ];

        $join["[>]language_strings(string)"] = ["custom_menu_items.string_constant" => "string_constant", "AND" => ["language_id" => $language_id]];

        $where["custom_menu_items.menu_item_id"] = $load["menu_item_id"];
        $where["LIMIT"] = 1;

        $menu_item = DB::connect()->select('custom_menu_items', $join, $columns, $where);

        if (!isset($menu_item[0])) {
            return false;
        } else {
            $menu_item = $menu_item[0];
        }

        $form['fields']->menu_item_id = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load["menu_item_id"]
        ];
        $form['loaded']->title = Registry::load('strings')->edit_menu_item;
        $form['loaded']->button = Registry::load('strings')->update;
    } else {
        $form['loaded']->title = Registry::load('strings')->add_menu_item;
        $form['loaded']->button = Registry::load('strings')->create;
    }

    $form['fields']->process = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $todo
    ];
    $form['fields']->$todo = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => 'custom_menu_items'
    ];

    if (isset($load["menu_item_id"]) && role(['permissions' => ['custom_menu' => 'edit']])) {

        $form['fields']->language_id = [
            "title" => Registry::load('strings')->language, "tag" => 'select', "class" => 'field'
        ];

        if (isset($load["language_id"]) && !empty($load["language_id"])) {
            $form['fields']->language_id['value'] = $load["language_id"];
        }
        $form['fields']->language_id["class"] = 'field switch_form';
        $form['fields']->language_id["parent_attributes"] = [
            "form" => "custom_menu_items",
            "data-menu_item_id" => $load["menu_item_id"],
        ];

        foreach ($languages as $language) {
            $language_identifier = $language['language_id'];
            $form['fields']->language_id['options'][$language_identifier] = $language['name'];
        }

    }


    $form['fields']->menu_title = [
        "title" => Registry::load('strings')->menu_title, "tag" => 'input', "type" => 'text', "class" => 'field',
        "placeholder" => Registry::load('strings')->menu_title
    ];

    $form['fields']->icon_class = [
        "title" => Registry::load('strings')->icon_class, "tag" => 'input', "type" => 'text', "class" => 'field',
        "placeholder" => Registry::load('strings')->icon_class
    ];


    $form['fields']->link_type = [
        "title" => Registry::load('strings')->link_type, "tag" => 'select', "class" => 'field showfieldon',
        "value" => "custom_page"
    ];
    $form['fields']->link_type['options'] = [
        "custom_page" => Registry::load('strings')->custom_page,
        "web_address" => Registry::load('strings')->web_address,
    ];

    $form['fields']->link_type["attributes"] = [
        "fieldclass" => "web_address",
        "checkvalue" => "web_address",
        "hideclass" => "page_selector"
    ];

    $form['fields']->page_id = [
        "title" => Registry::load('strings')->custom_page, "tag" => 'select', "class" => 'field page_selector'
    ];

    $columns = $join = $where = null;
    $columns = [
        'language_strings.string_value(page_title)', 'custom_pages.page_id'
    ];

    $join["[>]language_strings"] = ["custom_pages.string_constant" => "string_constant", "AND" => ["language_id" => Registry::load('current_user')->language]];

    $where["custom_pages.disabled[!]"] = 1;

    $pages = DB::connect()->select('custom_pages', $join, $columns, $where);

    foreach ($pages as $page) {
        $page_identifier = $page['page_id'];
        $form['fields']->page_id['options'][$page_identifier] = $page['page_title'];
    }

    $form['fields']->web_address = [
        "title" => Registry::load('strings')->web_address, "tag" => 'input', "type" => 'text', "class" => 'field web_address d-none',
        "placeholder" => Registry::load('strings')->web_address
    ];

    $form['fields']->link_target = [
        "title" => Registry::load('strings')->link_target, "tag" => 'select', "class" => 'field web_address d-none'
    ];
    $form['fields']->link_target['options'] = [
        "open_in_same_window" => Registry::load('strings')->open_in_same_window,
        "open_in_new_tab" => Registry::load('strings')->open_in_new_tab,
    ];


    $form['fields']->show_on_landing_page_header = [
        "title" => Registry::load('strings')->show_on_landing_page_header, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->show_on_landing_page_header['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    $form['fields']->show_on_landing_page_footer = [
        "title" => Registry::load('strings')->show_on_landing_page_footer, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->show_on_landing_page_footer['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    $form['fields']->show_on_entry_page = [
        "title" => Registry::load('strings')->show_on_entry_page, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->show_on_entry_page['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    $form['fields']->show_on_chat_page = [
        "title" => Registry::load('strings')->show_on_chat_page, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->show_on_chat_page['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    $form['fields']->menu_item_order = [
        "title" => Registry::load('strings')->order, "tag" => 'input', "type" => "number", "class" => 'field',
        "value" => 1,
    ];


    $language_id = Registry::load('current_user')->language;

    $join = ["[>]language_strings(string)" => ["site_roles.string_constant" => "string_constant", "AND" => ["language_id" => $language_id]]];
    $columns = ['site_roles.site_role_id', 'string.string_value(name)'];
    $where = ['site_role_attribute[!]' => 'banned_users'];

    $site_roles = DB::connect()->select('site_roles', $join, $columns, $where);

    $site_roles = array_column($site_roles, 'name', 'site_role_id');

    $form['fields']->menu_item_visibility = [
        "title" => Registry::load('strings')->menu_item_visibility, "tag" => 'checkbox',
        "class" => 'field', 'options' => $site_roles, 'select_all' => true
    ];


    $form['fields']->disabled = [
        "title" => Registry::load('strings')->disabled, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->disabled['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];




    if (isset($load["menu_item_id"]) && role(['permissions' => ['custom_menu' => 'edit']])) {

        $disabled = $show_on_landing_page_footer = $show_on_landing_page_header = 'no';
        $show_on_entry_page = $show_on_chat_page = 'no';
        $link_target = 'open_in_same_window';

        if ((int)$menu_item['disabled'] === 1) {
            $disabled = 'yes';
        }

        if ((int)$menu_item['show_on_landing_page_header'] === 1) {
            $show_on_landing_page_header = 'yes';
        }
        if ((int)$menu_item['show_on_landing_page_footer'] === 1) {
            $show_on_landing_page_footer = 'yes';
        }

        if ((int)$menu_item['show_on_entry_page'] === 1) {
            $show_on_entry_page = 'yes';
        }

        if ((int)$menu_item['show_on_chat_page'] === 1) {
            $show_on_chat_page = 'yes';
        }

        if ((int)$menu_item['link_target'] === 1) {
            $link_target = 'open_in_new_tab';
        }

        if (empty($menu_item['page_id'])) {
            $form['fields']->link_type["value"] = 'web_address';
            $form['fields']->web_address["class"] = 'field web_address';
            $form['fields']->link_target["class"] = 'field web_address';
            $form['fields']->page_id["class"] = 'field page_selector d-none';
        }

        if (isset($form['fields']->menu_item_visibility)) {

            if ($menu_item['menu_item_visibility'] !== 'all') {
                $form['fields']->menu_item_visibility["value"] = $menu_item['menu_item_visibility'];
            }

        }

        $form['fields']->menu_title["value"] = $menu_item['menu_title'];
        $form['fields']->icon_class["value"] = $menu_item['menu_icon_class'];
        $form['fields']->page_id["value"] = $menu_item['page_id'];
        $form['fields']->web_address["value"] = $menu_item['web_address'];
        $form['fields']->disabled["value"] = $disabled;
        $form['fields']->link_target["value"] = $link_target;
        $form['fields']->show_on_chat_page["value"] = $show_on_chat_page;
        $form['fields']->show_on_entry_page["value"] = $show_on_entry_page;
        $form['fields']->show_on_landing_page_header["value"] = $show_on_landing_page_header;
        $form['fields']->show_on_landing_page_footer["value"] = $show_on_landing_page_footer;
        $form['fields']->menu_item_order["value"] = $menu_item['menu_item_order'];

    }

}

?>