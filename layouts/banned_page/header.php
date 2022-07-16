<!doctype html>
<html lang="<?php echo Registry::load('strings')->iso_code ?>" dir="<?php echo Registry::load('strings')->text_direction ?>" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-status-bar-style" content="black" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no shrink-to-fit=no">
    <title><?php echo Registry::load('strings')->banned_page_title.' - '.Registry::load('settings')->site_name; ?></title>
    <meta name="description" content="<?php echo Registry::load('strings')->banned_page_description; ?>">
    <link rel="shortcut icon" type="image/png" href="<?php echo Registry::load('config')->site_url.'assets/files/defaults/favicon.png'; ?>" />
    <link href="<?php echo Registry::load('config')->site_url ?>assets/thirdparty/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo Registry::load('config')->site_url ?>assets/css/error_page/error_page.css" rel="stylesheet">
    <link href="<?php echo Registry::load('config')->site_url ?>assets/fonts/<?php echo Registry::load('settings')->default_font ?>/font.css" rel="stylesheet">

</head>
