<?php

$contents = '';
$contents .= '<?xml version="1.0" encoding="UTF-8"?>'."\n";
$contents .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

$contents .= '<url>'."\n";
$contents .= '<loc>'.Registry::load('config')->site_url.'</loc>'."\n";
$contents .= '<lastmod>'.date('c', time()).'</lastmod>'."\n";
$contents .= '<changefreq>daily</changefreq>'."\n";
$contents .= '</url>'."\n\n";

$custom_pages = DB::connect()->select('custom_pages', ['slug', 'updated_on'], ['custom_pages.disabled' => 0]);

foreach ($custom_pages as $custom_page) {
    $updated_on = strtotime($custom_page['updated_on']);
    $contents .= '<url>'."\n";
    $contents .= '<loc>'.Registry::load('config')->site_url.$custom_page['slug'].'/</loc>'."\n";
    $contents .= '<lastmod>'.date('c', time()).'</lastmod>'."\n";
    $contents .= '<changefreq>daily</changefreq>'."\n";
    $contents .= '</url>'."\n\n";
}

$columns = $join = $where = null;
$columns = [
    'groups.group_id', 'groups.slug', 'groups.updated_on'
];
$where["groups.suspended"] = 0;
$where["AND"] = [
    "OR" => [
        "groups.password(password_null)" => null,
        "groups.password(password_empty)" => '',
        "groups.password(password_zero)" => "0"
    ],
    "groups.secret_group" => "0"
];
$where["ORDER"] = ["groups.pin_group" => "DESC", "groups.updated_on" => "DESC"];
$where["LIMIT"] = 100;

$groups = DB::connect()->select('groups', $columns, $where);

foreach ($groups as $group) {

    if (empty($group['slug'])) {
        $group['slug'] = 'group/'.$group['group_id'];
    }
    $updated_on = strtotime($group['updated_on']);
    $contents .= '<url>'."\n";
    $contents .= '<loc>'.Registry::load('config')->site_url.$group['slug'].'/</loc>'."\n";
    $contents .= '<lastmod>'.date('c', time()).'</lastmod>'."\n";
    $contents .= '<changefreq>daily</changefreq>'."\n";
    $contents .= '</url>'."\n\n";
}

$contents .= '</urlset>';

$cachefile = 'assets/cache/sitemap.cache';

if (file_exists($cachefile)) {
    unlink($cachefile);
}

$cachefile = fopen($cachefile, "w");
fwrite($cachefile, $contents);
fclose($cachefile);


$sitemapUrl = Registry::load('config')->site_url.'sitemap/';
$ping_urls = [
    'https://www.google.com/ping?sitemap=',
];

foreach ($ping_urls as $ping_url) {
    $ping_url .= $sitemapUrl;
    $curl_request = curl_init($ping_url);
    curl_setopt($curl_request, CURLOPT_HEADER, 0);
    curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
    curl_exec($curl_request);
    $httpCode = curl_getinfo($curl_request, CURLINFO_HTTP_CODE);
    curl_close($curl_request);
}