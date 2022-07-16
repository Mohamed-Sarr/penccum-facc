<div class="form_container">

    <div class="message">
        <span class="error"><?php echo Registry::load('strings')->error ?> : </span>
        <span class="text"></span>
    </div>
    <?php include 'layouts/entry_page/login_form.php'; ?>
    <?php if (Registry::load('settings')->user_registration === 'enable') {
        include 'layouts/entry_page/signup_form.php';
    } ?>
    <?php if (Registry::load('settings')->guest_login === 'enable') {
        include 'layouts/entry_page/guest_login_form.php';
    } ?>
    <?php include 'layouts/entry_page/forgot_password_form.php'; ?>

    <div class="submit_form">
        <span class="login_form form_element" form="login_form"><?php echo Registry::load('strings')->login ?></span>

        <?php if (Registry::load('settings')->guest_login === 'enable') {
            ?>
            <span class="guest_login_form form_element d-none" form="guest_login_form"><?php echo Registry::load('strings')->login ?></span>
            <?php
        } ?>
        <?php if (Registry::load('settings')->user_registration === 'enable') {
            ?>
            <span class="signup_form form_element d-none" form="signup_form"><?php echo Registry::load('strings')->register ?></span>
            <?php
        } ?>
        <span class="forgot_password_form form_element d-none" form="forgot_password_form"><?php echo Registry::load('strings')->send_mail ?></span>
    </div>

    <div class="change_form ">
        <div>
            <span class="switch_form login_form form_element" form="forgot_password">
                <?php echo Registry::load('strings')->forgot_password ?>
            </span>
            <span class="switch_form forgot_password_form form_element d-none" form="login">
                <?php echo Registry::load('strings')->back_to_login ?>
            </span>
        </div>
    </div>

    <?php include 'layouts/entry_page/login_providers.php'; ?>

</div>