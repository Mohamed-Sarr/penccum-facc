<div class="preloader">
    <div>
        <div class='ripple-background'>
            <div class='circle xxlarge shade1'></div>
            <div class='circle xlarge shade2'></div>
            <div class='circle large shade3'></div>
            <div class='circle mediun shade4'></div>
            <div class='circle small shade5'></div>
        </div>
    </div>
</div>

<?php if (Registry::load('settings')->entry_page_background === 'slideshow') {
    ?>
    <div class="slideshow">
        <ul>
            <?php
            $slideshow_folder = 'assets/files/slideshows/entry_page/*';
            $extensions = rangeof_chars('jpg,png,gif,jpeg,bmp');
            $slideshow_folder = $slideshow_folder.'.{'.$extensions.'}';

            $slideshow_images = glob($slideshow_folder, GLOB_BRACE);

            foreach ($slideshow_images as $slideshow_image) {
                ?>
                <li><img src="<?php echo Registry::load('config')->site_url.$slideshow_image; ?>" /></li>
                <?php
            }
            ?>
        </ul>
    </div>

    <?php
} else {
    ?>
    <div class="image">
        <?php if (Registry::load('current_user')->color_scheme === 'dark_mode') {
            ?>
            <img src="<?php echo Registry::load('config')->site_url.'assets/files/backgrounds/entry_page_bg_dark_mode.jpg'.$cache_timestamp; ?>" />
            <?php
        } else {
            ?>
            <img src="<?php echo Registry::load('config')->site_url.'assets/files/backgrounds/entry_page_bg.jpg'.$cache_timestamp; ?>" />
            <?php
        } ?>
    </div>

    <?php
} ?>