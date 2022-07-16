<?php

if (role(['permissions' => ['custom_fields' => ['create', 'edit']], 'condition' => 'OR'])) {

    $todo = 'add';
    $language_id = Registry::load('current_user')->language;

    $form['loaded'] = new stdClass();
    $form['fields'] = new stdClass();

    if (role(['permissions' => ['custom_fields' => 'edit']]) && isset($load["field_id"])) {

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
            'custom_fields.field_id', 'custom_fields.field_category', 'custom_fields.field_type', 'fieldname.string_value(field_name)',
            'custom_fields.show_on_signup', 'custom_fields.required', 'custom_fields.disabled', 'custom_fields.show_on_info_page',
            'custom_fields.editable_only_once'
        ];

        $join["[>]language_strings(fieldname)"] = ["custom_fields.string_constant" => "string_constant", "AND" => ["language_id" => $language_id]];

        $where["custom_fields.field_id"] = $load["field_id"];
        $where["LIMIT"] = 1;

        $field = DB::connect()->select('custom_fields', $join, $columns, $where);

        if (!isset($field[0])) {
            return false;
        } else {
            $field = $field[0];
        }

        $form['fields']->field_id = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load["field_id"]
        ];

        $form['loaded']->title = Registry::load('strings')->edit_custom_field;
        $form['loaded']->button = Registry::load('strings')->update;
    } else {
        $form['loaded']->title = Registry::load('strings')->add_custom_field;
        $form['loaded']->button = Registry::load('strings')->create;
    }

    $form['fields']->$todo = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "custom_fields"
    ];

    if (role(['permissions' => ['custom_fields' => 'edit']]) && isset($load["field_id"])) {

        $form['fields']->language_id = [
            "title" => Registry::load('strings')->language, "tag" => 'select', "class" => 'field'
        ];

        if (isset($load["language_id"]) && !empty($load["language_id"])) {
            $form['fields']->language_id['value'] = $load["language_id"];
        }
        $form['fields']->language_id["class"] = 'field switch_form';
        $form['fields']->language_id["parent_attributes"] = [
            "form" => "custom_fields",
            "data-field_id" => $load["field_id"],
        ];

        foreach ($languages as $language) {
            $language_identifier = $language['language_id'];
            $form['fields']->language_id['options'][$language_identifier] = $language['name'];
        }


        $form['fields']->identifier = [
            "title" => Registry::load('strings')->identifier, "tag" => 'input', "type" => 'text', "class" => 'field',
            "attributes" => ["disabled" => "disabled"],
            "value" => 'custom_field_'.$load["field_id"],
        ];

    }

    $form['fields']->field_name = [
        "title" => Registry::load('strings')->name, "tag" => 'input', "type" => "text", "class" => 'field', "placeholder" => Registry::load('strings')->name,
    ];

    $form['fields']->field_category = [
        "title" => Registry::load('strings')->category, "tag" => 'select', "class" => 'field showfieldon'
    ];

    $form['fields']->field_category["attributes"] = ["fieldclass" => "show_on_signup", "checkvalue" => "profile"];

    $form['fields']->field_category['options'] = [
        "profile" => Registry::load('strings')->profile,
        "group" => Registry::load('strings')->group,
    ];

    $form['fields']->field_type = [
        "title" => Registry::load('strings')->field_type, "tag" => 'select', "class" => 'field showfieldon'
    ];

    $form['fields']->field_type["attributes"] = ["fieldclass" => "fieldoptions", "checkvalue" => "dropdown"];
    $form['fields']->field_type['options'] = [
        "short_text" => Registry::load('strings')->short_text_field,
        "long_text" => Registry::load('strings')->long_text_field,
        "date" => Registry::load('strings')->date_field,
        "number" => Registry::load('strings')->number_field,
        "dropdown" => Registry::load('strings')->dropdown_field,
        "link" => Registry::load('strings')->link_field,
    ];

    $form['fields']->field_options = [
        "title" => Registry::load('strings')->field_options, "tag" => 'textarea', "class" => 'field d-none fieldoptions', "placeholder" => Registry::load('strings')->separate_commas
    ];

    $form['fields']->field_options["attributes"] = ["rows" => 6];


    $form['fields']->show_on_signup = [
        "title" => Registry::load('strings')->show_on_signup, "tag" => 'select', "class" => 'field show_on_signup d-none'
    ];
    $form['fields']->show_on_signup['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    $form['fields']->required = [
        "title" => Registry::load('strings')->required_field, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->required['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    $form['fields']->show_on_info_page = [
        "title" => Registry::load('strings')->show_on_info_page, "tag" => 'select', "class" => 'field', "value" => 'yes'
    ];
    $form['fields']->show_on_info_page['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    $form['fields']->editable_only_once = [
        "title" => Registry::load('strings')->editable_only_once, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->editable_only_once['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    $form['fields']->disabled = [
        "title" => Registry::load('strings')->disabled, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->disabled['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];


    if (role(['permissions' => ['custom_fields' => 'edit']]) && isset($load["field_id"])) {
        $show_on_signup = $required = $disabled = $editable_only_once = $show_on_info_page = 'no';

        if ((int)$field['disabled'] === 1) {
            $disabled = 'yes';
        }

        if ((int)$field['show_on_signup'] === 1) {
            $show_on_signup = 'yes';
        }

        if ((int)$field['required'] === 1) {
            $required = 'yes';
        }

        if ((int)$field['editable_only_once'] === 1) {
            $editable_only_once = 'yes';
        }

        if ((int)$field['show_on_info_page'] === 1) {
            $show_on_info_page = 'yes';
        }

        $form['fields']->field_name["value"] = $field['field_name'];
        $form['fields']->field_category["value"] = $field['field_category'];
        $form['fields']->field_type["value"] = $field['field_type'];
        $form['fields']->show_on_signup["value"] = $show_on_signup;
        $form['fields']->required["value"] = $required;
        $form['fields']->disabled["value"] = $disabled;
        $form['fields']->editable_only_once["value"] = $editable_only_once;
        $form['fields']->show_on_info_page["value"] = $show_on_info_page;

        if ($field['field_category'] === 'profile') {
            $form['fields']->show_on_signup["class"] = 'field show_on_signup';
        }

        if ($field['field_type'] === 'dropdown') {

            $columns = $join = $where = null;
            $columns = [
                'language_strings.string_value(field_options)'
            ];

            $where["language_strings.string_constant"] = 'custom_field_'.$field['field_id'].'_options';
            $where["language_strings.language_id"] = $language_id;
            $where["LIMIT"] = 1;

            $fieldoptions = DB::connect()->select('language_strings', $columns, $where);

            $form['fields']->field_options["class"] = 'field fieldoptions';

            if (isset($fieldoptions[0])) {

                $field_options = '';
                $field_options_array = json_decode($fieldoptions[0]['field_options']);

                if (!empty($field_options_array)) {
                    foreach ($field_options_array as $key => $option) {
                        $field_options .= $key."=>".$option.",\n";
                    }
                    $field_options = trim($field_options, ',');
                }

                $form['fields']->field_options["value"] = $field_options;
            }
        }
    }
}
?>
