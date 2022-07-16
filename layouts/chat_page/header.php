<?php
include('fns/meta_tags/load.php');
$meta_tags = meta_tags();
$cache_timestamp = '?cache='.Registry::load('settings')->cache_timestamp;
?>
<!doctype html>
<html lang="<?php echo Registry::load('strings')->iso_code ?>" dir="<?php echo Registry::load('strings')->text_direction ?>">
<head>
    <meta charset="utf-8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-status-bar-style" content="black" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no shrink-to-fit=no">

    <title><?php echo $meta_tags['title']; ?></title>
    <meta name="default-title" content="<?php echo $meta_tags['default_title']; ?>">
    <meta name="description" content="<?php echo $meta_tags['description']; ?>">

    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo($meta_tags['url']); ?>">
    <meta property="og:title" content="<?php echo $meta_tags['title']; ?>">
    <meta property="og:description" content="<?php echo $meta_tags['description']; ?>">
    <meta property="og:image" content="<?php echo $meta_tags['social_share_image'].$cache_timestamp; ?>">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo($meta_tags['url']); ?>">
    <meta property="twitter:title" content="<?php echo $meta_tags['title']; ?>">
    <meta property="twitter:description" content="<?php echo $meta_tags['description']; ?>">
    <meta property="twitter:image" content="<?php echo $meta_tags['social_share_image'].$cache_timestamp; ?>">
    <base href="<?php echo Registry::load('config')->site_url; ?>">

    <link rel="shortcut icon" type="image/png" href="<?php echo Registry::load('config')->site_url.'assets/files/defaults/favicon.png'.$cache_timestamp; ?>" />

    <?php if (Registry::load('settings')->progressive_web_application === 'enable') {
        ?>
        <link rel='manifest' href='<?php echo Registry::load('config')->site_url; ?>manifest.json'>
        <link rel="apple-touch-icon" href="<?php echo Registry::load('config')->site_url.'assets/files/defaults/pwa_icon-72x72.png'.$cache_timestamp; ?>">
        <link rel="apple-touch-icon" href="<?php echo Registry::load('config')->site_url.'assets/files/defaults/pwa_icon-96x96.png'.$cache_timestamp; ?>">
        <link rel="apple-touch-icon" href="<?php echo Registry::load('config')->site_url.'assets/files/defaults/pwa_icon-128x128.png'.$cache_timestamp; ?>">
        <link rel="apple-touch-icon" href="<?php echo Registry::load('config')->site_url.'assets/files/defaults/pwa_icon-144x144.png'.$cache_timestamp; ?>">
        <link rel="apple-touch-icon" href="<?php echo Registry::load('config')->site_url.'assets/files/defaults/pwa_icon-152x152.png'.$cache_timestamp; ?>">
        <link rel="apple-touch-icon" href="<?php echo Registry::load('config')->site_url.'assets/files/defaults/pwa_icon-192x192.png'.$cache_timestamp; ?>">
        <link rel="apple-touch-icon" href="<?php echo Registry::load('config')->site_url.'assets/files/defaults/pwa_icon-512x512.png'.$cache_timestamp; ?>">
        <meta name="apple-mobile-web-app-status-bar" content="<?php echo Registry::load('settings')->pwa_background_color; ?>">
        <meta name="theme-color" content="<?php echo Registry::load('settings')->pwa_theme_color; ?>">
        <?php
    } ?>
    
    <link href="<?php echo Registry::load('config')->site_url.'assets/icon_font/style.css'.$cache_timestamp; ?>" rel="stylesheet">
   
    <?php
    if (Registry::load('current_user')->color_scheme === 'dark_mode') {
        ?>
        <link href="<?php echo Registry::load('config')->site_url.'assets/css/common/dark_mode_css_variables.css'.$cache_timestamp; ?>" rel="stylesheet">
        <?php
    } else {
        ?>
        <link href="<?php echo Registry::load('config')->site_url.'assets/css/common/css_variables.css'.$cache_timestamp; ?>" rel="stylesheet">
        <?php
    } ?>

    <link href="<?php echo Registry::load('config')->site_url.'assets/css/combined_css_chat_page.css'.$cache_timestamp; ?>" rel="stylesheet">
    <link href="<?php echo Registry::load('config')->site_url.'assets/css/chat_page/emojis_min.css'.$cache_timestamp; ?>" rel="stylesheet">

    <link href="<?php echo Registry::load('config')->site_url.'assets/fonts/'.Registry::load('settings')->default_font.'/font.css'.$cache_timestamp; ?>" rel="stylesheet">


    <?php include 'assets/headers_footers/chat_page/header.php'; ?>


</head>