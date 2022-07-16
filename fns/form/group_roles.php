<?php

$form = array();

if (role(['permissions' => ['group_roles' => ['create', 'edit']], 'condition' => 'OR'])) {


    $form['loaded'] = new stdClass();
    $todo = 'add';
    $language_id = Registry::load('current_user')->language;

    $form['fields'] = new stdClass();

    if (isset($load["group_role_id"])) {

        $todo = 'update';
        $columns = [
            'languages.name', 'languages.language_id'
        ];

        $where["languages.disabled[!]"] = 1;

        $languages = DB::connect()->select('languages', $columns, $where);

        if (isset($load["language_id"])) {
            $load["language_id"] = filter_var($load["language_id"], FILTER_SANITIZE_NUMBER_INT);

            if (!empty($load["language_id"])) {
                $language_id = $load["language_id"];
            }
        }

        $columns = $join = $where = null;

        $columns = [
            'group_roles.group_role_id', 'group_roles.permissions',
            'string.string_value(name)', 'group_roles.disabled',
            'group_roles.group_role_attribute'
        ];

        $join["[>]language_strings(string)"] = ["group_roles.string_constant" => "string_constant", "AND" => ["language_id" => $language_id]];

        $where["group_roles.group_role_id"] = $load["group_role_id"];
        $where["LIMIT"] = 1;

        $group_role = DB::connect()->select('group_roles', $join, $columns, $where);

        if (!isset($group_role[0])) {
            return false;
        } else {
            $group_role = $group_role[0];
        }

        $form['fields']->group_role_id = [
            "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $load["group_role_id"]
        ];
        $form['loaded']->title = Registry::load('strings')->edit_group_role;
        $form['loaded']->button = Registry::load('strings')->update;
    } else {
        $form['loaded']->title = Registry::load('strings')->create_group_role;
        $form['loaded']->button = Registry::load('strings')->create;
    }

    $form['fields']->process = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $todo
    ];
    $form['fields']->$todo = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => 'group_roles'
    ];

    if (isset($load["group_role_id"])) {

        $form['fields']->language_id = [
            "title" => Registry::load('strings')->language, "tag" => 'select', "class" => 'field'
        ];

        if (isset($load["language_id"]) && !empty($load["language_id"])) {
            $form['fields']->language_id['value'] = $load["language_id"];
        }
        $form['fields']->language_id["class"] = 'field switch_form';
        $form['fields']->language_id["parent_attributes"] = [
            "form" => "group_roles",
            "data-group_role_id" => $load["group_role_id"],
        ];

        foreach ($languages as $language) {
            $language_identifier = $language['language_id'];
            $form['fields']->language_id['options'][$language_identifier] = $language['name'];
        }

        $form['fields']->identifier = [
            "title" => Registry::load('strings')->identifier, "tag" => 'input', "type" => 'text', "class" => 'field',
            "attributes" => ["disabled" => "disabled"],
            "value" => $load["group_role_id"],
        ];

    }


    $form['fields']->name = [
        "title" => Registry::load('strings')->role_name, "tag" => 'input', "type" => 'text', "class" => 'field', "placeholder" => Registry::load('strings')->role_name
    ];

    $form['fields']->badge = [
        "title" => Registry::load('strings')->badge, "tag" => 'input', "type" => 'file', "class" => 'field filebrowse',
        "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
    ];

    $form['fields']->show_label = [
        "title" => Registry::load('strings')->show_group_label, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->show_label['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    $form['fields']->label_background_color = [
        "title" => Registry::load('strings')->label_background_color, "tag" => 'input', "type" => 'color', "class" => 'field'
    ];

    $form['fields']->label_text_color = [
        "title" => Registry::load('strings')->label_text_color, "tag" => 'input', "type" => 'color', "class" => 'field'
    ];

    $form['fields']->attribute = [
        "title" => Registry::load('strings')->attribute, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->attribute['options'] = [
        "custom_group_role" => Registry::load('strings')->custom_group_role,
        "default_group_role" => Registry::load('strings')->default_group_role,
        "administrators" => Registry::load('strings')->administrators,
        "moderators" => Registry::load('strings')->moderators,
        "banned_users" => Registry::load('strings')->banned_users,
    ];

    $form['fields']->disabled = [
        "title" => Registry::load('strings')->disabled, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->disabled['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    $form['fields']->group = [
        "title" => Registry::load('strings')->group, "tag" => 'checkbox', "class" => 'field'
    ];

    $form['fields']->group['options'] = [
        "edit_group" => Registry::load('strings')->edit_group,
        "view_shared_files" => Registry::load('strings')->view_shared_files,
        "view_shared_links" => Registry::load('strings')->view_shared_links,
        "delete_group" => Registry::load('strings')->delete_group
    ];

    $form['fields']->group_members = [
        "title" => Registry::load('strings')->group_members, "tag" => 'checkbox', "class" => 'field'
    ];

    $form['fields']->group_members['options'] = [
        "view_group_members" => Registry::load('strings')->view_group_members,
        "view_currently_online" => Registry::load('strings')->view_currently_online,
        "ban_users_from_group" => Registry::load('strings')->ban_users_from_group,
        "unban_users_from_group" => Registry::load('strings')->unban_users_from_group,
        "manage_user_roles" => Registry::load('strings')->manage_user_roles,
        "remove_group_members" => Registry::load('strings')->remove_group_members,

    ];

    $form['fields']->messages = [
        "title" => Registry::load('strings')->messages, "tag" => 'checkbox', "class" => 'field'
    ];

    $form['fields']->messages['options'] = [
        "send_message" => Registry::load('strings')->send_message,
        "send_audio_message" => Registry::load('strings')->send_audio_message,
        "attach_files" => Registry::load('strings')->attach_files,
        "attach_from_storage" => Registry::load('strings')->attach_from_storage,
        "attach_gifs" => Registry::load('strings')->attach_gifs,
        "attach_stickers" => Registry::load('strings')->attach_stickers,
        "check_read_receipts" => Registry::load('strings')->check_read_receipts,
        "share_screenshot" => Registry::load('strings')->share_screenshot,
        "allow_sharing_links" => Registry::load('strings')->allow_sharing_links,
        "generate_link_preview" => Registry::load('strings')->generate_link_preview,
        "download_attachments" => Registry::load('strings')->download_attachments,
        "delete_own_message" => Registry::load('strings')->delete_own_message,
        "delete_messages" => Registry::load('strings')->delete_all_messages,
        "view_reactions" => Registry::load('strings')->view_reactions,
        "react_messages" => Registry::load('strings')->react_messages,
        "reply_messages" => Registry::load('strings')->reply_messages,
        "mention_users" => Registry::load('strings')->mention_users,
    ];


    if (isset($load["group_role_id"])) {

        $disabled = 'no';

        if ((int)$group_role['disabled'] === 1) {
            $disabled = 'yes';
        }

        $form['fields']->disabled["value"] = $disabled;

        $permissions = array();

        if (!empty($group_role['permissions'])) {
            $permissions = json_decode($group_role['permissions']);

            if (!empty($permissions)) {
                $permissions = get_object_vars($permissions);
            } else {
                $permissions = array();
            }
        }

        unset($form['fields']->name["placeholder"]);
        $form['fields']->name["value"] = $group_role['name'];

        foreach ($permissions as $permission => $allowed_permissions) {
            if (isset($form['fields']->$permission)) {
                $form['fields']->$permission["value"] = $allowed_permissions;
            }
        }

        if (isset($group_role['group_role_attribute'])) {
            $form['fields']->attribute['value'] = $group_role['group_role_attribute'];
        }

    }

}

?>