<?php

$result = false;
$skip_update = 0;
$skip_cache = 0;

if (isset($data['skip_cache']) && $data['skip_cache']) {
    $skip_update = 1;
    $skip_cache = 1;
}

if (isset($data['skip_update']) && $data['skip_update']) {
    $skip_update = 1;
}

if (isset($data['add_string']) && isset($data['value'])) {

    $string_value = $data['value'];
    $string_type = 'one-line';

    if (empty($skip_cache)) {
        if (is_array($string_value)) {
            array_walk_recursive($string_value, "cleanOutput");
        } else {
            $string_value = htmlspecialchars($string_value, ENT_QUOTES, 'UTF-8');
        }
    } else {
        $string_type = 'multi_line';
    }

    if (isset($data['encode']) && $data['encode']) {
        $string_value = json_encode($string_value);
    }

    if (isset($data['multi_line']) && $data['multi_line']) {
        $string_type = 'multi_line';
    }

    $data = [
        "string_constant" => $data['add_string'], 'string_value' => $string_value, 'string_type' => $string_type,
        'skip_update' => $skip_update, 'skip_cache' => $skip_cache
    ];

    $query = "INSERT INTO <language_strings> (string_constant,string_value,string_type,skip_update,skip_cache,language_id) ";
    $query .= "SELECT :string_constant, :string_value, :string_type,:skip_update,:skip_cache, language_id ";
    $query .= "FROM <languages>;";

    DB::connect()->query($query, $data);

    cache(['rebuild' => 'languages']);
    $result = true;

} else if (isset($data['edit_string']) && isset($data['value'])) {

    $string_value = $data['value'];
    $string_type = 'one-line';

    if (empty($skip_cache)) {
        if (is_array($string_value)) {
            array_walk_recursive($string_value, "cleanOutput");
        } else {
            $string_value = htmlspecialchars($string_value, ENT_QUOTES, 'UTF-8');
        }
    } else {
        $string_type = 'multi_line';
    }

    $language_id = Registry::load('current_user')->language;

    if (isset($data['language_id'])) {
        $data['language_id'] = filter_var($data['language_id'], FILTER_SANITIZE_NUMBER_INT);

        if (!empty($data['language_id'])) {
            $language_id = $data['language_id'];
        }
    }

    if (isset($data['encode']) && $data['encode']) {
        $string_value = json_encode($string_value);
    }

    if (isset($data['multi_line']) && $data['multi_line']) {
        $string_type = 'multi_line';
    }

    DB::connect()->update("language_strings",
        ["string_value" => $string_value, "string_type" => $string_type, "skip_update" => $skip_update, "skip_cache" => $skip_cache],
        ["language_id" => $language_id, "string_constant" => $data['edit_string']]
    );

    cache(['rebuild' => 'languages']);
    $result = true;
} else if (isset($data['delete_string'])) {

    DB::connect()->delete("language_strings", ["string_constant" => $data['delete_string']]);

    cache(['rebuild' => 'languages']);
    $result = true;
}