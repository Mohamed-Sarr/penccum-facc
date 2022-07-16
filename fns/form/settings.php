<?php

if (role(['permissions' => ['super_privileges' => 'core_settings']])) {
    include 'fns/filters/load.php';

    $form = array();

    if (!isset($load['category']) || empty($load['category'])) {
        return false;
    } else {
        $load["category"] = preg_replace("/[^a-zA-Z0-9_]+/", "", $load["category"]);
    }

    if (!empty($load['category'])) {

        $category = $load['category'];

        $form['loaded'] = new stdClass();
        $form['loaded']->title = Registry::load('strings')->$category;
        $form['loaded']->button = Registry::load('strings')->update;

        $columns = [
            'settings.setting_id', 'settings.setting', 'settings.options', 'settings.value',
            'settings.required', 'settings.field_attributes'
        ];

        $where['settings.category'] = $load['category'];
        $where['ORDER'] = ['settings_order' => 'ASC'];

        $settings = DB::connect()->select('settings', $columns, $where);

        $form['fields'] = new stdClass();


        if ($load['category'] === 'notification_settings') {
            $form['fields']->push_notification_icon = [
                "title" => Registry::load('strings')->push_notification_icon, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse', "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];
        }

        foreach ($settings as $setting) {

            $setting_name = $setting['setting'];
            $setting_value = $setting['value'];
            $setting_options = $setting['options'];

            if (!empty($setting_options) && mb_strpos($setting_options, '[multi_select]') !== false) {

                $setting_options = str_replace('[multi_select]', '', $setting_options);
                $options = json_decode($setting_options);

                $form['fields']->$setting_name = [
                    "title" => Registry::load('strings')->$setting_name, "tag" => 'checkbox', "class" => 'field'
                ];
                $setting_options = array();
                foreach ($options as $option) {
                    $setting_options[$option] = Registry::load('strings')->$option;
                }
                $form['fields']->$setting_name['options'] = $setting_options;
                $form['fields']->$setting_name['values'] = $setting_value;

            } else if (!empty($setting_options) && isJson($setting_options) || $setting_options === 'select') {

                if ($setting_name === 'default_timezone') {
                    $setting_options = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
                } else if ($setting_name === 'default_language') {

                    $languages = DB::connect()->select('languages', ['languages.language_id', 'languages.name']);
                    $languages = array_column($languages, 'name', 'language_id');

                    $setting_options = $languages;

                } else if ($setting_name === 'notification_tone') {
                    $setting_options = array();
                    $sounds = glob('assets/files/sound_notifications/*');
                    foreach ($sounds as $sound) {
                        $sound_title = str_replace('-', ' ', $sound);
                        $setting_options[$sound] = ucwords(basename($sound_title, '.mp3'));
                    }
                } else if ($setting_name === 'default_font') {
                    $setting_options = array();
                    $fonts = glob('assets/fonts/*');
                    foreach ($fonts as $font) {
                        $font = basename($font);
                        if ($font !== 'iconicfont') {
                            $setting_options[$font] = ucwords($font);
                        }
                    }
                } else {
                    $options = json_decode($setting_options);
                    $setting_options = array();
                    foreach ($options as $option) {
                        if (is_numeric($option)) {
                            $setting_options[$option] = $option;
                        } else {
                            $setting_options[$option] = Registry::load('strings')->$option;
                        }
                    }
                }

                $form['fields']->$setting_name = [
                    "title" => Registry::load('strings')->$setting_name, "tag" => 'select', "class" => 'field', "value" => $setting_value,
                ];
                $form['fields']->$setting_name['options'] = $setting_options;

                if ($setting_name === 'default_timezone') {
                    $form['fields']->$setting_name['optionkey'] = 'optionvalue';
                }

                if ($setting_name === 'notification_tone') {
                    $form['fields']->$setting_name['attributes']['class'] = 'preview_audio_file';
                    $form['fields']->$setting_name['attributes']['audio_location'] = Registry::load('config')->site_url;
                }

            } else {
                if ($setting_options === 'textarea') {

                    if ($setting_name === 'disallowed_slugs') {
                        if (!empty($setting_value)) {
                            $setting_value = @unserialize($setting_value);
                            $setting_value = implode(PHP_EOL, $setting_value);
                        }
                    }

                    $form['fields']->$setting_name = [
                        "title" => Registry::load('strings')->$setting_name, "tag" => 'textarea', "class" => 'field', "closetag" => true, "value" => $setting_value,
                    ];
                } else {
                    $input_type = 'text';

                    if (!empty($setting_options)) {
                        $input_type = $setting_options;
                    }

                    $form['fields']->$setting_name = [
                        "title" => Registry::load('strings')->$setting_name, "tag" => 'input', "type" => $input_type, "class" => 'field', "value" => $setting_value,
                    ];
                }
            }
            if (!empty($setting['field_attributes'])) {
                $field_attributes = json_decode($setting['field_attributes']);
                if (!empty($field_attributes)) {
                    foreach ($field_attributes as $attribute_index => $field_attribute) {

                        if ($attribute_index === 'class') {
                            $form['fields']->$setting_name[$attribute_index] = $field_attribute;
                        } else {
                            $form['fields']->$setting_name['attributes'][$attribute_index] = $field_attribute;
                        }
                    }
                }
            }

            if (!empty($setting['required'])) {
                $form['fields']->$setting_name['required'] = true;
            }
        }


        if ($load['category'] === 'realtime_settings') {
            $form['fields']->clear_realtime_activity_logs = [
                "title" => Registry::load('strings')->clear_realtime_activity_logs, "tag" => 'select', "class" => 'field',
                "options" => ["yes" => Registry::load('strings')->yes, "no" => Registry::load('strings')->no]
            ];
        }


        if ($load['category'] === 'pwa_settings') {
            $form['fields']->pwa_icon = [
                "title" => Registry::load('strings')->pwa_icon, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse', "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];
        }

        if ($load['category'] === 'email_settings') {
            $form['fields']->email_logo = [
                "title" => Registry::load('strings')->email_logo, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse', "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];
        }

        if ($load['category'] === 'general_settings') {
            $form['fields']->favicon = [
                "title" => Registry::load('strings')->favicon, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse', "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];

            $form['fields']->social_share_image = [
                "title" => Registry::load('strings')->social_share_image, "tag" => 'input', "type" => 'file',
                "class" => 'field filebrowse', "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
            ];
        }

        $form['fields']->category = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $category
        ];
        $form['fields']->update = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "settings"
        ];
    }
}
?>