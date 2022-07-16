<?php

$form = array();

if (role(['permissions' => ['social_login_providers' => ['create', 'edit']], 'condition' => 'OR'])) {

    $todo = 'add';

    if (isset($load["social_login_provider_id"])) {
        $todo = 'update';
    }

    $form['loaded'] = new stdClass();
    $form['fields'] = new stdClass();

    if ($todo === 'update' && isset($load["social_login_provider_id"])) {

        $columns = [
            'social_login_providers.identity_provider', 'social_login_providers.app_id', 'social_login_providers.app_key',
            'social_login_providers.secret_key', 'social_login_providers.open_in_popup', 'social_login_providers.disabled',
            'social_login_providers.create_user'
        ];

        $where["social_login_providers.social_login_provider_id"] = $load["social_login_provider_id"];
        $where["LIMIT"] = 1;

        $provider = DB::connect()->select('social_login_providers', $columns, $where);

        if (!isset($provider[0])) {
            return false;
        } else {
            $provider = $provider[0];
        }

        $form['fields']->social_login_provider_id = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load["social_login_provider_id"]
        ];

        $form['loaded']->title = Registry::load('strings')->edit_provider;
        $form['loaded']->button = Registry::load('strings')->update;
    } else {
        $form['loaded']->title = Registry::load('strings')->add_provider;
        $form['loaded']->button = Registry::load('strings')->create;
    }


    $form['fields']->process = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $todo
    ];

    $form['fields']->$todo = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "social_login_providers"
    ];

    $form['fields']->identity_provider = [
        "title" => Registry::load('strings')->identity_provider, "tag" => 'select', "class" => 'field showfieldon',
    ];

    $form['fields']->identity_provider["attributes"] = ["hideclass" => "appid", "fieldclass" => "appkey", "checkvalue" => "Tumblr,Twitter"];

    $form['fields']->identity_provider['options'] = [
        'Amazon' => 'Amazon', 'Authentiq' => 'Authentiq',
        'BitBucket' => 'BitBucket', 'Blizzard' => 'Blizzard', 'Discord' => 'Discord',
        'Disqus' => 'Disqus', 'Dribbble' => 'Dribbble', 'Dropbox' => 'Dropbox',
        'Facebook' => 'Facebook', 'Foursquare' => 'Foursquare', 'GitHub' => 'GitHub',
        'GitLab' => 'GitLab', 'Google' => 'Google', 'Instagram' => 'Instagram',
        'LinkedIn' => 'LinkedIn', 'Mailru' => 'Mailru', 'Medium' => 'Medium',
        'MicrosoftGraph' => 'MicrosoftGraph', 'Odnoklassniki' => 'Odnoklassniki',
        'ORCID' => 'ORCID', 'Paypal' => 'Paypal', 'Reddit	' => 'Reddit', 'Slack' => 'Slack',
        'Spotify' => 'Spotify', 'StackExchange' => 'StackExchange', 'Steam' => 'Steam',
        'Strava' => 'Strava', 'SteemConnect' => 'SteemConnect', 'Telegram' => 'Telegram',
        'Tumblr' => 'Tumblr', 'TwitchTV' => 'TwitchTV', 'Twitter' => 'Twitter',
        'Vkontakte' => 'Vkontakte', 'WeChat' => 'WeChat', 'WindowsLive' => 'WindowsLive',
        'WordPress' => 'WordPress', 'Yandex' => 'Yandex', 'Yahoo' => 'Yahoo', 'QQ' => 'QQ',
    ];

    $form['fields']->app_id = [
        "title" => Registry::load('strings')->app_id, "tag" => 'input', "type" => "text", "class" => 'field appid',
        "placeholder" => Registry::load('strings')->app_id,
    ];

    $form['fields']->app_key = [
        "title" => Registry::load('strings')->appkey, "tag" => 'input', "type" => "text", "class" => 'field appkey d-none',
        "placeholder" => Registry::load('strings')->appkey,
    ];

    $form['fields']->secret_key = [
        "title" => Registry::load('strings')->secret_key, "tag" => 'input', "type" => "text", "class" => 'field',
        "placeholder" => Registry::load('strings')->secret_key,
    ];


    $form['fields']->icon = [
        "title" => Registry::load('strings')->icon, "tag" => 'input', "type" => 'file', "class" => 'field filebrowse',
        "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
    ];

    $form['fields']->call_back_url = [
        "title" => Registry::load('strings')->callback_url, "tag" => 'input', "type" => 'text', "class" => 'field base_encode selectfield',
        "value" => Registry::load('config')->site_url.'entry/social_login/',
    ];

    $form['fields']->open_in_popup = [
        "title" => Registry::load('strings')->open_in_popup, "tag" => 'select', "class" => 'field', "value" => "yes"
    ];
    $form['fields']->open_in_popup['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    $form['fields']->create_user = [
        "title" => Registry::load('strings')->create_user_if_not_exists, "tag" => 'select', "class" => 'field',
        "value" => "yes"
    ];
    $form['fields']->create_user['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    $form['fields']->disabled = [
        "title" => Registry::load('strings')->disabled, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->disabled['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    if (isset($load["social_login_provider_id"])) {
        $create_user = $disabled = $open_in_popup = 'no';

        if ((int)$provider['disabled'] === 1) {
            $disabled = 'yes';
        }

        if ((int)$provider['create_user'] === 1) {
            $create_user = 'yes';
        }

        if ((int)$provider['open_in_popup'] === 1) {
            $open_in_popup = 'yes';
        }

        $form['fields']->identity_provider["value"] = $provider['identity_provider'];
        $form['fields']->app_id["value"] = $provider['app_id'];
        $form['fields']->app_key["value"] = $provider['app_key'];
        $form['fields']->secret_key["value"] = $provider['secret_key'];
        $form['fields']->open_in_popup["value"] = $open_in_popup;
        $form['fields']->disabled["value"] = $disabled;
        $form['fields']->create_user["value"] = $create_user;

        if ($provider['identity_provider'] === 'Twitter' || $provider['identity_provider'] === 'Tumblr') {
            $form['fields']->app_key["class"] = 'field appkey';
            $form['fields']->app_id["class"] = 'field appid d-none';
        }
    }
}

?>