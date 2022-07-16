<?php

$columns = $where = null;
$columns = [
    'custom_menu_items.string_constant', 'custom_menu_items.page_id', 'custom_menu_items.web_address',
    'custom_menu_items.menu_icon_class', 'custom_menu_items.link_target',
    'custom_menu_items.menu_item_visibility',
];

$where["custom_menu_items.disabled"] = 0;
$where["custom_menu_items.show_on_entry_page"] = 1;
$where["ORDER"] = ["custom_menu_items.menu_item_order" => "ASC"];

$menu_items = DB::connect()->select('custom_menu_items', $columns, $where);

foreach ($menu_items as $menu_item) {

    $skip_menu_item = false;

    if ($menu_item['menu_item_visibility'] !== 'all') {
        $menu_item_visibility = json_decode($menu_item['menu_item_visibility']);
        if (!in_array(Registry::load('current_user')->site_role, $menu_item_visibility)) {
            $skip_menu_item = true;
        }
    }


    if (!$skip_menu_item) {
        $menu_item_title = $menu_item['string_constant'];
        $menu_item_attributes = '';

        if (!empty($menu_item['page_id'])) {
            $menu_item_attributes .= 'class="load_page"';
            $menu_item_attributes .= 'page_id="'.$menu_item['page_id'].'"';
        } else {
            $menu_item_attributes .= 'class="open_link"';
            $menu_item_attributes .= 'link="'.$menu_item['web_address'].'"';

            if (!empty($menu_item['link_target'])) {
                $menu_item_attributes .= 'open_in_new_tab="true"';
            }
        }

        ?>
        <li <?php echo $menu_item_attributes ?>>
            <span class="title"><?php echo Registry::load('strings')->$menu_item_title; ?></span>
        </li>

        <?php
    }
}
?>