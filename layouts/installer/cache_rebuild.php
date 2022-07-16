<?php

$cache_array = array();
$columns = [
    'gr_settings.setting', 'gr_settings.value', 'gr_settings.options'
];
$settings = $db_instance->select('gr_settings', $columns);

foreach ($settings as $setting) {
    $settingname = $setting['setting'];
    $setting_options = $setting['options'];
    if ($settingname === 'default_timezone' && empty($setting['value']) || $settingname === 'default_timezone' && $setting['value'] === 'Auto') {
        $cache_array[$settingname] = "Australia/Sydney";
    } else {
        if (!empty($setting_options) && mb_strpos($setting_options, '[multi_select]') !== false || $settingname === 'disallowed_slugs') {
            if (!empty($setting['value'])) {
                $setting['value'] = @unserialize($setting['value']);
                if ($setting['value'] === false) {
                    $setting['value'] = array();
                } else {
                    $setting_value = array();
                    foreach ($setting['value'] as $value) {
                        $setting_value[$value] = $value;
                    }
                    $setting['value'] = $setting_value;
                }
            }
        }

        $cache_array[$settingname] = $setting['value'];
    }
}

$cache_array['pause_userlog'] = random_string('10');
$cache_array['cache_timestamp'] = strtotime("now");

$cache = json_encode($cache_array);
$cachefile = 'assets/cache/settings.cache';

if (file_exists($cachefile)) {
    unlink($cachefile);
}

$cachefile = fopen($cachefile, "w");
fwrite($cachefile, $cache);
fclose($cachefile);