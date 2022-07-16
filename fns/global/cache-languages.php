<?php

$languages = array();

$join = [
    "[>]languages" => ["language_strings.language_id" => "language_id"]
];
$columns = [
    'languages.language_id', 'languages.text_direction', 'languages.iso_code',
    'language_strings.string_constant', 'language_strings.string_value'
];

$where = ['languages.language_id[!]' => 0, 'language_strings.skip_cache' => 0];

$strings = DB::connect()->select('language_strings', $join, $columns, $where);


foreach ($strings as $key => $string) {
    $language_id = $string['language_id'];
    $string_constant = $string['string_constant'];
    $languages[$language_id]['text_direction'] = $string['text_direction'];
    $languages[$language_id]['iso_code'] = $string['iso_code'];
    $languages[$language_id][$string_constant] = $string['string_value'];

}

$core_language = $languages[1];
$compare_languages = $languages;
unset($compare_languages[1]);

$total_strings = count($core_language);


foreach ($compare_languages as $compare_language_id => $compare_language) {
    if (count($compare_language) < $total_strings) {
        $array_difference = array_diff_key($core_language, $compare_language);
        $insert_data = array();
        $index = 0;

        foreach ($array_difference as $add_string_constant => $add_string_value) {
            $languages[$compare_language_id][$add_string_constant] = $add_string_value;
            $insert_data[$index]['string_constant'] = $add_string_constant;
            $insert_data[$index]['string_value'] = $add_string_value;
            $insert_data[$index]['language_id'] = $compare_language_id;
            $index++;
        }

        if (!empty($insert_data)) {
            DB::connect()->insert('language_strings', $insert_data);
        }
    }
}

if (!file_exists('assets/cache/languages/')) {
    mkdir('assets/cache/languages/', 0755, true);
} else {
    array_map('unlink', array_filter((array) glob("assets/cache/languages/*")));
}

foreach ($languages as $key => $language) {
    $cache = json_encode($languages[$key]);
    $cachefile = 'assets/cache/languages/language-'.$key.'.cache';
    if (file_exists($cachefile)) {
        unlink($cachefile);
    }
    $cachefile = fopen($cachefile, "w");
    fwrite($cachefile, $cache);
    fclose($cachefile);
}

$result = true;