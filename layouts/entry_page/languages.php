<?php
$columns = $where = null;
$columns = [
    'languages.language_id', 'languages.name'
];
$where = ['languages.disabled' => 0];
$languages = DB::connect()->select('languages', $columns, $where);
$current_language = $current_language_icon = null;
?>
<div class="dropdown_list d-none">
    <ul>
        <?php foreach ($languages as $language) {
            $language_icon = get_image(['from' => 'languages', 'search' => $language['language_id']]);
            ?>
            <li class='api_request' data-update="non_logged_in_user_settings" data-language_id='<?php echo $language['language_id'] ?>'>
                <img src="<?php echo $language_icon ?>" /> <span><?php echo $language['name'] ?></span>
            </li>
            <?php
            if ((int)Registry::load('current_user')->language === (int)$language['language_id']) {
                $current_language = $language['name'];
                $current_language_icon = $language_icon;
            }
        }
        ?>
    </ul>
</div>

<div class="current_language">
    <img src="<?php echo $current_language_icon; ?>" />
    <span><?php echo $current_language; ?></span>
</div>