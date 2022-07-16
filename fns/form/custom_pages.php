<?php

if (role(['permissions' => ['custom_pages' => ['create', 'edit']], 'condition' => 'OR'])) {

    $form = array();

    $todo = 'add';
    $form['loaded'] = new stdClass();
    $form['fields'] = new stdClass();
    $language_id = Registry::load('current_user')->language;

    if (isset($load["page_id"]) && role(['permissions' => ['custom_pages' => 'edit']])) {

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
            'language_strings.string_value(page_title)', 'custom_pages.slug', 'custom_pages.meta_title',
            'custom_pages.meta_description', 'custom_pages.disabled', 'custom_pages.who_all_can_view_page'
        ];

        $join["[>]language_strings"] = ["custom_pages.string_constant" => "string_constant", "AND" => ["language_id" => $language_id]];

        $where["custom_pages.page_id"] = $load["page_id"];
        $where["LIMIT"] = 1;

        $custompage = DB::connect()->select('custom_pages', $join, $columns, $where);


        $columns = $join = $where = null;
        $columns = ['language_strings.string_value(page_content)'];
        $where["language_strings.language_id"] = $language_id;
        $where["language_strings.string_constant"] = 'custom_page_'.$load["page_id"].'_content';
        $where["LIMIT"] = 1;

        $page_content = DB::connect()->select('language_strings', $columns, $where);

        if (!isset($custompage[0])) {
            return false;
        } else {
            $custompage = $custompage[0];
        }

        $form['fields']->page_id = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load["page_id"]
        ];

        $form['loaded']->title = Registry::load('strings')->edit_custom_page;
        $form['loaded']->button = Registry::load('strings')->update;
    } else {
        $form['loaded']->title = Registry::load('strings')->add_custom_page;
        $form['loaded']->button = Registry::load('strings')->create;
    }

    $form['fields']->$todo = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "custom_pages"
    ];

    if (isset($load["page_id"])) {

        $form['fields']->language_id = [
            "title" => Registry::load('strings')->language, "tag" => 'select', "class" => 'field'
        ];

        if (isset($load["language_id"]) && !empty($load["language_id"])) {
            $form['fields']->language_id['value'] = $load["language_id"];
        }

        $form['fields']->language_id["class"] = 'field switch_form';
        $form['fields']->language_id["parent_attributes"] = [
            "form" => "custom_pages",
            "enlarge" => "true",
            "data-page_id" => $load["page_id"],
        ];

        foreach ($languages as $language) {
            $language_identifier = $language['language_id'];
            $form['fields']->language_id['options'][$language_identifier] = $language['name'];
        }

    }


    $form['fields']->page_title = [
        "title" => Registry::load('strings')->page_title, "tag" => 'input', "type" => "text", "class" => 'field',
        "placeholder" => Registry::load('strings')->page_title,
    ];

    $form['fields']->slug = [
        "title" => Registry::load('strings')->slug, "tag" => 'input', "type" => "text", "class" => 'field',
        "placeholder" => Registry::load('strings')->slug,
    ];


    $form['fields']->featured_image = [
        "title" => Registry::load('strings')->featured_image, "tag" => 'input', "type" => 'file', "class" => 'field filebrowse',
        "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
    ];


    $form['fields']->page_content = [
        "title" => Registry::load('strings')->page_content, "tag" => 'textarea',
        "class" => 'content_editor field page_content',
        "placeholder" => Registry::load('strings')->page_content
    ];

    $form['fields']->page_content["attributes"] = ["rows" => 6];

    $form['fields']->meta_title = [
        "title" => Registry::load('strings')->meta_title, "tag" => 'input', "type" => "text",
        "class" => 'field', "placeholder" => Registry::load('strings')->meta_title,
    ];

    $form['fields']->meta_description = [
        "title" => Registry::load('strings')->meta_description, "tag" => 'textarea', "class" => 'field',
        "placeholder" => Registry::load('strings')->meta_description,
    ];

    $form['fields']->meta_description["attributes"] = ["rows" => 4];





    $form['fields']->who_all_can_view_page = [
        "title" => Registry::load('strings')->who_all_can_view_page, "tag" => 'select', "class" => 'field'
    ];

    $language_id = Registry::load('current_user')->language;

    $join = ["[>]language_strings(string)" => ["site_roles.string_constant" => "string_constant", "AND" => ["language_id" => $language_id]]];
    $columns = ['site_roles.site_role_id', 'string.string_value(name)'];
    $where = ['site_role_attribute[!]' => 'banned_users'];

    $site_roles = DB::connect()->select('site_roles', $join, $columns, $where);

    $site_roles = array_column($site_roles, 'name', 'site_role_id');

    $form['fields']->who_all_can_view_page = [
        "title" => Registry::load('strings')->who_all_can_view_page, "tag" => 'checkbox',
        "class" => 'field', 'options' => $site_roles, 'select_all' => true
    ];


    $form['fields']->disabled = [
        "title" => Registry::load('strings')->disabled, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->disabled['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    if (isset($load["page_id"]) && role(['permissions' => ['custom_pages' => 'edit']])) {
        $disabled = 'no';

        if ((int)$custompage['disabled'] === 1) {
            $disabled = 'yes';
        }

        unset($form['fields']->page_content["placeholder"]);

        $form['fields']->page_title["value"] = $custompage['page_title'];
        $form['fields']->slug["value"] = $custompage['slug'];
        $form['fields']->disabled["value"] = $disabled;

        if (!empty($custompage['meta_title'])) {
            $form['fields']->meta_title["value"] = $custompage['meta_title'];
        }

        if (!empty($custompage['meta_description'])) {
            $form['fields']->meta_description["value"] = $custompage['meta_description'];
        }

        if (isset($form['fields']->who_all_can_view_page)) {

            if ($custompage['who_all_can_view_page'] !== 'all') {
                $form['fields']->who_all_can_view_page["value"] = $custompage['who_all_can_view_page'];
            }

        }

        if (isset($page_content[0])) {
            $page_content = $page_content[0]['page_content'];

            if (!empty($page_content)) {
                $form['fields']->page_content["value"] = htmlspecialchars($page_content, ENT_QUOTES, 'UTF-8');
            }

        }

    }
}
?>