<?php

if (role(['permissions' => ['super_privileges' => 'customizer']])) {

    $all_css_variables = DB::connect()->select("css_variables", ["css_variable", "css_variable_value", "color_scheme"]);
    $stored_css_variables = array();

    foreach ($all_css_variables as $stored_css_variable) {
        $color_scheme = $stored_css_variable['color_scheme'];
        $variable = $stored_css_variable['css_variable'];
        $stored_css_variables[$color_scheme][$variable] = $stored_css_variable['css_variable_value'];
    }

    $form['loaded'] = new stdClass();
    $form['loaded']->title = Registry::load('strings')->appearance;
    $form['loaded']->button = Registry::load('strings')->update;

    $form['fields'] = new stdClass();

    $form['fields']->update = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "appearance"
    ];

    $form['fields']->color_scheme = [
        "title" => Registry::load('strings')->color_scheme, "tag" => 'select', "class" => 'field toggle_form_fields'
    ];
    $form['fields']->color_scheme ["options"] = ['light_mode' => 'Light Color Scheme', 'dark_mode' => 'Dark Color Scheme'];

    $form['fields']->color_scheme["attributes"] = [
        "hide_field" => "color_scheme_elements",
        "reset_value" => true,
        "show_fields" => "light_mode|light_color_scheme_elements,dark_mode|dark_color_scheme_elements"
    ];


    $form['fields']->light_color_scheme_elements = [
        "title" => Registry::load('strings')->location, "tag" => 'select', "class" => 'field toggle_form_fields color_scheme_elements light_color_scheme_elements'
    ];

    $form['fields']->light_color_scheme_elements["attributes"] = [
        "hide_field" => "css_variables",
        "show_fields" => ""
    ];

    include('fns/global/css_variables.php');

    $count = 0;

    foreach ($css_variables as $variable_index => $css_variable) {

        $element_name = str_replace('-', '_', $variable_index);
        $element_name = Registry::load('strings')->$element_name;

        $form['fields']->light_color_scheme_elements['options']['light-'.$variable_index] = $element_name;

        if ($count !== 0) {
            $form['fields']->light_color_scheme_elements["attributes"]["show_fields"] .= ',';
        }

        $form['fields']->light_color_scheme_elements["attributes"]["show_fields"] .= 'light-'.$variable_index.'|light-'.$variable_index.'_variables';

        if ($variable_index === 'chat-page') {

            $form['fields']->chat_page_logo_light_mode = [
                "title" => Registry::load('strings')->logo, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse color_scheme_elements css_variables d-none light_color_scheme light-'.$variable_index.'_variables',
                "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];
            $form['fields']->chat_page_bg_light_mode = [
                "title" => Registry::load('strings')->background, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse color_scheme_elements css_variables d-none light_color_scheme light-'.$variable_index.'_variables',
                "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];
            $form['fields']->loading_image_light_mode = [
                "title" => Registry::load('strings')->loading_image, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse color_scheme_elements css_variables d-none light_color_scheme light-'.$variable_index.'_variables',
                "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];
        } else if ($variable_index === 'entry-page') {
            $form['fields']->entry_page_logo_light_mode = [
                "title" => Registry::load('strings')->logo, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse color_scheme_elements css_variables d-none light_color_scheme light-'.$variable_index.'_variables',
                "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];

            $form['fields']->entry_page_bg_light_mode = [
                "title" => Registry::load('strings')->background, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse color_scheme_elements css_variables d-none light_color_scheme light-'.$variable_index.'_variables',
                "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];
        } else if ($variable_index === 'landing-page') {
            $form['fields']->landing_page_logo_light_mode = [
                "title" => Registry::load('strings')->logo, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse color_scheme_elements css_variables d-none light_color_scheme light-'.$variable_index.'_variables',
                "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];

            $form['fields']->landing_page_footer_logo_light_mode = [
                "title" => Registry::load('strings')->footer_logo, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse color_scheme_elements css_variables d-none light_color_scheme light-'.$variable_index.'_variables',
                "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];
        }

        foreach ($css_variable as $variable => $css_value) {

            $field_name = 'light-color-scheme-'.$variable_index.'-'.$variable;
            $field_title = str_replace('-', '_', $variable);
            $field_title = Registry::load('strings')->$field_title;
            $css_variable_name = $variable_index.'-'.$variable;

            if (isset($stored_css_variables['light_mode'][$css_variable_name])) {
                $css_value = $stored_css_variables['light_mode'][$css_variable_name];
            }


            if (strpos($variable, 'font-size') !== false) {
                $form['fields']->$field_name = [
                    "title" => $field_title, "tag" => 'input', "type" => "text",
                    "class" => 'field color_scheme_elements css_variables d-none light_color_scheme light-'.$variable_index.'_variables',
                    "value" => $css_value,
                ];
            } else {
                $form['fields']->$field_name = [
                    "title" => $field_title, "tag" => 'input', "type" => "color",
                    "class" => 'field color_scheme_elements css_variables d-none light_color_scheme light-'.$variable_index.'_variables',
                    "value" => $css_value,
                ];
            }

        }
        $count++;
    }


    $form['fields']->dark_color_scheme_elements = [
        "title" => Registry::load('strings')->location, "tag" => 'select', "class" => 'field toggle_form_fields color_scheme_elements dark_color_scheme_elements'
    ];

    $form['fields']->dark_color_scheme_elements["attributes"] = [
        "hide_field" => "css_variables",
        "show_fields" => ""
    ];


    include('fns/global/dark_mode_css_variables.php');

    $count = 0;

    foreach ($css_variables as $variable_index => $css_variable) {

        $element_name = str_replace('-', '_', $variable_index);
        $element_name = Registry::load('strings')->$element_name;

        $form['fields']->dark_color_scheme_elements['options']['dark-'.$variable_index] = $element_name;

        if ($count !== 0) {
            $form['fields']->dark_color_scheme_elements["attributes"]["show_fields"] .= ',';
        }

        $form['fields']->dark_color_scheme_elements["attributes"]["show_fields"] .= 'dark-'.$variable_index.'|dark-'.$variable_index.'_variables';


        if ($variable_index === 'chat-page') {
            $form['fields']->chat_page_logo_dark_mode = [
                "title" => Registry::load('strings')->logo, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse color_scheme_elements css_variables d-none dark_color_scheme dark-'.$variable_index.'_variables',
                "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];
            $form['fields']->chat_page_bg_dark_mode = [
                "title" => Registry::load('strings')->background, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse color_scheme_elements css_variables d-none dark_color_scheme dark-'.$variable_index.'_variables',
                "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];
            $form['fields']->loading_image_dark_mode = [
                "title" => Registry::load('strings')->loading_image, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse color_scheme_elements css_variables d-none dark_color_scheme dark-'.$variable_index.'_variables',
                "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];
        } else if ($variable_index === 'entry-page') {
            $form['fields']->entry_page_logo_dark_mode = [
                "title" => Registry::load('strings')->logo, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse color_scheme_elements css_variables d-none dark_color_scheme dark-'.$variable_index.'_variables',
                "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];
            $form['fields']->entry_page_bg_dark_mode = [
                "title" => Registry::load('strings')->background, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse color_scheme_elements css_variables d-none dark_color_scheme dark-'.$variable_index.'_variables',
                "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];
        } else if ($variable_index === 'landing-page') {
            $form['fields']->landing_page_logo_dark_mode = [
                "title" => Registry::load('strings')->logo, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse color_scheme_elements css_variables d-none dark_color_scheme dark-'.$variable_index.'_variables',
                "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];
            $form['fields']->landing_page_footer_logo_dark_mode = [
                "title" => Registry::load('strings')->footer_logo, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse color_scheme_elements css_variables d-none dark_color_scheme dark-'.$variable_index.'_variables',
                "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];
        }

        foreach ($css_variable as $variable => $css_value) {

            $field_name = 'dark-color-scheme-'.$variable_index.'-'.$variable;
            $field_title = str_replace('-', '_', $variable);
            $field_title = Registry::load('strings')->$field_title;
            $css_variable_name = $variable_index.'-'.$variable;

            if (isset($stored_css_variables['dark_mode'][$css_variable_name])) {
                $css_value = $stored_css_variables['dark_mode'][$css_variable_name];
            }

            if (strpos($variable, 'font-size') !== false) {
                $form['fields']->$field_name = [
                    "title" => $field_title, "tag" => 'input', "type" => "text",
                    "class" => 'field color_scheme_elements css_variables d-none dark_color_scheme dark-'.$variable_index.'_variables',
                    "value" => $css_value,
                ];
            } else {
                $form['fields']->$field_name = [
                    "title" => $field_title, "tag" => 'input', "type" => "color",
                    "class" => 'field color_scheme_elements css_variables d-none dark_color_scheme dark-'.$variable_index.'_variables',
                    "value" => $css_value,
                ];
            }

        }
        $count++;
    }


    $form['fields']->chat_page_boxed_layout = [
        "title" => Registry::load('strings')->chat_page_boxed_layout, "tag" => 'select',
        "class" => 'field color_scheme_elements css_variables d-none light_color_scheme light-chat-page_variables',
        "value" => Registry::load('settings')->chat_page_boxed_layout,
    ];
    $form['fields']->chat_page_boxed_layout['options'] = [
        'enable' => Registry::load('strings')->enable,
        'disable' => Registry::load('strings')->disable
    ];
    $form['fields']->chat_page_boxed_layout['class'] .= ' dark_color_scheme dark-chat-page_variables';


}

?>