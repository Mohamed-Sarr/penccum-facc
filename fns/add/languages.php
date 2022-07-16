<?php
$result = array();
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->went_wrong;
$result['error_key'] = 'something_went_wrong';

if (role(['permissions' => ['languages' => 'create']])) {

    $result['error_message'] = Registry::load('strings')->invalid_value;
    $result['error_key'] = 'invalid_value';
    $result['error_variables'] = [];

    $max_input_vars = ini_get('max_input_vars');

    include 'fns/filters/load.php';
    include 'fns/files/load.php';

    $noerror = true;
    $disabled = 0;
    $iso_code = 'en';
    $iso_codes = [
        "ab", "aa", "af", "ak", "sq", "am", "ar", "an", "hy", "as", "av", "ae", "ay", "az", "bm", "ba", "eu", "be", "bn", "bh", "bi", "bs", "br",
        "bg", "my", "ca", "km", "ch", "ce", "ny", "zh", "cu", "cv", "kw", "co", "cr", "hr", "cs", "da", "dv", "nl", "dz", "en", "eo", "et", "ee",
        "fo", "fj", "fi", "fr", "ff", "gd", "gl", "lg", "ka", "de", "ki", "el", "kl", "gn", "gu", "ht", "ha", "he", "hz", "hi", "ho", "hu", "is",
        "io", "ig", "id", "ia", "ie", "iu", "ik", "ga", "it", "ja", "jv", "kn", "kr", "ks", "kk", "rw", "kv", "kg", "ko", "kj", "ku", "ky", "lo",
        "la", "lv", "lb", "li", "ln", "lt", "lu", "mk", "mg", "ms", "ml", "mt", "gv", "mi", "mr", "mh", "ro", "mn", "na", "nv", "nd", "ng", "ne",
        "se", "no", "nb", "nn", "ii", "oc", "oj", "or", "om", "os", "pi", "pa", "ps", "fa", "pl", "pt", "qu", "rm", "rn", "ru", "sm", "sg", "sa",
        "sc", "sr", "sn", "sd", "si", "sk", "sl", "so", "st", "nr", "es", "su", "sw", "ss", "sv", "tl", "ty", "tg", "ta", "tt", "te", "th", "bo",
        "ti", "to", "ts", "tn", "tr", "tk", "tw", "ug", "uk", "ur", "uz", "ve", "vi", "vo", "wa", "cy", "fy", "wo", "xh", "yi", "yo", "za", "zu"
    ];
    $text_direction = 'ltr';

    if (!isset($data['name']) || empty($data['name'])) {
        $result['error_variables'][] = ['name'];
        $noerror = false;
    }
    if (!isset($data['create_method']) || empty($data['create_method'])) {
        $result['error_variables'][] = ['create_method'];
        $noerror = false;
    } else if ($data['create_method'] === 'import' && !isset($_FILES['import_file']['name']) || $data['create_method'] === 'import' && empty($_FILES['import_file']['name'])) {
        $result['error_variables'][] = ['import_file'];
        $noerror = false;
    }

    if ((int)$max_input_vars < 1999) {
        $result['error_message'] = 'Increase the max_input_vars limit in your server php.ini to 3000 or higher.';
        $result['error_key'] = 'max_input_vars_exceeded';
        $result['error_variables'] = [];
        $noerror = false;
    }

    if ($noerror) {
        $data['name'] = htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8');

        if (isset($data['disabled']) && $data['disabled'] === 'yes') {
            $disabled = 1;
        }

        if ($data['create_method'] === 'import' && isset($_FILES['import_file']['name']) && !empty($_FILES['import_file']['name'])) {
            $jsonfilename = 'importlang_'.random_string(['length' => 6]).'.json';
            $importjson = array();

            $upload_json = [
                'upload' => 'import_file',
                'folder' => 'assets/cache/languages/',
                'saveas' => $jsonfilename,
                'real_path' => true
            ];

            if (files('upload', $upload_json)['result']) {
                $load_json = file_get_contents('assets/cache/languages/'.$jsonfilename);
                if (isJson($load_json)) {
                    $importjson = json_decode($load_json);
                }
            }
        }

        if (isset($importjson->text_direction) && $importjson->text_direction === 'rtl' || isset($importjson->core_align) && $importjson->core_align === 'rtl') {
            $text_direction = 'rtl';
        }

        if (isset($importjson->iso_code) && in_array($importjson->iso_code, $iso_codes)) {
            $iso_code = $importjson->iso_code;
        }

        if (isset($data['text_direction']) && $data['text_direction'] === 'rtl') {
            $text_direction = 'rtl';
        }

        if (isset($data['iso_code']) && in_array($data['iso_code'], $iso_codes)) {
            $iso_code = $data['iso_code'];
        }

        DB::connect()->insert("languages", [
            "name" => $data['name'],
            "iso_code" => $iso_code,
            "text_direction" => $text_direction,
            "disabled" => $disabled,
            "updated_on" => Registry::load('current_user')->time_stamp,
        ]);

        if (!DB::connect()->error) {

            $language_id = DB::connect()->id();
            $insert_data = array();
            $columns = [
                'language_strings.string_id', 'language_strings.string_constant',
                'language_strings.string_value', 'language_strings.string_type',
                'language_strings.skip_update', 'language_strings.skip_cache'
            ];

            $where["language_strings.language_id"] = 1;
            $strings = DB::connect()->select('language_strings', $columns, $where);

            $i = 0;
            foreach ($strings as $string) {
                $string_field = 'string_'.$string['string_id'];
                $string_constant = $string['string_constant'];
                $insert_data[$i]['string_constant'] = $string['string_constant'];
                $insert_data[$i]['string_type'] = $string['string_type'];
                $insert_data[$i]['skip_update'] = $string['skip_update'];
                $insert_data[$i]['skip_cache'] = $string['skip_cache'];
                $insert_data[$i]['language_id'] = $language_id;

                if (isset($importjson->$string_constant) && !empty($importjson->$string_constant)) {
                    $insert_data[$i]['string_value'] = $importjson->$string_constant;
                } else if (isset($data[$string_field]) && !empty($data[$string_field])) {
                    $insert_data[$i]['string_value'] = htmlspecialchars($data[$string_field], ENT_QUOTES, 'UTF-8');
                } else {
                    $insert_data[$i]['string_value'] = $string['string_value'];
                }

                $i = $i+1;
            }

            if (!empty($insert_data)) {
                DB::connect()->insert('language_strings', $insert_data);
            }

            if (isset($_FILES['icon']['name']) && !empty($_FILES['icon']['name'])) {
                if (isImage($_FILES['icon']['tmp_name'])) {

                    $extension = pathinfo($_FILES['icon']['name'])['extension'];
                    $filename = $language_id.Registry::load('config')->file_seperator.random_string(['length' => 6]).'.'.$extension;

                    if (files('upload', ['upload' => 'icon', 'folder' => 'languages', 'saveas' => $filename])['result']) {
                        files('resize_img', ['resize' => 'languages/'.$filename, 'width' => 150, 'height' => 150, 'crop' => true]);
                    }

                }
            }

            cache(['rebuild' => 'languages']);

            $result = array();
            $result['success'] = true;
            $result['todo'] = 'reload';
            $result['reload'] = 'languages';

        } else {
            $result['error_message'] = Registry::load('strings')->went_wrong;
            $result['error_key'] = 'something_went_wrong';
        }

    }
}
?>