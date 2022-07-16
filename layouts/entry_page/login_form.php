<form id="login_form" class="login_form form_element">
    <div class="field">
        <label><?php echo Registry::load('strings')->email_username ?></label>
        <input type="text" name="user" />
    </div>
    <div class="field">
        <label><?php echo Registry::load('strings')->password ?></label>
        <input type="password" name="password" />
    </div>

    <div class="field checkbox">
        <label>
            <input type="checkbox" name="remember_me" checked value="remember">
            <span class="checkmark"></span>
            <span class="text"><?php echo Registry::load('strings')->remember_me ?></span>
        </label>
    </div>
    <div class="d-none">
        <input type="hidden" name="add" value="login_session" />

        <?php if (isset($_GET['redirect'])) {
            ?>
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect']) ?>" />
            <?php
        } ?>

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