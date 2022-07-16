<?php

$form = array();

if (role(['permissions' => ['badges' => ['add', 'edit']], 'condition' => 'OR'])) {


    $form['loaded'] = new stdClass();
    $todo = 'add';
    $language_id = Registry::load('current_user')->language;

    $form['fields'] = new stdClass();

    if (isset($load["badge_id"])) {

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
            'badges.badge_id', 'badges.badge_category', 'string.string_value(badge_title)',
            'badges.disabled'
        ];

        $join["[>]language_strings(string)"] = ["badges.string_constant" => "string_constant", "AND" => ["language_id" => $language_id]];

        $where["badges.badge_id"] = $load["badge_id"];
        $where["LIMIT"] = 1;

        $badge = DB::connect()->select('badges', $join, $columns, $where);

        if (!isset($badge[0])) {
            return false;
        } else {
            $badge = $badge[0];
        }

        $form['fields']->badge_id = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load["badge_id"]
        ];
        $form['loaded']->title = Registry::load('strings')->edit_badge;
        $form['loaded']->button = Registry::load('strings')->update;
    } else {
        $form['loaded']->title = Registry::load('strings')->create_badge;
        $form['loaded']->button = Registry::load('strings')->create;
    }

    $form['fields']->process = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $todo
    ];
    $form['fields']->$todo = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => 'badges'
    ];

    if (isset($load["badge_id"])) {

        $form['fields']->language_id = [
            "title" => Registry::load('strings')->language, "tag" => 'select', "class" => 'field'
        ];

        if (isset($load["language_id"]) && !empty($load["language_id"])) {
            $form['fields']->language_id['value'] = $load["language_id"];
        }

        $form['fields']->language_id["class"] = 'field switch_form';

        $form['fields']->language_id["parent_attributes"] = [
            "form" => "badges",
            "data-badge_id" => $load["badge_id"],
        ];

        foreach ($languages as $language) {
            $language_identifier = $language['language_id'];
            $form['fields']->language_id['options'][$language_identifier] = $language['name'];
        }

    }


    $form['fields']->badge_title = [
        "title" => Registry::load('strings')->badge_title, "tag" => 'input', "type" => 'text', "class" => 'field',
        "placeholder" => Registry::load('strings')->badge_title
    ];

    $form['fields']->badge_image = [
        "title" => Registry::load('strings')->badge_image, "tag" => 'input', "type" => 'file', "class" => 'field filebrowse',
        "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
    ];


    $form['fields']->badge_category = [
        "title" => Registry::load('strings')->category, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->badge_category['options'] = [
        "profile" => Registry::load('strings')->profile,
        "group" => Registry::load('strings')->group,
    ];

    $form['fields']->disabled = [
        "title" => Registry::load('strings')->disabled, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->disabled['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];




    if (isset($load["badge_id"])) {

        $disabled = 'no';

        if ((int)$badge['disabled'] === 1) {
            $disabled = 'yes';
        }

        $form['fields']->badge_title["value"] = $badge['badge_title'];
        $form['fields']->badge_category["value"] = $badge['badge_category'];
        $form['fields']->disabled["value"] = $disabled;

    }

}

?>