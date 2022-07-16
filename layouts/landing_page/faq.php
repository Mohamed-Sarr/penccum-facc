<section class="frequently_asked_questions" id="faq">

    <div class="divider d-none">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0V7.23C0,65.52,268.63,112.77,600,112.77S1200,65.52,1200,7.23V0Z" class="shape-fill"></path>
        </svg>
    </div>

    <div>
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h3><?php echo Registry::load('strings')->landing_page_faq_section_heading; ?></h3>
                    <div class="questions">

                        <?php
                        for ($index = 1; $index <= 10; $index++) {
                            $question = 'landing_page_faq_question_'.$index;
                            $answer = $question.'_answer';

                            if (isset(Registry::load('strings')->$question) && !empty(Registry::load('strings')->$question)) {
                                ?>
                                <div class="item">
                                    <div class="question">
                                        <?php echo Registry::load('strings')->$question; ?>
                                    </div>
                                    <div class="answer">
                                        <?php echo nl2br(Registry::load('strings')->$answer); ?>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>

                    </div>
                </div>
            </div>

            <?php
            $site_advert = DB::connect()->rand("site_advertisements",
                ['site_advertisements.site_advert_min_height', 'site_advertisements.site_advert_max_height',
                    'site_advertisements.site_advert_content'],
                ["site_advertisements.site_advert_placement" => 'landing_page_faq_section', "site_advertisements.disabled[!]" => 1, "LIMIT" => 1]
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
            ?>
        </div>


    </div>
</section>