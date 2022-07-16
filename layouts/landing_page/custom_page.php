<?php

$columns = $join = $where = null;
$page_accessible = true;

$columns = [
    'language_strings.string_value(page_title)', 'custom_pages.disabled',
    'custom_pages.who_all_can_view_page'
];

$join["[>]language_strings"] = ["custom_pages.string_constant" => "string_constant", "AND" => ["language_id" => Registry::load('current_user')->language]];

$where["custom_pages.page_id"] = Registry::load('config')->load_page;
$where["custom_pages.disabled"] = 0;

$where["LIMIT"] = 1;

$custom_page = DB::connect()->select('custom_pages', $join, $columns, $where);
if (isset($custom_page[0]) && $custom_page[0]['who_all_can_view_page'] !== 'all') {
    $who_all_can_view_page = json_decode($custom_page[0]['who_all_can_view_page']);
    if (!in_array(Registry::load('current_user')->site_role, $who_all_can_view_page)) {
        $page_accessible = false;
    }
}

if (isset($custom_page[0]) && $page_accessible) {
    $custom_page = $custom_page[0];

    $columns = $join = $where = null;
    $columns = ['language_strings.string_value(page_content)'];
    $where["language_strings.language_id"] = Registry::load('current_user')->language;
    $where["language_strings.string_constant"] = 'custom_page_'.Registry::load('config')->load_page.'_content';
    $where["LIMIT"] = 1;

    $page_content = DB::connect()->select('language_strings', $columns, $where);
    $featured_image = get_image(['from' => 'custom_pages', 'search' => Registry::load('config')->load_page]);

    if (isset($page_content[0])) {
        $page_content = $page_content[0]['page_content'];
    } else {
        $page_content = '';
    }
} else {
    redirect('404');
}
?>

<section class="custom_page">
    <div class="header">
        <div class="text-center container heading">
            <div class="row py-lg-5">
                <div class="col-lg-4 col-md-8 mx-auto">
                    <h1><?php echo $custom_page['page_title']; ?></h1>
                </div>
            </div>
        </div>
        <div class="divider">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
            </svg>
        </div>
        <div class="featured_image">
            <div class="image">
                <img src="<?php echo $featured_image; ?>">
                <span class="overlay"></span>
            </div>
        </div>
    </div>

    <div class="content">
        <div>
            <div class="container">
                <div class="row py-lg-5">
                    <div class="col-lg-10 mx-auto">
                        <?php echo $page_content; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>