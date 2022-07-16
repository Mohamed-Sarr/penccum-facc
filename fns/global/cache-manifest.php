<?php

$settings = extract_json(['file' => 'assets/cache/settings.cache']);

$navigation_scope = Registry::load('config')->navigation_scope;

if ($navigation_scope !== '/' || empty($navigation_scope)) {
    $navigation_scope = $navigation_scope.'/';
}

if (empty($settings->pwa_short_name)) {
    $settings->pwa_short_name = $settings->site_name;
}

if (empty($settings->pwa_name)) {
    $settings->pwa_name = $settings->site_name;
}

if (empty($settings->pwa_description)) {
    $settings->pwa_description = $settings->site_description;
}

if (empty($settings->pwa_display)) {
    $settings->pwa_display = 'standalone';
}

if (empty($settings->pwa_background_color)) {
    $settings->pwa_background_color = '#0000';
}

if (empty($settings->pwa_theme_color)) {
    $settings->pwa_theme_color = '#FFFF';
}


$contents = '{'."\n";
$contents .= '"short_name": "'.$settings->pwa_short_name.'",'."\n";
$contents .= '"name": "'.$settings->pwa_name.'",'."\n";
$contents .= '"description": "'.$settings->pwa_description.'",'."\n";
$contents .= '"icons": ['."\n";

$contents .= '{'."\n";
$contents .= '"src": "'.Registry::load('config')->site_url.'assets/files/defaults/pwa_icon-72x72.png",'."\n";
$contents .= '"type": "image/png",'."\n";
$contents .= '"sizes": "72x72"'."\n";
$contents .= '},'."\n";

$contents .= '{'."\n";
$contents .= '"src": "'.Registry::load('config')->site_url.'assets/files/defaults/pwa_icon-96x96.png",'."\n";
$contents .= '"type": "image/png",'."\n";
$contents .= '"sizes": "96x96"'."\n";
$contents .= '},'."\n";

$contents .= '{'."\n";
$contents .= '"src": "'.Registry::load('config')->site_url.'assets/files/defaults/pwa_icon-128x128.png",'."\n";
$contents .= '"type": "image/png",'."\n";
$contents .= '"sizes": "128x128"'."\n";
$contents .= '},'."\n";

$contents .= '{'."\n";
$contents .= '"src": "'.Registry::load('config')->site_url.'assets/files/defaults/pwa_icon-144x144.png",'."\n";
$contents .= '"type": "image/png",'."\n";
$contents .= '"sizes": "144x144"'."\n";
$contents .= '},'."\n";

$contents .= '{'."\n";
$contents .= '"src": "'.Registry::load('config')->site_url.'assets/files/defaults/pwa_icon-152x152.png",'."\n";
$contents .= '"type": "image/png",'."\n";
$contents .= '"sizes": "152x152"'."\n";
$contents .= '},'."\n";

$contents .= '{'."\n";
$contents .= '"src": "'.Registry::load('config')->site_url.'assets/files/defaults/pwa_icon-192x192.png",'."\n";
$contents .= '"type": "image/png",'."\n";
$contents .= '"sizes": "192x192"'."\n";
$contents .= '},'."\n";

$contents .= '{'."\n";
$contents .= '"src": "'.Registry::load('config')->site_url.'assets/files/defaults/pwa_icon-512x512.png",'."\n";
$contents .= '"type": "image/png",'."\n";
$contents .= '"sizes": "512x512"'."\n";
$contents .= '}'."\n";

$contents .= '],'."\n";
$contents .= '"start_url": "'.$navigation_scope.'",'."\n";
$contents .= '"scope": "'.$navigation_scope.'",'."\n";
$contents .= '"display": "'.$settings->pwa_display.'",'."\n";
$contents .= '"background_color": "'.$settings->pwa_background_color.'",'."\n";
$contents .= '"theme_color": "'.$settings->pwa_theme_color.'",'."\n";
$contents .= '"dir": "'.Registry::load('strings')->text_direction.'"'."\n";
$contents .= '}'."\n";

$cachefile = 'manifest.json';

if (file_exists($cachefile)) {
    unlink($cachefile);
}

$cachefile = fopen($cachefile, "w");
fwrite($cachefile, $contents);
fclose($cachefile);
$result = true;