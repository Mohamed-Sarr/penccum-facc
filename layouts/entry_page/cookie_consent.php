<?php

if (isset($_COOKIE["cookie_constent"]) && $_COOKIE["cookie_constent"] === 'accepted') {
    Registry::load('settings')->cookie_consent = 'disable';
}

if (isset(Registry::load('settings')->cookie_consent) && Registry::load('settings')->cookie_consent === 'enable') {
    ?>
    <div class="modal fade cookie_consent_modal" id="cookie_consent" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo Registry::load('strings')->cookie_consent_modal_title ?></h5>
                </div>
                <div class="modal-body">
                    <?php echo Registry::load('strings')->cookie_consent_modal_content ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="agree_btn" data-bs-dismiss="modal" accept='true'>
                        <?php echo Registry::load('strings')->agree ?>
                    </button>
                    <button type="button" class="disagree_btn">
                        <?php echo Registry::load('strings')->disagree ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php
}

?>