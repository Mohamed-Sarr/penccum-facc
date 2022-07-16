<?php

$columns = $where = null;
$columns = [
    'social_login_providers.social_login_provider_id', 'social_login_providers.identity_provider',
    'social_login_providers.open_in_popup'
];

$where["social_login_providers.disabled"] = 0;

$login_providers = DB::connect()->select('social_login_providers', $columns, $where);
if (count($login_providers) > 0) {
    ?>
    <div class="social_login login_form form_element">
        <div>
            <div>
                <span><?php echo Registry::load('strings')->or_login_using ?></span>
            </div>
            <ul>
                <?php
                foreach ($login_providers as $login_provider) {

                    $open_in_popup = 'true';
                    $provider_icon = get_image(['from' => 'social_login', 'search' => $login_provider['social_login_provider_id']]);
                    $social_login_link = Registry::load('config')->site_url.'entry/social_login/?social_login_provider_id='.$login_provider['social_login_provider_id'];

                    if (empty($login_provider['open_in_popup'])) {
                        $open_in_popup = 'false';
                    }

                    ?>
                    <li class="open_link" open_in_popup="<?php echo $open_in_popup ?>" link="<?php echo $social_login_link ?>">
                        <img src="<?php echo $provider_icon ?>" />
                    </li>
                    <?php
                }
                ?>

            </ul>
        </div>
    </div>
    <?php
}
?>