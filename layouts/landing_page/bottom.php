<footer class="footer">
    <div class="divider">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
        </svg>
    </div>
    <div class="container">
        <div class="row align-items-md-baseline align-items-center">

            <div class="col-6 col-lg-4 first_block">
                <div class="logo">
                    <?php if (Registry::load('current_user')->color_scheme === 'dark_mode') {
                        ?>
                        <img src="<?php echo Registry::load('config')->site_url.'assets/files/logos/landing_page_footer_logo_dark_mode.png'.$cache_timestamp; ?>" />
                        <?php
                    } else {
                        ?>
                        <img src="<?php echo Registry::load('config')->site_url.'assets/files/logos/landing_page_footer_logo.png'.$cache_timestamp; ?>" />
                        <?php
                    } ?>
                </div>
                <p class="text_content">
                    <?php echo Registry::load('strings')->landing_page_footer_text; ?>
                </p>
            </div>
            <div class="col-6 col-lg-2 second_block offset-lg-2">
                <h5><?php echo Registry::load('strings')->landing_page_footer_block_one_heading; ?></h5>
                <p class="text_content">
                    <?php echo nl2br(Registry::load('strings')->landing_page_footer_block_one_description); ?>
                </p>
            </div>

            <div class="col-6 col-lg-2">
                <h5><?php echo Registry::load('strings')->landing_page_footer_block_two_heading; ?></h5>

                <p class="text_content">
                    <?php echo nl2br(Registry::load('strings')->landing_page_footer_block_two_description); ?>
                </p>
            </div>

            <div class="col-6 col-lg-2">
                <ul class="nav flex-column">
                    <?php

                    $columns = $where = $join = null;
                    $columns = [
                        'custom_menu_items.string_constant', 'custom_menu_items.page_id', 'custom_menu_items.web_address',
                        'custom_menu_items.menu_icon_class', 'custom_menu_items.link_target', 'custom_pages.slug'
                    ];

                    $join["[>]custom_pages"] = ["custom_menu_items.page_id" => "page_id"];

                    $where["custom_menu_items.disabled"] = 0;
                    $where["custom_menu_items.show_on_landing_page_footer"] = 1;
                    $where["ORDER"] = ["custom_menu_items.menu_item_order" => "ASC"];

                    $menu_items = DB::connect()->select('custom_menu_items', $join, $columns, $where);

                    foreach ($menu_items as $menu_item) {

                        $menu_item_title = $menu_item['string_constant'];
                        $menu_item_attributes = '';
                        $hyperlink = '';

                        if (!empty($menu_item['page_id'])) {
                            $hyperlink = Registry::load('config')->site_url;
                            $hyperlink .= $menu_item['slug'].'/';
                        } else {

                            if (substr($menu_item['web_address'], 0, 7) !== 'http://' && !substr($menu_item['web_address'], 0, 8) !== 'https://') {
                                $hyperlink = Registry::load('config')->site_url;
                            }

                            $hyperlink .= $menu_item['web_address'];
                        }
                        if (!empty($menu_item['link_target'])) {
                            $menu_item_attributes .= 'target="_blank" ';
                        }

                        ?>
                        <li class="nav-item mb-2">
                            <a class="nav-link p-0" <?php echo $menu_item_attributes ?> href="<?php echo $hyperlink ?>">
                                <?php echo Registry::load('strings')->$menu_item_title; ?>
                            </a>
                        </li>

                        <?php
                    }
                    ?>
                </ul>
            </div>
        </div>

        <div class="d-flex justify-content-between py-4 my-4 border-top">
            <p class="copyright">
                <?php echo Registry::load('strings')->landing_page_copyright_notice; ?>
            </p>
            <ul class="list-unstyled d-flex social_icons">

                <?php if (!empty(Registry::load('settings')->twitter_url)) {
                    ?>
                    <li class="ms-3">
                        <a href="<?php echo Registry::load('settings')->twitter_url; ?>">
                            <i class="bi bi-twitter"></i>
                        </a>
                    </li>
                    <?php
                } ?>

                <?php if (!empty(Registry::load('settings')->facebook_url)) {
                    ?>
                    <li class="ms-3">
                        <a href="<?php echo Registry::load('settings')->facebook_url; ?>">
                            <i class="bi bi-facebook"></i>
                        </a>
                    </li>
                    <?php
                } ?>

                <?php if (!empty(Registry::load('settings')->instagram_url)) {
                    ?>
                    <li class="ms-3">
                        <a href="<?php echo Registry::load('settings')->instagram_url; ?>">
                            <i class="bi bi-instagram"></i>
                        </a>
                    </li>
                    <?php
                } ?>

                <?php if (!empty(Registry::load('settings')->linkedin_url)) {
                    ?>
                    <li class="ms-3">
                        <a href="<?php echo Registry::load('settings')->linkedin_url; ?>">
                            <i class="bi bi-linkedin"></i>
                        </a>
                    </li>
                    <?php
                } ?>

                <?php if (!empty(Registry::load('settings')->twitch_url)) {
                    ?>
                    <li class="ms-3">
                        <a href="<?php echo Registry::load('settings')->twitch_url; ?>">
                            <i class="bi bi-twitch"></i>
                        </a>
                    </li>
                    <?php
                } ?>

            </ul>
        </div>
    </div>
</footer>