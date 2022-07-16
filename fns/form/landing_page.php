<?php

if (role(['permissions' => ['super_privileges' => 'core_settings']])) {

    $columns = $join = $where = null;
    $language_id = Registry::load('current_user')->language;

    $language_strings = [
        'hero_section_heading' => '',
        'hero_section_description' => '',
        'groups_section_heading' => '',
        'groups_section_description' => '',
        'footer_text' => '',
        'footer_block_one_heading' => '',
        'footer_block_one_description' => '',
        'footer_block_two_heading' => '',
        'footer_block_two_description' => '',
        'copyright_notice' => '',
        'faq_section_heading' => '',
    ];

    for ($index = 1; $index <= 10; $index++) {
        $question_index = 'faq_question_'.$index;
        $answer_index = $question_index.'_answer';
        $language_strings[$question_index] = '';
        $language_strings[$answer_index] = '';
    }

    $columns = [
        'languages.name', 'languages.language_id'
    ];

    $where["languages.disabled[!]"] = 1;

    $languages = DB::connect()->select('languages', $columns, $where);

    if (isset($load["language_id"])) {
        $load["language_id"] = filter_var($load["language_id"], FILTER_SANITIZE_NUMBER_INT);

        if (!empty($load["language_id"])) {
            $language_id = $load["language_id"];
        }
    }

    $columns = $join = $where = null;

    $columns = ['language_strings.string_constant', 'language_strings.string_value'];

    $where["language_strings.language_id"] = $language_id;

    $where["AND"]["OR"] = [
        "language_strings.string_constant #condition_01" => 'landing_page_hero_section_heading',
        "language_strings.string_constant #condition_02" => 'landing_page_hero_section_description',
        "language_strings.string_constant #condition_03" => 'landing_page_groups_section_heading',
        "language_strings.string_constant #condition_04" => 'landing_page_groups_section_description',
        "language_strings.string_constant #condition_05" => 'landing_page_footer_text',
        "language_strings.string_constant #condition_06" => 'landing_page_footer_block_one_heading',
        "language_strings.string_constant #condition_07" => 'landing_page_footer_block_one_description',
        "language_strings.string_constant #condition_08" => 'landing_page_footer_block_two_heading',
        "language_strings.string_constant #condition_09" => 'landing_page_footer_block_two_description',
        "language_strings.string_constant #condition_10" => 'landing_page_copyright_notice',
        "language_strings.string_constant #condition_11" => 'landing_page_faq_section_heading',
    ];

    $condition_index = 12;

    for ($index = 1; $index <= 10; $index++) {
        $question_index = 'faq_question_'.$index;
        $answer_index = $question_index.'_answer';

        $where["AND"]["OR"]["language_strings.string_constant #condition_".$condition_index] = 'landing_page_'.$question_index;
        $condition_index++;

        $where["AND"]["OR"]["language_strings.string_constant #condition_".$condition_index] = 'landing_page_'.$answer_index;
        $condition_index++;
    }

    $landing_page_contents = DB::connect()->select('language_strings', $columns, $where);

    foreach ($landing_page_contents as $landing_page_content) {
        $string_constant = $landing_page_content['string_constant'];
        $string_constant = str_replace('landing_page_', '', $string_constant);
        $language_strings[$string_constant] = $landing_page_content['string_value'];
    }

    $form = array();
    $form['loaded'] = new stdClass();
    $form['loaded']->title = Registry::load('strings')->landing_page;
    $form['loaded']->button = Registry::load('strings')->update;

    $form['fields'] = new stdClass();

    $form['fields']->update = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "landing_page"
    ];

    $form['fields']->language_id = [
        "title" => Registry::load('strings')->language, "tag" => 'select', "class" => 'field'
    ];

    if (isset($load["language_id"]) && !empty($load["language_id"])) {
        $form['fields']->language_id['value'] = $load["language_id"];
    }

    $form['fields']->language_id["class"] = 'field switch_form';
    $form['fields']->language_id["parent_attributes"] = [
        "form" => "landing_page",
    ];

    foreach ($languages as $language) {
        $language_identifier = $language['language_id'];
        $form['fields']->language_id['options'][$language_identifier] = $language['name'];
    }

    $form['fields']->status = [
        "title" => Registry::load('strings')->status, "tag" => 'select', "class" => 'field',
        "value" => Registry::load('settings')->landing_page
    ];
    $form['fields']->status['options'] = [
        "enable" => Registry::load('strings')->enable,
        "disable" => Registry::load('strings')->disable,
    ];

    $form['fields']->hero_section_image = [
        "title" => Registry::load('strings')->hero_section_image, "tag" => 'input', "type" => 'file', "class" => 'field filebrowse',
        "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
    ];

    $form['fields']->hero_section_animation = [
        "title" => Registry::load('strings')->hero_section_animation, "tag" => 'select', "class" => 'field',
        "value" => Registry::load('settings')->hero_section_animation
    ];
    $form['fields']->hero_section_animation['options'] = [
        "enable" => Registry::load('strings')->enable,
        "disable" => Registry::load('strings')->disable,
    ];

    $form['fields']->hero_section_heading = [
        "title" => Registry::load('strings')->hero_section_heading, "tag" => 'textarea', "closetag" => true, "class" => 'field',
        "value" => $language_strings['hero_section_heading'],
    ];

    $form['fields']->hero_section_heading["attributes"] = ["rows" => 4];

    $form['fields']->hero_section_description = [
        "title" => Registry::load('strings')->hero_section_description, "tag" => 'textarea', "closetag" => true, "class" => 'field',
        "value" => $language_strings['hero_section_description']
    ];

    $form['fields']->hero_section_description["attributes"] = ["rows" => 6];

    $form['fields']->groups_section_status = [
        "title" => Registry::load('strings')->groups_section_status, "tag" => 'select', "class" => 'field',
        "value" => Registry::load('settings')->groups_section_status
    ];
    
    $form['fields']->groups_section_status['options'] = [
        "enable" => Registry::load('strings')->enable,
        "disable" => Registry::load('strings')->disable,
    ];

    $form['fields']->groups_section_heading = [
        "title" => Registry::load('strings')->groups_section_heading, "tag" => 'textarea', "closetag" => true, "class" => 'field',
        "value" => $language_strings['groups_section_heading'],
    ];

    $form['fields']->groups_section_heading["attributes"] = ["rows" => 4];

    $form['fields']->groups_section_description = [
        "title" => Registry::load('strings')->groups_section_description, "tag" => 'textarea', "closetag" => true, "class" => 'field',
        "value" => $language_strings['groups_section_description']
    ];

    $form['fields']->groups_section_description["attributes"] = ["rows" => 6];


    $form['fields']->footer_text = [
        "title" => Registry::load('strings')->footer_text, "tag" => 'textarea', "closetag" => true, "class" => 'field',
        "value" => $language_strings['footer_text']
    ];

    $form['fields']->footer_text["attributes"] = ["rows" => 6];



    $form['fields']->footer_block_one_heading = [
        "title" => Registry::load('strings')->footer_block_heading, "tag" => 'textarea', "closetag" => true, "class" => 'field',
        "value" => $language_strings['footer_block_one_heading'],
    ];

    $form['fields']->footer_block_one_heading["attributes"] = ["rows" => 4];

    $form['fields']->footer_block_one_description = [
        "title" => Registry::load('strings')->footer_block_description, "tag" => 'textarea', "closetag" => true, "class" => 'field',
        "value" => $language_strings['footer_block_one_description']
    ];

    $form['fields']->footer_block_one_description["attributes"] = ["rows" => 6];

    $form['fields']->footer_block_two_heading = [
        "title" => Registry::load('strings')->footer_block_heading, "tag" => 'textarea', "closetag" => true, "class" => 'field',
        "value" => $language_strings['footer_block_two_heading'],
    ];

    $form['fields']->footer_block_two_heading["attributes"] = ["rows" => 4];

    $form['fields']->footer_block_two_description = [
        "title" => Registry::load('strings')->footer_block_description, "tag" => 'textarea', "closetag" => true, "class" => 'field',
        "value" => $language_strings['footer_block_two_description']
    ];

    $form['fields']->footer_block_two_description["attributes"] = ["rows" => 6];




    $form['fields']->copyright_notice = [
        "title" => Registry::load('strings')->copyright_notice, "tag" => 'textarea', "closetag" => true, "class" => 'field',
        "value" => $language_strings['copyright_notice']
    ];

    $form['fields']->copyright_notice["attributes"] = ["rows" => 6];


    $form['fields']->facebook_url = [
        "title" => Registry::load('strings')->facebook_url, "tag" => 'input', "type" => "text",
        "class" => 'field', "value" => Registry::load('settings')->facebook_url
    ];

    $form['fields']->instagram_url = [
        "title" => Registry::load('strings')->instagram_url, "tag" => 'input', "type" => "text",
        "class" => 'field', "value" => Registry::load('settings')->instagram_url
    ];

    $form['fields']->twitter_url = [
        "title" => Registry::load('strings')->twitter_url, "tag" => 'input', "type" => "text",
        "class" => 'field', "value" => Registry::load('settings')->twitter_url
    ];

    $form['fields']->linkedin_url = [
        "title" => Registry::load('strings')->linkedin_url, "tag" => 'input', "type" => "text",
        "class" => 'field', "value" => Registry::load('settings')->linkedin_url
    ];

    $form['fields']->twitch_url = [
        "title" => Registry::load('strings')->twitch_url, "tag" => 'input', "type" => "text",
        "class" => 'field', "value" => Registry::load('settings')->twitch_url
    ];


    $form['fields']->faq_section_status = [
        "title" => Registry::load('strings')->faq_section_status, "tag" => 'select', "class" => 'field',
        "value" => Registry::load('settings')->faq_section_status
    ];
    
    $form['fields']->faq_section_status['options'] = [
        "enable" => Registry::load('strings')->enable,
        "disable" => Registry::load('strings')->disable,
    ];

    $form['fields']->faq_section_heading = [
        "title" => Registry::load('strings')->faq_section_heading, "tag" => 'textarea', "closetag" => true, "class" => 'field',
        "value" => $language_strings['faq_section_heading'],
    ];

    $form['fields']->faq_section_heading["attributes"] = ["rows" => 4];


    for ($index = 1; $index <= 10; $index++) {
        $question_index = 'faq_question_'.$index;
        $answer_index = $question_index.'_answer';

        $form['fields']->$question_index = [
            "title" => $index.' - '.Registry::load('strings')->question, "tag" => 'textarea', "closetag" => true, "class" => 'field',
            "value" => $language_strings[$question_index],
        ];

        $form['fields']->$question_index["attributes"] = ["rows" => 4];

        $form['fields']->$answer_index = [
            "title" => Registry::load('strings')->answer, "tag" => 'textarea', "closetag" => true, "class" => 'field',
            "value" => $language_strings[$answer_index],
        ];

        $form['fields']->$answer_index["attributes"] = ["rows" => 4];
    }


}
?>