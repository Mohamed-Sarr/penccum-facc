<?php

if (role(['permissions' => ['super_privileges' => 'slideshows']])) {

    $form = array();
    include 'fns/filters/load.php';
    include 'fns/files/load.php';

    $location = 0;
    $form['loaded'] = new stdClass();
    $form['fields'] = new stdClass();

    if (isset($load["slideshow"])) {
        $load["slideshow"] = sanitize_filename($load['slideshow']);
        $location = 'assets/files/slideshows/'.$load["slideshow"].'/';
    }

    if (isset($load["slideshow"]) && !empty($load["slideshow"]) && file_exists($location)) {

        $form['fields']->slideshow = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load["slideshow"]
        ];

        $form['loaded']->title = Registry::load('strings')->add_images;
        $form['loaded']->button = Registry::load('strings')->add;

        $slideshow_name = ucwords(str_replace('_', ' ', $load["slideshow"]));

        $form['fields']->process = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "add"
        ];

        $form['fields']->add = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "slideshow_images"
        ];

        $form['fields']->name = [
            "title" => Registry::load('strings')->slideshow, "tag" => 'input', "type" => "text", "class" => 'field',
            "value" => $slideshow_name, "attributes" => ['disabled' => 1]
        ];

        $form['fields']->images = [
            "title" => Registry::load('strings')->images, "tag" => 'input', "type" => 'file', 'multi_select' => true, "class" => 'field filebrowse',
            "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
        ];
        $form['fields']->images['infotip'] = Registry::load('strings')->infotip_select_multiple_files;
    }
}
?>