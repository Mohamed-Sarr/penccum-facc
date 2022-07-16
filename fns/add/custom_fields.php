<?php

$result = array();
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->went_wrong;
$result['error_key'] = 'something_went_wrong';

if (role(['permissions' => ['custom_fields' => 'create']])) {

    $result['error_message'] = Registry::load('strings')->invalid_value;
    $result['error_key'] = 'invalid_value';
    $result['error_variables'] = [];

    $noerror = true;
    $show_on_signup = $required = $editable_only_once = $show_on_info_page = $disabled = 0;

    if (!isset($data['field_name']) || empty($data['field_name'])) {
        $result['error_variables'][] = ['field_name'];
        $noerror = false;
    }
    if (!isset($data['field_category']) || empty($data['field_category'])) {
        $result['error_variables'][] = ['field_category'];
        $noerror = false;
    }
    if (!isset($data['field_type']) || empty($data['field_type'])) {
        $result['error_variables'][] = ['field_type'];
        $noerror = false;
    }

    if ($noerror) {
        $field_categories = ['profile', 'group'];
        $field_types = ["short_text", "long_text", "date", "number", "dropdown", "link"];

        if (!in_array($data['field_category'], $field_categories)) {
            $data['field_category'] = 'profile';
        }

        if (!in_array($data['field_type'], $field_types)) {
            $data['field_type'] = 'short_text';
        }

        if (isset($data['show_on_signup']) && $data['show_on_signup'] === 'yes') {
            $show_on_signup = 1;
        }

        if (isset($data['required']) && $data['required'] === 'yes') {
            $required = 1;
        }

        if (isset($data['disabled']) && $data['disabled'] === 'yes') {
            $disabled = 1;
        }

        if (isset($data['show_on_info_page']) && $data['show_on_info_page'] === 'yes') {
            $show_on_info_page = 1;
        }

        if (isset($data['editable_only_once']) && $data['editable_only_once'] === 'yes') {
            $editable_only_once = 1;
        }

        if ($data['field_type'] === 'dropdown' && empty($data['field_options'])) {
            $data['field_type'] = 'short_text';
        }

        DB::connect()->insert("custom_fields", [
            "string_constant" => $data['field_name'],
            "field_category" => $data['field_category'],
            "field_type" => $data['field_type'],
            "show_on_signup" => $show_on_signup,
            "required" => $required,
            "editable_only_once" => $editable_only_once,
            "show_on_info_page" => $show_on_info_page,
            "disabled" => $disabled,
            "updated_on" => Registry::load('current_user')->time_stamp,
        ]);

        if (!DB::connect()->error) {
            $custom_field_id = DB::connect()->id();
            $custom_field_string = 'custom_field_'.$custom_field_id;
            $custom_field_options = 'custom_field_'.$custom_field_id.'_options';

            DB::connect()->update("custom_fields", ["string_constant" => $custom_field_string], ["field_id" => $custom_field_id]);

            language(['add_string' => $custom_field_string, 'value' => $data['field_name'], 'skip_update' => true]);

            if ($data['field_type'] === 'dropdown' && !empty($data['field_options'])) {
                $field_options = array();
                $data['field_options'] = str_replace(array('\'', '"'), '', $data['field_options']);
                $data['field_options'] = trim(preg_replace('/\s*,\s*/', ',', $data['field_options']), ',');
                $index = 1;

                if (!empty($data['field_options'])) {
                    $field_options_array = array_map('trim', explode(',', $data['field_options']));
                    foreach ($field_options_array as $key => $field_option) {
                        $field_option_value = explode("=>", $field_option);
                        if (isset($field_option_value[1])) {
                            $field_option_value[0] = trim($field_option_value[0]);
                            $field_options[$field_option_value[0]] = trim($field_option_value[1]);
                        } else {
                            $field_options[$index] = $field_option;
                            $index++;
                        }
                    }
                }

                language(['add_string' => $custom_field_options, 'value' => $field_options, 'encode' => true, 'skip_update' => true]);
            }

            $result = array();
            $result['success'] = true;
            $result['todo'] = 'reload';
            $result['reload'] = 'custom_fields';
        } else {
            $result['error_message'] = Registry::load('strings')->went_wrong;
            $result['error_key'] = 'something_went_wrong';
        }
    }
}
