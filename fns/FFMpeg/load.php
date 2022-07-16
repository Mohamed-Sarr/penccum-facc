<?php

if (!(PHP_VERSION_ID >= 80100)) {
    include('fns/FFMpeg/php_older_version/autoload.php');
} else {
    include('fns/FFMpeg/php_latest_version/autoload.php');
}

$ffmpeg_path = exec('which ffmpeg');
$ffprobe_path = exec('which ffprobe');

if (isset(Registry::load('settings')->ffmpeg_binaries_path) && !empty(Registry::load('settings')->ffmpeg_binaries_path)) {
    $ffmpeg_path = Registry::load('settings')->ffmpeg_binaries_path;
}

if (isset(Registry::load('settings')->ffprobe_binaries_path) && !empty(Registry::load('settings')->ffprobe_binaries_path)) {
    $ffprobe_path = Registry::load('settings')->ffprobe_binaries_path;
}

$ffmpeg = FFMpeg\FFMpeg::create(array(
    'ffmpeg.binaries' => $ffmpeg_path,
    'ffprobe.binaries' => $ffprobe_path
));