<?php

if (role(['permissions' => ['site_adverts' => 'create']]) || role(['permissions' => ['site_adverts' => 'edit']])) {
    $form = array();

    $todo = 'add';
    $form['loaded'] = new stdClass();
    $form['fields'] = new stdClass();

    if (isset($load["site_advert_id"])) {

        $todo = 'update';

        $columns = [
            'site_advertisements.site_advert_name', 'site_advertisements.disabled',
            'site_advertisements.site_advert_placement', 'site_advertisements.site_advert_max_height',
            'site_advertisements.site_advert_content', 'site_advertisements.site_advert_min_height',
        ];

        $where["site_advertisements.site_advert_id"] = $load["site_advert_id"];
        $where["LIMIT"] = 1;

        $advert = DB::connect()->select('site_advertisements', $columns, $where);

        if (!isset($advert[0])) {
            return false;
        } else {
            $advert = $advert[0];
        }

        $form['fields']->site_advert_id = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load["site_advert_id"]
        ];

        $form['loaded']->title = Registry::load('strings')->edit_advert;
        $form['loaded']->button = Registry::load('strings')->update;
    } else {
        $form['loaded']->title = Registry::load('strings')->create_advert;
        $form['loaded']->button = Registry::load('strings')->create;
    }

    $form['fields']->$todo = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "site_adverts"
    ];

    $form['fields']->advert_name = [
        "title" => Registry::load('strings')->advert_name, "tag" => 'input', "type" => "text", "class" => 'field',
        "placeholder" => Registry::load('strings')->advert_name,
    ];

    $form['fields']->advert_min_height = [
        "title" => Registry::load('strings')->advert_min_height, "tag" => 'input', "type" => "number", "class" => 'field',
        "placeholder" => Registry::load('strings')->advert_min_height,
    ];

    $form['fields']->advert_min_height["value"] = 150;

    $form['fields']->advert_max_height = [
        "title" => Registry::load('strings')->advert_max_height, "tag" => 'input', "type" => "number", "class" => 'field',
        "placeholder" => Registry::load('strings')->advert_max_height,
    ];

    $form['fields']->advert_max_height["value"] = 150;

    $form['fields']->advert_placement = [
        "title" => Registry::load('strings')->advert_placement, "tag" => 'select', "class" => 'field'
    ];

    $form['fields']->advert_placement['options'] = [
        "left_content_block" => Registry::load('strings')->left_content_block,
        "info_panel" => Registry::load('strings')->info_panel,
        "welcome_screen" => Registry::load('strings')->welcome_screen,
        "entry_page_form_header" => Registry::load('strings')->entry_page_form_header,
        "entry_page_form_footer" => Registry::load('strings')->entry_page_form_footer,
        "landing_page_groups_section" => Registry::load('strings')->landing_page_groups_section,
        "landing_page_faq_section" => Registry::load('strings')->landing_page_faq_section,
    ];

    $form['fields']->advert_content = [
        "title" => Registry::load('strings')->advert_content, "tag" => 'textarea',
        "class" => 'field page_content',
        "placeholder" => Registry::load('strings')->advert_content
    ];

    $form['fields']->advert_content["attributes"] = ["rows" => 6];

    $form['fields']->disabled = [
        "title" => Registry::load('strings')->disabled, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->disabled['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    if (isset($load["site_advert_id"])) {
        $disabled = 'no';

        if ((int)$advert['disabled'] === 1) {
            $disabled = 'yes';
        }

        unset($form['fields']->advert_content["placeholder"]);

        $form['fields']->advert_name["value"] = $advert['site_advert_name'];

        if (empty($advert['site_advert_min_height'])) {
            $advert['site_advert_min_height'] = 0;
        }

        $form['fields']->advert_placement["value"] = $advert['site_advert_placement'];
        $form['fields']->advert_min_height["value"] = $advert['site_advert_min_height'];
        $form['fields']->advert_max_height["value"] = $advert['site_advert_max_height'];
        $form['fields']->advert_content["value"] = htmlspecialchars($advert['site_advert_content'], ENT_QUOTES, 'UTF-8');
        $form['fields']->disabled["value"] = $disabled;

    }
}
?>