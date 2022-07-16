<?php
if (Registry::load('current_user')->logged_in) {
    if (!empty(Registry::load('settings')->push_notifications) && Registry::load('settings')->push_notifications !== 'disable') {
        ?>

        <div class="web_push_service_variables d-none">
            <span class='provider'><?php echo Registry::load('settings')->push_notifications ?></span>
            <?php
            if (Registry::load('settings')->push_notifications === 'webpushr') {
                ?>
                <span class='public_key'><?php echo (Registry::load('settings')->webpushr_public_key) ?></span>
                <?php
            } else if (Registry::load('settings')->push_notifications === 'onesignal') {
                ?>
                <span class='appId'><?php echo (Registry::load('settings')->onesignal_app_id) ?></span>
                <span class='safari_web_id'><?php echo (Registry::load('settings')->onesignal_safari_web_id) ?></span>
                <span class='prompt_message'><?php echo (Registry::load('strings')->onesignal_prompt_message) ?></span>
                <span class='prompt_accept_button'><?php echo (Registry::load('strings')->onesignal_prompt_accept_button) ?></span>
                <span class='prompt_cancel_button'><?php echo (Registry::load('strings')->onesignal_prompt_cancel_button) ?></span>
                <span class='navigation_scope'><?php echo Registry::load('config')->navigation_scope; ?></span>
                <?php
            } ?>

        </div>
        <?php
    }
} ?>