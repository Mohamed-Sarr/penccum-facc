<form id="forgot_password_form" class="forgot_password_form form_element d-none">

    <div class="d-none">
        <input type="hidden" name="add" value="access_token" />
    </div>
    <div class="field">
        <label><?php echo Registry::load('strings')->email_username ?></label>
        <input type="text" name="user" />
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
</form>