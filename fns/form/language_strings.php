<?php

$form = array();

if (role(['permissions' => ['languages' => 'edit']])) {

    if (isset($load['string_id'])) {
        $load["string_id"] = filter_var($load["string_id"], FILTER_SANITIZE_NUMBER_INT);
    }

    if (isset($load["string_id"]) && !empty($load["string_id"])) {

        $form['loaded'] = new stdClass();
        $form['fields'] = new stdClass();
        $string_id = $load["string_id"];

        $columns = [
            'language_strings.string_constant', 'language_strings.string_value', 'languages.name',
            'languages.text_direction', 'language_strings.string_type',
        ];
        $join["[>]languages"] = ["language_strings.language_id" => "language_id"];

        $where["language_strings.string_id"] = $load["string_id"];

        $string = DB::connect()->select('language_strings', $join, $columns, $where);

        if (!isset($string[0])) {
            return false;
        } else {
            $string = $string[0];
        }

        $form['fields']->string_id = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load["string_id"]
        ];

        $form['loaded']->title = Registry::load('strings')->edit_language;
        $form['loaded']->button = Registry::load('strings')->update;

        $form['fields']->process = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => 'update'
        ];

        $form['fields']->update = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "language_strings"
        ];

        $form['fields']->language = [
            "title" => Registry::load('strings')->language, "tag" => 'input', "type" => "text", "class" => 'field',
            "value" => $string['name'], "attributes" => ['disabled' => 1]
        ];

        $text_direction = $string['text_direction'];

        $form['fields']->text_direction = [
            "title" => Registry::load('strings')->language_text_direction, "tag" => 'input', "type" => "text", "class" => 'field',
            "value" => Registry::load('strings')->$text_direction, "attributes" => ['disabled' => 1]
        ];

        $form['fields']->string_constant = [
            "title" => Registry::load('strings')->language, "tag" => 'input', "type" => "text", "class" => 'field',
            "value" => $string['string_constant'], "attributes" => ['disabled' => 1]
        ];
        if ($string['string_type'] === 'one-line' && strlen($string['string_value']) < 50) {
            $form['fields']->string_value = [
                "title" => Registry::load('strings')->language, "tag" => 'input', "type" => "text", "class" => 'field',
                "value" => $string['string_value']
            ];
        } else {
            $form['fields']->string_value = [
                "title" => Registry::load('strings')->language, "tag" => 'textarea', "class" => 'field',
                "value" => $string['string_value'],
            ];
            $form['fields']->string_value["attributes"] = ["rows" => 6];
        }

    }
}
?>