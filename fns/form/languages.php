<?php

use Medoo\Medoo;

$form = array();

if (role(['permissions' => ['languages' => ['create', 'edit']], 'condition' => 'OR'])) {

    $todo = 'add';
    $form['loaded'] = new stdClass();
    $form['fields'] = new stdClass();

    if (isset($load["language_id"])) {
        $load["language_id"] = filter_var($load["language_id"], FILTER_SANITIZE_NUMBER_INT);
    }

    $columns = [
        'language_strings.string_id', 'language_strings.string_constant',
        'language_strings.string_value', 'language_strings.string_type',
        'languages.name', 'languages.disabled', 'languages.iso_code', 'languages.text_direction',
    ];
    $join["[>]languages"] = ["language_strings.language_id" => "language_id"];

    if (isset($load["language_id"]) && !empty($load["language_id"]) && role(['permissions' => ['languages' => 'edit']])) {
        $where["language_strings.language_id"] = $load["language_id"];
    } else {
        $where["language_strings.language_id"] = 1;
    }

    $where["language_strings.skip_update"] = 0;
    $where["language_strings.skip_cache"] = 0;

    $strings = DB::connect()->select('language_strings', $join, $columns, $where);

    if (isset($load["language_id"]) && !empty($load["language_id"]) && role(['permissions' => ['languages' => 'edit']])) {

        $todo = 'update';

        if (!isset($strings[0])) {
            return false;
        }

        $form['fields']->language_id = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load["language_id"]
        ];

        $form['loaded']->title = Registry::load('strings')->edit_language;
        $form['loaded']->button = Registry::load('strings')->update;
    } else {
        $form['loaded']->title = Registry::load('strings')->add_language;
        $form['loaded']->button = Registry::load('strings')->create;
    }


    $form['fields']->process = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $todo
    ];

    $form['fields']->$todo = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "languages"
    ];

    $form['fields']->name = [
        "title" => Registry::load('strings')->name, "tag" => 'input', "type" => "text", "class" => 'field',
        "placeholder" => Registry::load('strings')->name,
    ];

    $form['fields']->icon = [
        "title" => Registry::load('strings')->icon_img, "tag" => 'input', "type" => 'file', "class" => 'field filebrowse',
        "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
    ];

    $iso_codes = [
        'ab' => 'Abkhazian', 'aa' => 'Afar', 'af' => 'Afrikaans', 'ak' => 'Akan', 'sq' => 'Albanian', 'am' => 'Amharic', 'ar' => 'Arabic',
        'an' => 'Aragonese', 'hy' => 'Armenian', 'as' => 'Assamese', 'av' => 'Avaric', 'ae' => 'Avestan', 'ay' => 'Aymara', 'az' => 'Azerbaijani',
        'bm' => 'Bambara', 'ba' => 'Bashkir', 'eu' => 'Basque', 'be' => 'Belarusian', 'bn' => 'Bengali', 'bh' => 'Bihari languages',
        'bi' => 'Bislama', 'bs' => 'Bosnian', 'br' => 'Breton', 'bg' => 'Bulgarian', 'my' => 'Burmese', 'ca' => 'Catalan',
        'km' => 'Central Khmer', 'ch' => 'Chamorro', 'ce' => 'Chechen', 'ny' => 'Chewa', 'zh' => 'Chinese',
        'cu' => 'Church Slavonic', 'cv' => 'Chuvash', 'kw' => 'Cornish', 'co' => 'Corsican', 'cr' => 'Cree',
        'hr' => 'Croatian', 'cs' => 'Czech', 'da' => 'Danish', 'dv' => 'Maldivian', 'nl' => 'Dutch, Flemish',
        'dz' => 'Dzongkha', 'en' => 'English', 'eo' => 'Esperanto', 'et' => 'Estonian', 'ee' => 'Ewe', 'fo' => 'Faroese',
        'fj' => 'Fijian', 'fi' => 'Finnish', 'fr' => 'French', 'ff' => 'Fulah', 'gd' => 'Gaelic', 'gl' => 'Galician',
        'lg' => 'Ganda', 'ka' => 'Georgian', 'de' => 'German', 'ki' => 'Gikuyu, Kikuyu', 'el' => 'Greek (Modern)',
        'kl' => 'Greenlandic', 'gn' => 'Guarani', 'gu' => 'Gujarati', 'ht' => 'Haitian', 'ha' => 'Hausa',
        'he' => 'Hebrew', 'hz' => 'Herero', 'hi' => 'Hindi', 'ho' => 'Hiri Motu', 'hu' => 'Hungarian', 'is' => 'Icelandic', 'io' => 'Ido',
        'ig' => 'Igbo', 'id' => 'Indonesian', 'ia' => 'Interlingua', 'ie' => 'Interlingue', 'iu' => 'Inuktitut',
        'ik' => 'Inupiaq', 'ga' => 'Irish', 'it' => 'Italian', 'ja' => 'Japanese', 'jv' => 'Javanese', 'kn' => 'Kannada',
        'kr' => 'Kanuri', 'ks' => 'Kashmiri', 'kk' => 'Kazakh', 'rw' => 'Kinyarwanda', 'kv' => 'Komi', 'kg' => 'Kongo', 'ko' => 'Korean',
        'kj' => 'Kwanyama', 'ku' => 'Kurdish', 'ky' => 'Kyrgyz', 'lo' => 'Lao', 'la' => 'Latin', 'lv' => 'Latvian',
        'lb' => 'Letzeburgesch', 'li' => 'Limburgish', 'ln' => 'Lingala', 'lt' => 'Lithuanian',
        'lu' => 'Luba-Katanga', 'mk' => 'Macedonian', 'mg' => 'Malagasy', 'ms' => 'Malay', 'ml' => 'Malayalam', 'mt' => 'Maltese',
        'gv' => 'Manx', 'mi' => 'Maori', 'mr' => 'Marathi', 'mh' => 'Marshallese', 'ro' => 'Moldovan', 'mn' => 'Mongolian',
        'na' => 'Nauru', 'nv' => 'Navajo, Navaho', 'nd' => 'Northern Ndebele', 'ng' => 'Ndonga', 'ne' => 'Nepali', 'se' => 'Northern Sami',
        'no' => 'Norwegian', 'nb' => 'Norwegian Bokmål', 'nn' => 'Norwegian Nynorsk', 'ii' => 'Nuosu, Sichuan Yi', 'oc' => 'Occitan (post 1500)',
        'oj' => 'Ojibwa', 'or' => 'Oriya', 'om' => 'Oromo', 'os' => 'Ossetian, Ossetic', 'pi' => 'Pali', 'pa' => 'Panjabi, Punjabi',
        'ps' => 'Pashto, Pushto', 'fa' => 'Persian', 'pl' => 'Polish', 'pt' => 'Portuguese', 'qu' => 'Quechua', 'rm' => 'Romansh',
        'rn' => 'Rundi', 'ru' => 'Russian', 'sm' => 'Samoan', 'sg' => 'Sango', 'sa' => 'Sanskrit', 'sc' => 'Sardinian', 'sr' => 'Serbian',
        'sn' => 'Shona', 'sd' => 'Sindhi', 'si' => 'Sinhala', 'sk' => 'Slovak', 'sl' => 'Slovenian', 'so' => 'Somali',
        'st' => 'Sotho', 'nr' => 'South Ndebele', 'es' => 'Spanish', 'su' => 'Sundanese', 'sw' => 'Swahili',
        'ss' => 'Swati', 'sv' => 'Swedish', 'tl' => 'Tagalog', 'ty' => 'Tahitian', 'tg' => 'Tajik', 'ta' => 'Tamil', 'tt' => 'Tatar',
        'te' => 'Telugu', 'th' => 'Thai', 'bo' => 'Tibetan', 'ti' => 'Tigrinya', 'to' => 'Tonga (Tonga Islands)', 'ts' => 'Tsonga',
        'tn' => 'Tswana', 'tr' => 'Turkish', 'tk' => 'Turkmen', 'tw' => 'Twi', 'ug' => 'Uyghur', 'uk' => 'Ukrainian',
        'ur' => 'Urdu', 'uz' => 'Uzbek', 've' => 'Venda', 'vi' => 'Vietnamese', 'vo' => 'Volap_k', 'wa' => 'Walloon', 'cy' => 'Welsh',
        'fy' => 'Western Frisian', 'wo' => 'Wolof', 'xh' => 'Xhosa', 'yi' => 'Yiddish', 'yo' => 'Yoruba', 'za' => 'Zhuang',
        'zu' => 'Zulu'
    ];

    $form['fields']->iso_code = [
        "title" => Registry::load('strings')->iso_language_code, "tag" => 'select', "class" => 'field', "options" => $iso_codes
    ];

    $form['fields']->create_method = [
        "title" => Registry::load('strings')->select_an_option, "tag" => 'select', "class" => 'field showfieldon'
    ];
    $form['fields']->create_method["attributes"] = [
        "hideclass" => "language_string", "fieldclass" => "import_json",
        "checkvalue" => "import", "removefield_onsubmit" => true
    ];

    $form['fields']->create_method['options'] = [
        "create" => Registry::load('strings')->create,
        "import" => Registry::load('strings')->import,
    ];

    $form['fields']->import_file = [
        "title" => Registry::load('strings')->import_json, "tag" => 'input', "type" => 'file', "class" => 'field filebrowse import_json d-none', "accept" => 'application/JSON'
    ];

    $form['fields']->text_direction = [
        "title" => Registry::load('strings')->language_text_direction, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->text_direction['options'] = [
        "ltr" => Registry::load('strings')->ltr,
        "rtl" => Registry::load('strings')->rtl,
    ];

    if (isset($load["language_id"]) && !empty($load["language_id"]) && role(['permissions' => ['languages' => 'edit']])) {

        $form['fields']->set_as_default = [
            "title" => Registry::load('strings')->set_as_default, "tag" => 'select', "class" => 'field'
        ];
        $form['fields']->set_as_default['options'] = [
            "yes" => Registry::load('strings')->yes,
            "no" => Registry::load('strings')->no,
        ];
    }

    $form['fields']->disabled = [
        "title" => Registry::load('strings')->disabled, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->disabled['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    foreach ($strings as $string) {
        $string_field = 'string_'.$string['string_id'];

        if ($string['string_type'] === 'one-line') {
            $form['fields']->$string_field = [
                "title" => $string['string_constant'], "tag" => 'input', "type" => "text", "class" => 'field language_string d-none',
                "value" => $string['string_value'],
            ];
        } else {
            $form['fields']->$string_field = [
                "title" => $string['string_constant'], "tag" => 'textarea', "class" => 'field language_string d-none',
                "value" => $string['string_value'],
            ];
            $form['fields']->$string_field["attributes"] = ["rows" => 6];
        }
    }

    if (isset($load["language_id"]) && !empty($load["language_id"]) && role(['permissions' => ['languages' => 'edit']])) {

        $disabled = 'no';

        if ((int)$string['disabled'] === 1) {
            $disabled = 'yes';
        }

        $form['fields']->create_method['options'] = [
            "edit" => Registry::load('strings')->edit,
            "import" => Registry::load('strings')->import,
        ];

        if ((int)$load['language_id'] === (int)Registry::load('settings')->default_language) {
            $form['fields']->set_as_default["value"] = 'yes';
        }

        $form['fields']->name["value"] = $string['name'];
        $form['fields']->disabled["value"] = $disabled;
        $form['fields']->text_direction["value"] = $string['text_direction'];
        $form['fields']->iso_code["value"] = $string['iso_code'];
    }
}
?>