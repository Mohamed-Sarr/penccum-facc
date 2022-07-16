<?php

$result = array();
$result['success'] = false;
$result['error_message'] = Registry::load('strings')->went_wrong;
$result['error_key'] = 'something_went_wrong';

if (role(['permissions' => ['custom_menu' => 'create']])) {

    $result['error_message'] = Registry::load('strings')->invalid_value;
    $result['error_key'] = 'invalid_value';
    $result['error_variables'] = [];

    $noerror = true;
    $disabled = $link_target = $show_on_landing_page_footer = $show_on_landing_page_header = 0;
    $show_on_entry_page = $show_on_chat_page = 0;


    if (!isset($data['menu_title']) || empty($data['menu_title'])) {
        $result['error_variables'][] = ['menu_title'];
        $noerror = false;
    }
    if (!isset($data['link_type']) || empty($data['link_type'])) {
        $result['error_variables'][] = ['link_type'];
        $noerror = false;
    }
    if (isset($data['link_type']) && $data['link_type'] === 'custom_page') {
        if (!isset($data['page_id']) || empty($data['page_id'])) {
            $result['error_variables'][] = ['page_id'];
            $noerror = false;
        } else {
            $data["page_id"] = filter_var($data["page_id"], FILTER_SANITIZE_NUMBER_INT);

            if (empty($data["page_id"])) {
                $result['error_variables'][] = ['page_id'];
                $noerror = false;
            }
        }
    }

    if ($noerror) {
        $data['menu_title'] = htmlspecialchars(trim($data['menu_title']), ENT_QUOTES, 'UTF-8');

        if (isset($data['link_type']) && $data['link_type'] === 'custom_page') {
            $data['web_address'] = '#';
        } else {
            $data['page_id'] = null;
        }

        if (!isset($data['web_address']) || empty($data['web_address'])) {
            $data['web_address'] = '#';
        }

        if (!isset($data['icon_class']) || empty($data['icon_class'])) {
            $data['icon_class'] = 'bi-card-text';
        } else {
            $data['icon_class'] = htmlspecialchars(trim($data['icon_class']), ENT_QUOTES, 'UTF-8');
        }

        if (isset($data['disabled']) && $data['disabled'] === 'yes') {
            $disabled = 1;
        }

        if (isset($data['link_target']) && $data['link_target'] === 'open_in_new_tab') {
            $link_target = 1;
        }

        if (isset($data["menu_item_order"])) {
            $data["menu_item_order"] = filter_var($data["menu_item_order"], FILTER_SANITIZE_NUMBER_INT);
        }

        if (!isset($data['menu_item_order']) || empty($data['menu_item_order'])) {
            $data["menu_item_order"] = 1;
        }

        if (isset($data['show_on_landing_page_header']) && $data['show_on_landing_page_header'] === 'yes') {
            $show_on_landing_page_header = 1;
        }

        if (isset($data['show_on_landing_page_footer']) && $data['show_on_landing_page_footer'] === 'yes') {
            $show_on_landing_page_footer = 1;
        }

        if (isset($data['show_on_chat_page']) && $data['show_on_chat_page'] === 'yes') {
            $show_on_chat_page = 1;
        }

        if (isset($data['show_on_entry_page']) && $data['show_on_entry_page'] === 'yes') {
            $show_on_entry_page = 1;
        }
        
        if (isset($data['menu_item_visibility'])) {
            $data['menu_item_visibility'] = array_filter($data['menu_item_visibility'], 'is_numeric');
            $data["menu_item_visibility"] = json_encode($data['menu_item_visibility']);
        } else {
            $data["menu_item_visibility"] = '';
        }

        DB::connect()->insert("custom_menu_items", [
            "string_constant" => $data['menu_title'],
            "menu_icon_class" => $data['icon_class'],
            "page_id" => $data['page_id'],
            "web_address" => $data['web_address'],
            "link_target" => $link_target,
            "show_on_landing_page_header" => $show_on_landing_page_header,
            "show_on_landing_page_footer" => $show_on_landing_page_footer,
            "show_on_entry_page" => $show_on_entry_page,
            "show_on_chat_page" => $show_on_chat_page,
            "menu_item_order" => $data['menu_item_order'],
            "menu_item_visibility" => $data["menu_item_visibility"],
            "disabled" => $disabled,
            "created_on" => Registry::load('current_user')->time_stamp,
            "updated_on" => Registry::load('current_user')->time_stamp,
        ]);

        if (!DB::connect()->error) {

            $menu_item_id = DB::connect()->id();
            $string_constant = 'custom_menu_item_'.$menu_item_id;
            DB::connect()->update("custom_menu_items", ["string_constant" => $string_constant], ["menu_item_id" => $menu_item_id]);
            language(['add_string' => $string_constant, 'value' => $data['menu_title']]);

            $result = array();
            $result['success'] = true;
            $result['todo'] = 'reload';
            $result['reload'] = 'custom_menu_items';
        } else {
            $result['error_message'] = Registry::load('strings')->went_wrong;
            $result['error_key'] = 'something_went_wrong';
        }

    }
}

?>