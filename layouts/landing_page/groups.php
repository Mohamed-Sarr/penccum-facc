<section class="groups_list" id="groups">

    <div>
        <div class="heading">
            <div class="container">
                <div class="row">
                    <div class="col-lg-5 mx-auto">
                        <h3><?php echo Registry::load('strings')->landing_page_groups_section_heading; ?></h3>
                        <p>
                            <?php echo nl2br(Registry::load('strings')->landing_page_groups_section_description); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="groups">
            <div class="container">
                <div class="row row-cols-1 row-cols-md-4 g-4">

                    <?php
                    $columns = $join = $where = null;
                    $columns = [
                        'groups.group_id', 'groups.name', 'groups.slug', 'groups.description'
                    ];
                    $where["groups.description[!]"] = '';
                    $where["groups.suspended"] = 0;
                    $where["AND"] = [
                        "OR" => [
                            "groups.password(password_null)" => null,
                            "groups.password(password_empty)" => '',
                            "groups.password(password_zero)" => "0"
                        ],
                        "groups.secret_group" => "0"
                    ];
                    $where["ORDER"] = ["groups.pin_group" => "DESC", "groups.updated_on" => "DESC"];
                    $where["LIMIT"] = 16;

                    $groups = DB::connect()->select('groups', $columns, $where);
                    foreach ($groups as $group) {
                        $group_image = get_image(['from' => 'groups/icons', 'search' => $group['group_id']]);
                        $group_cover_pic = get_image(['from' => 'groups/cover_pics', 'search' => $group['group_id']]);
                        $group_url = Registry::load('config')->site_url;

                        if (!empty($group['slug'])) {
                            $group_url .= $group['slug'].'/';
                        } else {
                            $group_url .= 'group/'.$group['group_id'].'/';
                        }

                        ?>
                        <div class="col">
                            <a href="<?php echo $group_url; ?>">
                                <div class="card h-100">
                                    <div class="group_cover_pic">
                                        <img src="<?php echo $group_cover_pic; ?>" />
                                    </div>
                                    <div class="card-body">
                                        <div class="group_image">
                                            <img src="<?php echo $group_image; ?>" />
                                        </div>
                                        <h5 class="card-title"><?php echo $group['name']; ?></h5>
                                        <p class="card-text">
                                            <?php echo $group['description']; ?>
                                        </p>
                                    </div>
                                    <div class="card-footer">
                                        <div class="button">
                                            <span><?php echo Registry::load('strings')->view_group; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <?php
                    }
                    ?>
                </div>

                <?php
                $site_advert = DB::connect()->rand("site_advertisements",
                    ['site_advertisements.site_advert_min_height', 'site_advertisements.site_advert_max_height',
                        'site_advertisements.site_advert_content'],
                    ["site_advertisements.site_advert_placement" => 'landing_page_groups_section', "site_advertisements.disabled[!]" => 1, "LIMIT" => 1]
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

    </div>
</section>