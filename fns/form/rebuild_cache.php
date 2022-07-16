<?php

if (role(['permissions' => ['super_privileges' => 'core_settings']])) {
    $form = array();
    $form['loaded'] = new stdClass();
    $form['loaded']->title = Registry::load('strings')->rebuild_cache;
    $form['loaded']->button = Registry::load('strings')->update;



    $form['fields'] = new stdClass();

    $form['fields']->update = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "cache_system"
    ];
    
    $form['fields']->rebuild = [
        "title" => Registry::load('strings')->rebuild, "tag" => 'checkbox', "class" => 'field'
    ];

    $form['fields']->rebuild['options'] = [
        "style_sheets" => Registry::load('strings')->style_sheets,
        "javascript_files" => Registry::load('strings')->javascript_files,
        "sitemap" => Registry::load('strings')->sitemap,
        "web_app_manifest" => Registry::load('strings')->web_app_manifest,
        "core_settings" => Registry::load('strings')->core_settings,
        "languages" => Registry::load('strings')->languages,
        "site_roles" => Registry::load('strings')->site_roles,
        "group_roles" => Registry::load('strings')->group_roles,
    ];
}
?>