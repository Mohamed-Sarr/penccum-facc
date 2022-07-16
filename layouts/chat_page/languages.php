<?php

$columns = $where = null;
$columns = [
    'languages.language_id', 'languages.name'
];

$where["languages.disabled"] = 0;
$where["ORDER"] = ["languages.language_id" => "ASC"];

$languages = DB::connect()->select('languages', $columns, $where);
$language_icon = get_image(['from' => 'languages', 'search' => Registry::load('settings')->default_language]);
?>

<li class="has_child">
    <div class="menu_item">
        <span class="icon">
            <i class="iconic_translate"></i>
        </span>
        <span class="title">
            <?php echo(Registry::load('strings')->languages) ?>
        </span>
    </div>
    <div class="child_menu">
        <ul>
            <li class='api_request' data-update="site_users_settings" data-language_id='0'>
                <span class="image"><img src="<?php echo $language_icon; ?>" /></span>
                <span class="text"><?php echo Registry::load('strings')->default ?></span>
                </li>
                <?php
                foreach ($languages as $language) {
                    $language_icon = get_image(['from' => 'languages', 'search' => $language['language_id']]);
                    ?>
                    <li class='api_request' data-update="site_users_settings" data-language_id='<?php echo $language['language_id'] ?>'>
                        <span class="image"><img src="<?php echo $language_icon; ?>" /></span>
                        <span class="text"><?php echo $language['name'] ?></span>
                    </li>
                    <?php
                } ?>
            </ul>
        </div>
    </li>