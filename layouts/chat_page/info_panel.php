<div class="col-md-5 col-lg-3 info_panel d-none page_column" column="fourth">
    <div class="fixed_header">
        <div class="icons">
            <div class="left">

            </div>
            <div class="right">
                <span class="icon close_info_panel">
                    <i class="bi bi-x-lg"></i>
                </span>
            </div>
        </div>
    </div>
    <div class="coverpic">
        <span class="img"></span>
    </div>

    <div class="confirm_box d-none animate__animated animate__flipInX">
        <div class="error">
            <span class="message"><?php echo(Registry::load('strings')->error) ?> : <span></span></span>
        </div>
        <div class="content">
            <span class="text"></span>
            <span class="btn cancel" column="fourth"><span></span></span>
            <span class="btn submit"><span></span></span>
        </div>
    </div>

    <div class="info_box">
        <span class="img">
            <img>
            <span class="online_status"><span></span></span>
        </span>
        <span class="heading"></span>
        <span class="subheading"></span>
    </div>

    <div class="controls">
        <div>
            <span class="button"></span>
            <div class="options dropdown_button">
                <span class="text"><?php echo(Registry::load('strings')->options) ?></span>
                <div class="dropdown_list">
                    <ul></ul>
                </div>
            </div>
        </div>
    </div>


    <div class="statistics">
        <div></div>
    </div>


    <div class="content">
        <div class="fields"></div>
    </div>


    <?php
    if (!role(['permissions' => ['site_adverts' => 'ad_free_account']])) {
        $site_advert = DB::connect()->rand("site_advertisements",
            ['site_advertisements.site_advert_min_height', 'site_advertisements.site_advert_max_height',
                'site_advertisements.site_advert_content'],
            ["site_advertisements.site_advert_placement" => 'info_panel', "disabled[!]" => 1, "LIMIT" => 1]
        );
        if (isset($site_advert[0])) {
            $site_advert = $site_advert[0];
            $advert_css = 'max-height:'.$site_advert['site_advert_max_height'].'px;';

            if (!empty($site_advert['site_advert_min_height'])) {
                $advert_css .= 'min-height:'.$site_advert['site_advert_min_height'].'px;';
            }
            ?>

            <div class="site_advert_block" style="<?php echo $advert_css; ?>">
                <div>
                    <?php echo $site_advert['site_advert_content']; ?>
                </div>
            </div>
            <?php
        }
    }
    ?>

    <div class="loader">
        <div>
            <span class="cover_pic"></span>
            <span class="image">
                <span class="default_image"></span>
                <span class="error_image"><img src="<?php echo Registry::load('config')->site_url ?>assets/files/defaults/error_image.png" /></span>
            </span>

            <span class="error_text">
                <span class="title"></span>
                <span class="subtitle"></span>
            </span>

            <span class="heading"><span></span></span>
            <span class="subheading">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </span>
            <span class="icons">
                <span></span>
                <span></span><span></span><span></span>
                <span></span>
                <span></span>
            </span>
            <span class="contents">
                <span>
                    <span class="title"></span>
                    <span class="value"></span>
                </span>
                <span>
                    <span class="title"></span>
                    <span class="value"></span>
                </span>
                <span>
                    <span class="title"></span>
                    <span class="value"></span>
                </span>
                <span>
                    <span class="title"></span>
                    <span class="value"></span>
                </span>
                <span>
                    <span class="title"></span>
                    <span class="value"></span>
                </span>
            </span>
        </div>
    </div>

</div>