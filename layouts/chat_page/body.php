<body class='<?php echo(Registry::load('appearance')->body_class) ?> overflow-hidden'>

    <?php include 'assets/headers_footers/chat_page/body.php'; ?>

    <div class="preloader">
        <div class="content">
            <div>
                <div class="loader_image">
                    <?php if (Registry::load('current_user')->color_scheme === 'dark_mode') {
                        ?>
                        <img src="<?php echo Registry::load('config')->site_url.'assets/files/defaults/loading_image_dark_mode.png'.$cache_timestamp; ?>" />
                        <?php
                    } else {
                        ?>
                        <img src="<?php echo Registry::load('config')->site_url.'assets/files/defaults/loading_image_light_mode.png'.$cache_timestamp; ?>" />
                        <?php
                    } ?>
                </div>
                <div class="loader">
                    <div class="loading">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="main main_window" last_realtime_log_id=0>
        <div class='window fh'>
            <div class="container-fluid fh">
                <div class="row fh nowrap page_row chat_page_container">
                    <?php if (Registry::load('current_user')->logged_in) {
                        include 'layouts/chat_page/side_navigation.php';
                    } ?>
                    <?php include 'layouts/chat_page/aside.php'; ?>
                    <?php include 'layouts/chat_page/middle.php'; ?>
                    <?php include 'layouts/chat_page/form.php'; ?>
                    <?php include 'layouts/chat_page/info_panel.php'; ?>
                </div>
            </div>
        </div>
    </section>


    <div class="on_site_load d-none">
        <?php if (isset(Registry::load('config')->load_user_profile) && !empty(Registry::load('config')->load_user_profile)) {
            ?>
            <span class="get_info" user_id="<?php echo(Registry::load('config')->load_user_profile) ?>">Profile</span>
            <?php
        } else if (isset(Registry::load('config')->load_private_conversation) && !empty(Registry::load('config')->load_private_conversation)) {
            ?>
            <span class="load_conversation" user_id="<?php echo(Registry::load('config')->load_private_conversation) ?>">Group</span>
            <?php
        } else if (isset(Registry::load('config')->load_group_conversation) && !empty(Registry::load('config')->load_group_conversation)) {
            ?>
            <span class="load_conversation" group_id="<?php echo(Registry::load('config')->load_group_conversation) ?>">Group</span>
            <?php
        } else if (isset(Registry::load('config')->load_page) && !empty(Registry::load('config')->load_page)) {
            ?>
            <span class="load_page" page_id="<?php echo(Registry::load('config')->load_page) ?>">Page</span>
            <?php
        } else if (Registry::load('current_user')->logged_in && !isset(Registry::load('config')->load_user_profile)) {
            if (role(['find' => 'load_profile_on_page_load']) === 'yes') {
                ?>
                <span class="get_info load_profile_on_page_load">User Profile</span>
                <?php
            }
        }
        ?>
    </div>

    <div class="content_on_page_load d-none">
        <?php
        if (Registry::load('current_user')->logged_in) {
            ?>
            <span class="left_panel_content_on_page_load"><?php echo role(['find' => 'left_panel_content_on_page_load']); ?></span>
            <span class="main_panel_content_on_page_load"><?php echo role(['find' => 'main_panel_content_on_page_load']); ?></span>
            <?php
        }
        ?>
    </div>

    <div class="load_on_refresh d-none"></div>

    <div class="language_strings d-none">
        <span class="string_uploading_files"><?php echo(Registry::load('strings')->uploading_files) ?></span>
        <span class='string_loading'><?php echo(Registry::load('strings')->loading) ?></span>
        <span class='string_sort'><?php echo(Registry::load('strings')->sort) ?></span>
        <span class='string_error'><?php echo(Registry::load('strings')->error) ?></span>
        <span class='string_error_message'><?php echo(Registry::load('strings')->error_message) ?></span>
        <span class='string_choose_file'><?php echo(Registry::load('strings')->choose_file) ?></span>
        <span class='string_load_more'><?php echo(Registry::load('strings')->load_more) ?></span>
        <span class='string_new'><?php echo(Registry::load('strings')->new) ?></span>
        <span class='string_new_message_notification'><?php echo(Registry::load('strings')->new_message_notification) ?></span>
        <span class='string_is_typing'><?php echo(Registry::load('strings')->is_typing) ?></span>
        <span class='string_recording'><?php echo(Registry::load('strings')->recording) ?></span>
    </div>

    <div class="system_variables d-none">
        <span class="variable_message_alignment"><?php echo(Registry::load('settings')->message_alignment) ?></span>
        <span class="variable_own_message_alignment"><?php echo(Registry::load('settings')->own_message_alignment) ?></span>
        <span class="variable_refresh_rate"><?php echo(Registry::load('settings')->refresh_rate) ?></span>
        <span class="variable_enter_is_send"><?php echo(Registry::load('settings')->enter_is_send) ?></span>
        <span class="variable_load_group_info_on_group_load"><?php echo(Registry::load('settings')->load_group_info_on_group_load) ?></span>
        <span class="variable_current_title"></span>
    </div>

    <div class="site_sound_notification">
        <div>
            <audio controls>
                <source src="<?php echo(Registry::load('settings')->notification_tone) ?>" type="audio/mpeg">
            </audio>
        </div>
    </div>

    <?php include 'layouts/chat_page/web_push_service_variables.php'; ?>
</body>