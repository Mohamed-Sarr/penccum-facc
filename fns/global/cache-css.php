<?php

require_once 'fns/minify/load.php';
use MatthiasMullie\Minify;

$import_css = false;

$css_files = [
    'assets/thirdparty/bootstrap/bootstrap.min.css',
    'assets/thirdparty/animate/animate.min.css',

    'assets/thirdparty/videojs/video-js.min.css',
    'assets/thirdparty/videojs/city.css',
    'assets/thirdparty/viewerjs/viewer.min.css',
    'assets/thirdparty/colorpicker/dist/css/bootstrap-colorpicker.min.css',
    'assets/thirdparty/summernote/summernote-lite.css',
    'assets/thirdparty/bootstrap-icons/bootstrap-icons.css',

    'assets/css/chat_page/main.css',
    'assets/css/chat_page/side_navigation.css',
    'assets/css/chat_page/aside.css',
    'assets/css/chat_page/middle.css',
    'assets/css/chat_page/info_panel.css',
    'assets/css/chat_page/statistics.css',
    'assets/css/chat_page/audio_player_box.css',
    'assets/css/chat_page/form.css',
    'assets/css/chat_page/loader.css',
    'assets/css/chat_page/popup-dialog.css',
    'assets/css/chat_page/responsive.css',
    'assets/css/chat_page/rtl_language.css',
    'assets/css/common/scroll.css',
    'assets/css/common/custom_css.css',

];

if ($import_css) {
    $css = '';
    foreach ($css_files as $cssfile) {
        $css .= '@import "'.$cssfile.'";';
    }
    $css_files = $css;
}

$minifier = new Minify\CSS();
$minifier->add($css_files);

$minifiedPath = 'assets/css/combined_css_chat_page.css';
$minifier->minify($minifiedPath);


$css_files = [
    'assets/thirdparty/bootstrap/bootstrap.min.css',
    'assets/thirdparty/animate/animate.min.css',

    'assets/css/entry_page/style.css',
    'assets/css/entry_page/rtl_language.css',
    'assets/css/common/scroll.css',
    'assets/css/common/custom_css.css',
];

if ($import_css) {
    $css = '';
    foreach ($css_files as $cssfile) {
        $css .= '@import "'.$cssfile.'";';
    }
    $css_files = $css;
}

$minifier = new Minify\CSS();
$minifier->add($css_files);

$minifiedPath = 'assets/css/combined_css_entry_page.css';
$minifier->minify($minifiedPath);


$css_files = [
    'assets/thirdparty/bootstrap/bootstrap.min.css',
    'assets/thirdparty/animate/animate.min.css',
    'assets/thirdparty/bootstrap-icons/bootstrap-icons.css',
    'assets/css/landing_page/style.css',
    'assets/css/landing_page/rtl_language.css',
    'assets/css/common/scroll.css',
    'assets/css/common/custom_css.css',
];

if ($import_css) {
    $css = '';
    foreach ($css_files as $cssfile) {
        $css .= '@import "'.$cssfile.'";';
    }
    $css_files = $css;
}

$minifier = new Minify\CSS();
$minifier->add($css_files);

$minifiedPath = 'assets/css/combined_css_landing_page.css';
$minifier->minify($minifiedPath);


$result = true;