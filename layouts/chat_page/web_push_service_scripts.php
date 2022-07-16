<?php
if (Registry::load('current_user')->logged_in) {
    if (!empty(Registry::load('settings')->push_notifications) && Registry::load('settings')->push_notifications !== 'disable') {
        if (Registry::load('settings')->push_notifications === 'onesignal') {
            ?>

            <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js"></script>
            <?php
        } ?>

        <script src="<?php echo(Registry::load('config')->site_url) ?>assets/js/chat_page/push_notifications.js"></script>
        <?php
    }
} ?>