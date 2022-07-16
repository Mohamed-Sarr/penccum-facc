<form class="guest_login_form form_element d-none" id="guest_login_form">
    <div class="d-none">
        <input type="hidden" name="add" value="guest_user" />

        <?php if (isset($_GET['redirect'])) {
            ?>
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect']) ?>" />
            <?php
        } ?>

    </div>
    <div class="field">
        <label><?php echo Registry::load('strings')->nickname ?></label>
        <input type="text" name="nickname" />
    </div>

    <div class="captcha_validation">
        <?php if (isset(Registry::load('settings')->captcha) && Registry::load('settings')->captcha === 'google_recaptcha_v2') {
            ?>
            <div class="captcha_box g-recaptcha" data-sitekey="<?php echo Registry::load('settings')->captcha_site_key; ?>"></div>
            <?php
        } else if (isset(Registry::load('settings')->captcha) && Registry::load('settings')->captcha === 'hcaptcha') {
            ?>
            <div class="captcha_box h-captcha" data-sitekey="<?php echo Registry::load('settings')->captcha_site_key; ?>"></div>
            <?php
        } ?>
    </div>
</form>