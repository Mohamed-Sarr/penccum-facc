<div class="side_navigation boundary">
    <div class="top">
        <div class="logo refresh_page">
            <?php if (Registry::load('current_user')->color_scheme === 'dark_mode') {
                ?>
                <img src="<?php echo Registry::load('config')->site_url.'assets/files/logos/chat_page_logo_dark_mode.png'.$cache_timestamp; ?>" />
                <?php
            } else {
                ?>
                <img src="<?php echo Registry::load('config')->site_url.'assets/files/logos/chat_page_logo.png'.$cache_timestamp; ?>" />
                <?php
            } ?>
        </div>
        <div class="icon">
            <i class="toggle_side_navigation">
                <i class="iconic_close"></i>
            </i>
        </div>
    </div>
    <div class="center">
        <ul class="menu_items">

            <?php
            if (role(['permissions' => ['site_notifications' => 'view']])) {
                ?>
                <li class="load_aside realtime_module load_site_notifications" module="site_notifications" load="site_notifications" id="alerts">
                    <div class="menu_item">
                        <span class="icon">
                            <i class="iconic_notifications"></i>
                        </span>
                        <span class="title">
                            <?php echo(Registry::load('strings')->notifications) ?>
                        </span>
                        <span class="unread"></span>
                    </div>
                </li>
                <?php
            } ?>

            <?php
            if (role(['permissions' => ['super_privileges' => ['view_statistics', 'monitor_group_chats', 'monitor_private_chats']], 'condition' => 'OR'])) {
                ?>
                <li class="has_child">
                    <div class="menu_item">
                        <span class="icon">
                            <i class="iconic_monitor"></i>
                        </span>
                        <span class="title">
                            <?php echo Registry::load('strings')->monitor ?>
                        </span>
                    </div>
                    <div class="child_menu">
                        <ul>
                            <?php
                            if (role(['permissions' => ['super_privileges' => 'view_statistics']])) {
                                ?>
                                <li class="show_statistics load_statistics"><?php echo(Registry::load('strings')->statistics) ?></li>
                                <?php
                            }
                            if (role(['permissions' => ['super_privileges' => 'monitor_private_chats']])) {
                                ?>
                                <li class="load_conversation" group_id="all"><?php echo Registry::load('strings')->group_chats ?></li>
                                <?php
                            }
                            if (role(['permissions' => ['super_privileges' => 'monitor_private_chats']])) {
                                ?>
                                <li class="load_conversation" user_id="all"><?php echo Registry::load('strings')->private_chats ?></li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                </li>
                <?php
            } ?>

            <?php
            $view_groups = false;

            if (!Registry::load('current_user')->logged_in) {
                $view_groups = true;
            } else if (role(['permissions' => ['groups' => ['view_public_groups', 'view_password_protected_groups', 'view_joined_groups', 'view_secret_groups']], 'condition' => 'OR'])) {
                $view_groups = true;
            }

            if ($view_groups) {
                ?>

                <li class="load_aside realtime_module load_groups" load="groups" module="groups" unread="0">
                    <div class="menu_item">
                        <span class="icon">
                            <i class="iconic_groups"></i>
                        </span>
                        <span class="title">
                            <?php echo(Registry::load('strings')->groups) ?>
                        </span>
                        <span class="unread"></span>
                    </div>
                </li>
                <?php
            } ?>

            <?php
            if (role(['permissions' => ['private_conversations' => 'view_private_chats']])) {
                ?>
                <li class="load_aside realtime_module load_private_conversations" load="private_conversations" module="private_conversations">
                    <div class="menu_item">
                        <span class="icon">
                            <i class="iconic_messages"></i>
                        </span>
                        <span class="title">
                            <?php echo Registry::load('strings')->messages ?>
                        </span>
                        <span class="unread"></span>
                    </div>
                </li>
                <?php
            }
            if (role(['permissions' => ['site_users' => 'view_online_users']])) {
                ?>
                <li class="load_aside realtime_module load_online_users" module="online_users" load="online">
                    <div class="menu_item">
                        <span class="icon">
                            <i class="iconic_online"></i>
                        </span>
                        <span class="title">
                            <?php echo Registry::load('strings')->online ?>
                        </span>
                    </div>
                </li>
                <?php
            }
            ?>


            <?php if (role(['permissions' => ['site_users' => ['view_site_users', 'block_users', 'ignore_users']], 'condition' => 'OR'])) {
                ?>
                <li class="has_child">
                    <div class="menu_item">
                        <span class="icon">
                            <i class="iconic_users"></i>
                        </span>
                        <span class="title">
                            <?php echo(Registry::load('strings')->site_users) ?>
                        </span>
                    </div>
                    <div class="child_menu">
                        <ul>
                            <?php if (role(['permissions' => ['site_users' => 'view_site_users']])) {
                                ?>
                                <li class="load_aside load_site_users" load="site_users"><?php echo(Registry::load('strings')->view_all) ?></li>
                                <?php
                            }

                            if (role(['permissions' => ['site_users' => 'approve_users']])) {
                                ?>
                                <li class='load_aside' load="site_users" filter="pending_approval" skip_filter_title="true"><?php echo(Registry::load('strings')->pending_approval) ?></li>
                                <?php
                            }
                            if (role(['permissions' => ['site_users' => 'block_users']])) {
                                ?>
                                <li class='load_aside' load="blocked"><?php echo(Registry::load('strings')->blocked) ?></li>
                                <?php
                            }
                            if (role(['permissions' => ['site_users' => 'ignore_users']])) {
                                ?>
                                <li class='load_aside' load="ignored"><?php echo(Registry::load('strings')->ignored) ?></li>
                                <?php
                            }
                            if (role(['permissions' => ['site_users' => 'import_users']])) {
                                ?>
                                <li class='load_form' form="import_users"><?php echo(Registry::load('strings')->import_users) ?></li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                </li>
                <?php
            }
            ?>
            <?php
            $module_permissions = array();

            if (role(['permissions' => ['custom_menu' => 'view']])) {
                $module_permissions['custom_menu'] = true;
            }

            if (role(['permissions' => ['custom_fields' => 'view']])) {
                $module_permissions['custom_fields'] = true;
            }

            if (role(['permissions' => ['custom_pages' => 'view']])) {
                $module_permissions['custom_pages'] = true;
            }

            if (role(['permissions' => ['stickers' => 'view']])) {
                $module_permissions['sticker_packs'] = true;
            }

            if (role(['permissions' => ['avatars' => 'view']])) {
                $module_permissions['avatars'] = true;
            }

            if (role(['permissions' => ['languages' => 'view']])) {
                $module_permissions['languages'] = true;
            }

            if (role(['permissions' => ['site_roles' => 'view']])) {
                $module_permissions['site_roles'] = true;
            }

            if (role(['permissions' => ['social_login_providers' => 'view']])) {
                $module_permissions['social_login_providers'] = true;
            }

            if (role(['permissions' => ['audio_player' => 'view']])) {
                $module_permissions['audio_player'] = true;
            }

            if (role(['permissions' => ['site_adverts' => 'view']])) {
                $module_permissions['site_adverts'] = true;
            }

            if (role(['permissions' => ['group_roles' => 'view']])) {
                $module_permissions['group_roles'] = true;
            }

            if (role(['permissions' => ['badges' => 'view']])) {
                $module_permissions['badges'] = true;
            }

            if (role(['permissions' => ['super_privileges' => 'firewall']])) {
                $module_permissions['firewall'] = true;
            }

            if (role(['permissions' => ['super_privileges' => 'profanity_filter']])) {
                $module_permissions['profanity_filter'] = true;
            }

            if (role(['permissions' => ['super_privileges' => 'cron_jobs']])) {
                $module_permissions['cron_jobs'] = true;
            }

            if (!empty($module_permissions)) {
                ?>
                <li class="has_child">
                    <div class="menu_item">
                        <span class="icon">
                            <i class="iconic_modules"></i>
                        </span>
                        <span class="title">
                            <?php echo Registry::load('strings')->modules ?>
                        </span>
                    </div>
                    <div class="child_menu">
                        <ul>
                            <?php if (isset($module_permissions['custom_menu'])) {
                                ?>
                                <li class="load_aside" load="custom_menu_items"><?php echo Registry::load('strings')->custom_menu ?></li>
                                <?php
                            } if (isset($module_permissions['custom_fields'])) {
                                ?>
                                <li class="load_aside" load="custom_fields"><?php echo(Registry::load('strings')->custom_fields) ?></li>
                                <?php
                            } if (isset($module_permissions['custom_pages'])) {
                                ?>
                                <li class="load_aside" load="custom_pages"><?php echo(Registry::load('strings')->custom_pages) ?></li>
                                <?php
                            } if (isset($module_permissions['sticker_packs'])) {
                                ?>
                                <li class="load_aside" load="sticker_packs"><?php echo(Registry::load('strings')->sticker_packs) ?></li>
                                <?php
                            } if (isset($module_permissions['avatars'])) {
                                ?>
                                <li class="load_aside" load="avatars"><?php echo(Registry::load('strings')->avatars) ?></li>
                                <?php
                            } if (isset($module_permissions['languages'])) {
                                ?>
                                <li class="load_aside" load="languages"><?php echo(Registry::load('strings')->languages) ?></li>
                                <?php
                            } if (isset($module_permissions['site_roles'])) {
                                ?>
                                <li class="load_aside" load="site_roles"><?php echo(Registry::load('strings')->site_roles) ?></li>
                                <?php
                            } if (isset($module_permissions['social_login_providers'])) {
                                ?>
                                <li class="load_aside" load="social_login_providers"><?php echo(Registry::load('strings')->social_login) ?></li>
                                <?php
                            } if (isset($module_permissions['audio_player'])) {
                                ?>
                                <li class="load_aside" load="audio_player_contents"><?php echo(Registry::load('strings')->audio_player) ?></li>
                                <?php
                            } if (isset($module_permissions['site_adverts'])) {
                                ?>
                                <li class="load_aside" load="site_adverts"><?php echo(Registry::load('strings')->site_adverts) ?></li>
                                <?php
                            } if (isset($module_permissions['group_roles'])) {
                                ?>
                                <li class="load_aside" load="group_roles"><?php echo(Registry::load('strings')->group_roles) ?></li>
                                <?php
                            } if (isset($module_permissions['badges'])) {
                                ?>
                                <li class="load_aside" load="badges"><?php echo(Registry::load('strings')->badges) ?></li>
                                <?php
                            }
                            if (isset($module_permissions['firewall'])) {
                                ?>
                                <li class="load_form" form="firewall" todo="edit">
                                    <?php echo(Registry::load('strings')->firewall) ?>
                                </li>
                                <?php
                            }
                            if (isset($module_permissions['profanity_filter'])) {
                                ?>
                                <li class="load_form" form="profanity_filter" todo="edit">
                                    <?php echo(Registry::load('strings')->profanity_filter) ?>
                                </li>
                                <?php
                            }
                            if (isset($module_permissions['cron_jobs'])) {
                                ?>
                                <li class="load_aside" load="cron_jobs">
                                    <?php echo Registry::load('strings')->cron_jobs ?>
                                </li>
                                <?php
                            } ?>
                        </ul>
                    </div>
                </li>
                <?php
            }
            ?>


            <?php
            $settings_permissions = array();

            if (role(['permissions' => ['super_privileges' => 'core_settings']])) {
                $settings_permissions['core_settings'] = true;
            }

            if (role(['permissions' => ['super_privileges' => 'header_footer']])) {
                $settings_permissions['headers_footers'] = true;
            }

            if (role(['permissions' => ['super_privileges' => 'slideshows']])) {
                $settings_permissions['slideshows'] = true;
            }

            if (role(['permissions' => ['super_privileges' => 'customizer']])) {
                $settings_permissions['customizer'] = true;
            }

            if (!empty($settings_permissions)) {
                ?>
                <li class="has_child">
                    <div class="menu_item">
                        <span class="icon">
                            <i class="iconic_settings"></i>
                        </span>
                        <span class="title">
                            <?php echo Registry::load('strings')->settings ?>
                        </span>
                    </div>
                    <div class="child_menu">
                        <ul>
                            <?php
                            if (isset($settings_permissions['customizer'])) {
                                ?>
                                <li class="load_form" form="appearance" todo="edit">
                                    <?php echo(Registry::load('strings')->appearance) ?>
                                </li>
                                <?php
                            }
                            if (isset($settings_permissions['core_settings'])) {
                                ?>
                                <li class="load_form" form="settings" data-category="general_settings">
                                    <?php echo Registry::load('strings')->general_settings ?>
                                </li>

                                <li class="load_form" form="settings" data-category="email_settings">
                                    <?php echo Registry::load('strings')->email_settings ?>
                                </li>

                                <li class="load_form" form="settings" data-category="login_settings">
                                    <?php echo Registry::load('strings')->login_settings ?>
                                </li>

                                <li class="load_form" form="settings" data-category="message_settings">
                                    <?php echo Registry::load('strings')->message_settings ?>
                                </li>

                                <li class="load_form" form="settings" data-category="moderation_settings">
                                    <?php echo Registry::load('strings')->moderation_settings ?>
                                </li>

                                <li class="load_form" form="settings" data-category="notification_settings">
                                    <?php echo Registry::load('strings')->notifications ?>
                                </li>

                                <li class="load_form" form="settings" data-category="pwa_settings">
                                    <?php echo Registry::load('strings')->pwa_settings ?>
                                </li>

                                <li class="load_form" form="settings" data-category="realtime_settings">
                                    <?php echo Registry::load('strings')->realtime_settings ?>
                                </li>

                                <li class="load_form" form="landing_page" todo="edit">
                                    <?php echo(Registry::load('strings')->landing_page) ?>
                                </li>

                                <li class="load_form" form="welcome_screen" todo="edit">
                                    <?php echo(Registry::load('strings')->welcome_screen) ?>
                                </li>
                                <?php
                            }
                            if (isset($settings_permissions['customizer'])) {
                                ?>
                                <li class="load_form" form="custom_css" todo="edit">
                                    <?php echo(Registry::load('strings')->custom_css) ?>
                                </li>
                                <?php
                            }

                            if (isset($settings_permissions['slideshows'])) {
                                ?>
                                <li class="load_aside" load="slideshows">
                                    <?php echo(Registry::load('strings')->slideshows) ?>
                                </li>
                                <?php
                            }

                            if (isset($settings_permissions['core_settings'])) {
                                ?>
                                <li class="load_form" form="rebuild_cache">
                                    <?php echo(Registry::load('strings')->rebuild_cache) ?>
                                </li>
                                <li class="load_form" form="system_info">
                                    <?php echo(Registry::load('strings')->system_info) ?>
                                </li>
                                <?php
                            }
                            if (isset($settings_permissions['headers_footers'])) {
                                ?>

                                <li class="load_form" form="headers_footers" todo="edit">
                                    <?php echo Registry::load('strings')->headers_footers ?>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                </li>
                <?php
            }
            ?>

            <?php if (role(['permissions' => ['storage' => 'super_privileges']])) {
                ?>
                <li class="load_aside" load="storage">
                    <div class="menu_item">
                        <span class="icon">
                            <i class="iconic_storage"></i>
                        </span>
                        <span class="title">
                            <?php echo(Registry::load('strings')->storage) ?>
                        </span>
                    </div>
                </li>
                <?php
            } else if (role(['permissions' => ['storage' => 'access_storage']])) {
                ?>

                <li class="load_aside" load="site_user_files">
                    <div class="menu_item">
                        <span class="icon">
                            <i class="iconic_storage"></i>
                        </span>
                        <span class="title">
                            <?php echo(Registry::load('strings')->storage) ?>
                        </span>
                    </div>
                </li>
                <?php
            } ?>

            <?php if (role(['permissions' => ['complaints' => ['track_status', 'review_complaints']], 'condition' => 'OR'])) {
                ?>
                <li class="load_aside realtime_module load_complaints" module="complaints" load="complaints">
                    <div class="menu_item">
                        <span class="icon">
                            <i class="iconic_complaints"></i>
                        </span>
                        <span class="title">
                            <?php echo(Registry::load('strings')->complaints) ?>
                        </span>
                        <span class="unread"></span>
                    </div>
                </li>
                <?php
            } ?>

            <?php if (role(['permissions' => ['audio_player' => 'listen_music']])) {
                ?>
                <li class="load_audio_player">
                    <div class="menu_item">
                        <span class="icon">
                            <i class="iconic_music"></i>
                        </span>
                        <span class="title">
                            <?php echo(Registry::load('strings')->audio_player) ?>
                        </span>
                    </div>
                </li>
                <?php
            } ?>

            <?php
            if (role(['permissions' => ['profile' => 'switch_languages']])) {
                include 'layouts/chat_page/languages.php';
            }
            ?>

            <?php include 'layouts/chat_page/custom_menu_items.php'; ?>
        </ul>
    </div>
    <div class="bottom has_child side_navigation_footer">
        <div class="user_info">
            <span class="left">
                <img class="logged_in_user_avatar" src="<?php echo(get_image(['from' => 'site_users/profile_pics', 'search' => Registry::load('current_user')->id, 'gravatar' => Registry::load('current_user')->email_address])) ?>">
            </span>
            <span class="center">
                <span class="title logged_in_user_name"><?php echo Registry::load('current_user')->name; ?></span>
                <span class="sub_title">@<?php echo Registry::load('current_user')->username; ?></span>
                <span class="logged_in_user_name_color d-none"><?php echo role(['find' => 'name_color']); ?></span>
                <span class="logged_in_user_id d-none"><?php echo Registry::load('current_user')->id; ?></span>
            </span>
            <span class="right">
                <i class="icon"><i class="chevron"></i></i>
            </span>
        </div>
        <div class="child_menu">
            <span><i><?php echo Registry::load('current_user')->name; ?></i></span>
            <ul>
                <?php
                if (role(['permissions' => ['profile' => 'edit_profile']])) {
                    ?>
                    <li class='load_form' form='site_users' data-user_id="<?php echo(Registry::load('current_user')->id); ?>"><?php echo(Registry::load('strings')->edit_profile) ?></li>
                    <?php
                }
                ?>
                <li class='get_info' user_id="<?php echo(Registry::load('current_user')->id); ?>"><?php echo(Registry::load('strings')->view_profile) ?></li>

                <?php
                if (role(['permissions' => ['profile' => 'go_offline']])) {
                    if (empty(Registry::load('current_user')->offline_mode)) {
                        ?>
                        <li class='api_request' data-update="site_users_settings" data-offline_mode='go_offline'><?php echo(Registry::load('strings')->go_offline) ?></li>
                        <?php
                    } else {
                        ?>
                        <li class='api_request' data-update="site_users_settings" data-offline_mode='go_online'><?php echo(Registry::load('strings')->go_online) ?></li>
                        <?php
                    }
                }
                if (role(['permissions' => ['profile' => 'switch_color_scheme']])) {
                    if (Registry::load('current_user')->color_scheme === 'dark_mode') {
                        ?>
                        <li class='api_request'data-update="site_users_settings" data-color_scheme='light_mode'><?php echo(Registry::load('strings')->light_mode) ?></li>
                        <?php
                    } else {
                        ?>
                        <li class='api_request' data-update="site_users_settings" data-color_scheme='dark_mode'><?php echo(Registry::load('strings')->dark_mode) ?></li>
                        <?php
                    }
                }
                ?>
                <li class="api_request" data-remove="login_session"><?php echo(Registry::load('strings')->logout) ?></li>


            </ul>
        </div>
    </div>
</div>