<section class="hero_section" id="home">
    <div class="introduction">

        <div class="container">
            <div class="row align-items-center min-vh-100">

                <div class="col-lg-4 text_content mx-auto text-center">
                    <div>
                        <h1 class="lh-1 mb-3"><?php echo Registry::load('strings')->landing_page_hero_section_heading; ?></h1>
                        <p class="lead">
                            <?php echo Registry::load('strings')->landing_page_hero_section_description; ?>
                        </p>
                        <div class="buttons">
                            <?php
                            if (Registry::load('settings')->user_registration === 'enable') {
                                ?>
                                <a href="<?php echo Registry::load('config')->site_url; ?>entry/signup/">
                                    <span class="button primary">
                                        <?php echo Registry::load('strings')->register; ?>
                                    </span>
                                </a>
                                <a href="<?php echo Registry::load('config')->site_url; ?>entry/">
                                    <span class="button secondary">
                                        <?php echo Registry::load('strings')->login; ?>
                                    </span>
                                </a>
                                <?php
                            } else {
                                ?>
                                <a href="<?php echo Registry::load('config')->site_url; ?>entry/">
                                    <span class="button primary">
                                        <?php echo Registry::load('strings')->login; ?>
                                    </span>
                                </a>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <span class="overlay"></span>
</section>