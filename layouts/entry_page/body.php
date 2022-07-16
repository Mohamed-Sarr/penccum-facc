<body class='entry_page<?php echo ' '.Registry::load('appearance')->body_class ?>'>
    <?php include 'assets/headers_footers/entry_page/body.php'; ?>
    <div class="container-fluid">
        <div class="row no-gutter">

            <div class="col-md-5 col-lg-4 col-xl-3 entry_box">
                <div>

                    <div class="top">
                        <div class="logo">
                            <a href=".">
                                <?php if (Registry::load('current_user')->color_scheme === 'dark_mode') {
                                    ?>
                                    <img src="<?php echo Registry::load('config')->site_url.'assets/files/logos/entry_page_logo_dark_mode.png'.$cache_timestamp; ?>" />
                                    <?php
                                } else {
                                    ?>
                                    <img src="<?php echo Registry::load('config')->site_url.'assets/files/logos/entry_page_logo.png'.$cache_timestamp; ?>" />
                                    <?php
                                } ?>
                            </a>
                        </div>


                    </div>
                    <div class="middle">

                        <?php
                        $site_advert = DB::connect()->rand("site_advertisements",
                            ['site_advertisements.site_advert_min_height', 'site_advertisements.site_advert_max_height',
                                'site_advertisements.site_advert_content'],
                            ["site_advertisements.site_advert_placement" => 'entry_page_form_header', "site_advertisements.disabled[!]" => 1, "LIMIT" => 1]
                        );
                        if (isset($site_advert[0])) {
                            $site_advert = $site_advert[0];
                            $advert_css = 'max-height:'.$site_advert['site_advert_max_height'].'px;';

                            if (!empty($site_advert['site_advert_min_height'])) {
                                $advert_css .= 'min-height:'.$site_advert['site_advert_min_height'].'px;';
                            }
                            ?>

                            <div class="site_advert_block" style="<?php echo $advert_css; ?>">
                                <div>
                                    <?php echo $site_advert['site_advert_content']; ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>

                        <div class="heading">
                            <div class="login_form form_element">
                                <h3><?php echo Registry::load('strings')->login ?></h3>
                                <span><?php echo Registry::load('strings')->login_text ?></span>
                            </div>
                            <?php if (Registry::load('settings')->guest_login === 'enable') {
                                ?>
                                <div class="guest_login_form form_element d-none">
                                    <h3><?php echo Registry::load('strings')->guest_login ?></h3>
                                    <span><?php echo Registry::load('strings')->guest_login_text ?></span>
                                </div>
                                <?php
                            }
                            ?>

                            <?php if (Registry::load('settings')->user_registration === 'enable') {
                                ?>
                                <div class="signup_form form_element d-none">
                                    <h3><?php echo Registry::load('strings')->signup ?></h3>
                                    <span><?php echo Registry::load('strings')->signup_text ?></span>
                                </div>
                                <?php
                            }
                            ?>

                            <div class="forgot_password_form form_element d-none">
                                <h3><?php echo Registry::load('strings')->forgot_password ?></h3>
                                <span><?php echo Registry::load('strings')->forgot_password_text ?></span>
                            </div>
                        </div>

                        <div class="tabs">
                            <ul>
                                <li class="selected switch_form" form="login"><?php echo Registry::load('strings')->login ?></li>
                                <?php if (Registry::load('settings')->guest_login === 'enable') {
                                    ?>
                                    <li class="switch_form" form="guest_login"><?php echo Registry::load('strings')->guest_login ?></li>
                                    <?php
                                }
                                ?>

                                <?php if (Registry::load('settings')->user_registration === 'enable') {
                                    ?>
                                    <li class="switch_form" form="signup"><?php echo Registry::load('strings')->signup ?></li>
                                    <?php
                                }
                                ?>

                                <li class="switch_form d-none" form="forgot_password"><?php echo Registry::load('strings')->forgot_password ?></li>
                            </ul>
                        </div>


                        <?php include 'layouts/entry_page/form.php'; ?>

                        <?php
                        $site_advert = DB::connect()->rand("site_advertisements",
                            ['site_advertisements.site_advert_min_height', 'site_advertisements.site_advert_max_height',
                                'site_advertisements.site_advert_content'],
                            ["site_advertisements.site_advert_placement" => 'entry_page_form_footer', "site_advertisements.disabled[!]" => 1, "LIMIT" => 1]
                        );
                        if (isset($site_advert[0])) {
                            $site_advert = $site_advert[0];
                            $advert_css = 'max-height:'.$site_advert['site_advert_max_height'].'px;';

                            if (!empty($site_advert['site_advert_min_height'])) {
                                $advert_css .= 'min-height:'.$site_advert['site_advert_min_height'].'px;';
                            }
                            ?>

                            <div class="site_advert_block" style="<?php echo $advert_css; ?>">
                                <div>
                                    <?php echo $site_advert['site_advert_content']; ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>


                    </div>
                    <div class="bottom">
                        <div class="ad_placement">
                        </div>

                        <div class="menu footer_menu">
                            <ul>
                                <?php include 'layouts/entry_page/custom_menu_items.php'; ?>
                            </ul>
                        </div>

                        <div class="copyright">
                            <?php echo Registry::load('strings')->entry_page_footer_text ?>
                        </div>
                        <div class="switch_languages dropdown_button">
                            <?php include 'layouts/entry_page/languages.php'; ?>
                        </div>
                    </div>

                </div>
            </div>
            <!-- End -->


            <!-- The image half -->
            <div class="col-md-7 col-lg-8 col-xl-9 d-none d-md-flex background">
                <?php include 'layouts/entry_page/background.php'; ?>
            </div>

        </div>
    </div>