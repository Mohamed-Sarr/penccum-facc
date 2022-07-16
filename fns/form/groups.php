<?php

$todo = 'add';
$group_id = 0;
$super_privileges = false;

if (role(['permissions' => ['groups' => 'super_privileges']])) {
    $super_privileges = true;
}


if (isset($load['group_id'])) {
    $load["group_id"] = filter_var($load["group_id"], FILTER_SANITIZE_NUMBER_INT);
    if (!empty($load['group_id'])) {
        $todo = 'update';
        $group_id = $load["group_id"];
    }
}

if (!role(['permissions' => ['groups' => 'create_groups']]) && empty($group_id)) {
    return false;
}

$columns = $where = $join = null;
$columns = ['custom_fields.string_constant(field_name)', 'custom_fields.field_type', 'custom_fields.editable_only_once', 'custom_fields.required'];

if (!empty($group_id)) {
    $columns[] = 'custom_fields_values.field_value';
    $join["[>]custom_fields_values"] = ["custom_fields.field_id" => "field_id", "AND" => ["group_id" => $group_id]];
}

$where['AND'] = ['custom_fields.field_category' => 'group', 'custom_fields.disabled' => 0];
$where["ORDER"] = ["custom_fields.field_id" => "ASC"];

if (!empty($group_id)) {
    $custom_fields = DB::connect()->select('custom_fields', $join, $columns, $where);
} else {
    $custom_fields = DB::connect()->select('custom_fields', $columns, $where);
}


$form['loaded'] = new stdClass();
$form['fields'] = new stdClass();

if (!empty($group_id)) {

    $columns = $where = $join = null;
    $columns = [
        'groups.group_id', 'groups.name', 'groups.slug', 'groups.description', 'groups.meta_description',
        'groups.secret_group', 'groups.password', 'groups.unleavable', 'groups.who_all_can_send_messages',
        'groups.pin_group', 'groups.auto_join_group', 'groups.meta_title', 'group_members.group_role_id',
        'groups.suspended'
    ];

    $join["[>]group_members"] = ["groups.group_id" => "group_id", "AND" => ["user_id" => Registry::load('current_user')->id]];

    $where["groups.group_id"] = $group_id;
    $where["LIMIT"] = 1;

    $group = DB::connect()->select('groups', $join, $columns, $where);

    if (!isset($group[0])) {
        return false;
    } else {
        $group = $group[0];
    }

    if (!$super_privileges && isset($group['suspended']) && !empty($group['suspended'])) {
        return false;
    }

    if ($super_privileges || isset($group['group_role_id']) && !empty($group['group_role_id'])) {
        if (!$super_privileges && !role(['permissions' => ['group' => 'edit_group'], 'group_role_id' => $group['group_role_id']])) {
            return false;
        }
    } else {
        return false;
    }

    $form['fields']->group_id = [
        "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => $group_id
    ];

    $form['loaded']->title = Registry::load('strings')->edit_group;
    $form['loaded']->button = Registry::load('strings')->update;
} else {
    $form['loaded']->title = Registry::load('strings')->create_group;
    $form['loaded']->button = Registry::load('strings')->create;
}

$form['fields']->$todo = [
    "tag" => 'input', "type" => 'hidden', "class" => 'd-none', "value" => "groups"
];

$form['fields']->group_name = [
    "title" => Registry::load('strings')->group_name, "tag" => 'input', "type" => "text",
    "class" => 'field', "placeholder" => Registry::load('strings')->group_name,
    "required" => true
];

if (role(['permissions' => ['groups' => 'set_group_slug']])) {
    $form['fields']->slug = [
        "title" => Registry::load('strings')->slug, "tag" => 'input', "type" => "text", "class" => 'field',
        "placeholder" => Registry::load('strings')->slug,
    ];
}

$form['fields']->description = [
    "title" => Registry::load('strings')->description, "tag" => 'textarea', "class" => 'field',
    "placeholder" => Registry::load('strings')->description,
];

$form['fields']->description["attributes"] = ["rows" => 4];

if (role(['permissions' => ['groups' => 'add_meta_tags']])) {
    $form['fields']->meta_title = [
        "title" => Registry::load('strings')->meta_title, "tag" => 'input', "type" => "text",
        "class" => 'field', "placeholder" => Registry::load('strings')->meta_title,
    ];

    $form['fields']->meta_description = [
        "title" => Registry::load('strings')->meta_description, "tag" => 'textarea', "class" => 'field',
        "placeholder" => Registry::load('strings')->meta_description,
    ];

    $form['fields']->meta_description["attributes"] = ["rows" => 4];
}

$form['fields']->group_icon = [
    "title" => Registry::load('strings')->group_icon, "tag" => 'input', "type" => 'file', "class" => 'field filebrowse',
    "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
];

if (role(['permissions' => ['groups' => 'set_cover_pic']])) {
    $form['fields']->cover_pic = [
        "title" => Registry::load('strings')->cover_pic, "tag" => 'input', "type" => 'file', "class" => 'field filebrowse',
        "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
    ];

    if (!empty($group_id)) {
        if (get_image(['from' => 'groups/cover_pics', 'search' => $group_id, 'exists' => true])) {
            $form['fields']->remove_cover_pic = [
                "title" => Registry::load('strings')->remove_cover_pic, "tag" => 'select', "class" => 'field'
            ];
            $form['fields']->remove_cover_pic['options'] = [
                "yes" => Registry::load('strings')->yes, "no" => Registry::load('strings')->no,
            ];
        }
    }
}

if (role(['permissions' => ['groups' => 'set_custom_background']])) {
    $form['fields']->custom_background = [
        "title" => Registry::load('strings')->custom_background, "tag" => 'input', "type" => 'file', "class" => 'field filebrowse',
        "accept" => 'image/png,image/x-png,image/gif,image/jpeg'
    ];

    if (!empty($group_id)) {
        if (get_image(['from' => 'groups/backgrounds', 'search' => $group_id, 'exists' => true])) {
            $form['fields']->remove_custom_bg = [
                "title" => Registry::load('strings')->remove_custom_bg, "tag" => 'select', "class" => 'field'
            ];
            $form['fields']->remove_custom_bg['options'] = [
                "yes" => Registry::load('strings')->yes, "no" => Registry::load('strings')->no,
            ];
        }
    }
}

foreach ($custom_fields as $custom_field) {
    $field_name = $custom_field['field_name'];

    if ($custom_field['field_type'] === 'short_text' || $custom_field['field_type'] === 'link') {

        $form['fields']->$field_name = [
            "title" => Registry::load('strings')->$field_name, "tag" => 'input', "type" => 'text', "class" => 'field',
            "placeholder" => Registry::load('strings')->$field_name,
        ];

    } else if ($custom_field['field_type'] === 'long_text') {

        $form['fields']->$field_name = [
            "title" => Registry::load('strings')->$field_name, "tag" => 'textarea', "class" => 'field',
            "placeholder" => Registry::load('strings')->$field_name,
        ];
        $form['fields']->$field_name["attributes"] = ["rows" => 6];

    } else if ($custom_field['field_type'] === 'date') {

        $form['fields']->$field_name = [
            "title" => Registry::load('strings')->$field_name, "tag" => 'input', "type" => 'date', "class" => 'field',
            "attributes" => ['class' => 'icon-calendar'], "placeholder" => Registry::load('strings')->$field_name,
        ];

    } else if ($custom_field['field_type'] === 'number') {

        $form['fields']->$field_name = [
            "title" => Registry::load('strings')->$field_name, "tag" => 'input', "type" => 'number', "class" => 'field',
            "placeholder" => Registry::load('strings')->$field_name,
        ];

    } else if ($custom_field['field_type'] === 'dropdown') {

        $field_options = array();
        $dropdownoptions = $field_name.'_options';

        if (isset(Registry::load('strings')->$dropdownoptions)) {
            $field_options = json_decode(Registry::load('strings')->$dropdownoptions);
        }

        $form['fields']->$field_name = [
            "title" => Registry::load('strings')->$field_name, "tag" => 'select', "class" => 'field',
            "options" => $field_options,
        ];

    }

    if ((int)$custom_field['required'] === 1) {
        $form['fields']->$field_name['required'] = true;
    }

    if (!empty($group_id)) {
        if (isset($custom_field['field_value']) && !empty($custom_field['field_value'])) {
            $form['fields']->$field_name['value'] = $custom_field['field_value'];
            if (!empty($custom_field['editable_only_once']) && !role(['permissions' => ['groups' => 'super_privileges']])) {
                $form['fields']->$field_name['attributes']['disabled'] = 'disabled';
            }
        }
    }
}

if (role(['permissions' => ['groups' => 'create_protected_group']])) {
    $form['fields']->password_protect = [
        "title" => Registry::load('strings')->password_protect, "tag" => 'select', "class" => 'field showfieldon'
    ];

    $form['fields']->password_protect["attributes"] = ["fieldclass" => "group_password", "checkvalue" => "yes"];

    $form['fields']->password_protect['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];

    $form['fields']->password = [
        "title" => Registry::load('strings')->password, "tag" => 'input', "type" => 'password', "class" => 'field group_password d-none',
        "placeholder" => Registry::load('strings')->password,
    ];

    $form['fields']->confirm_password = [
        "title" => Registry::load('strings')->confirm_password, "tag" => 'input', "type" => 'text', "class" => 'field group_password d-none',
        "placeholder" => Registry::load('strings')->confirm_password,
    ];
}

if (role(['permissions' => ['groups' => 'create_secret_group']])) {
    $form['fields']->secret_group = [
        "title" => Registry::load('strings')->secret_group, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->secret_group['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];
}

if (role(['permissions' => ['groups' => 'pin_groups']])) {
    $form['fields']->pin_group = [
        "title" => Registry::load('strings')->pin_group, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->pin_group['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];
}

if (role(['permissions' => ['groups' => 'create_unleavable_group']])) {
    $form['fields']->unleavable = [
        "title" => Registry::load('strings')->unleavable, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->unleavable['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];
}

if (role(['permissions' => ['groups' => 'set_auto_join_groups']])) {
    $form['fields']->auto_join_group = [
        "title" => Registry::load('strings')->auto_join_group, "tag" => 'select', "class" => 'field'
    ];
    $form['fields']->auto_join_group['options'] = [
        "yes" => Registry::load('strings')->yes,
        "no" => Registry::load('strings')->no,
    ];
}

if (role(['permissions' => ['groups' => 'set_participant_settings']])) {
    $form['fields']->who_all_can_send_messages = [
        "title" => Registry::load('strings')->who_all_can_send_messages, "tag" => 'select', "class" => 'field'
    ];

    $language_id = Registry::load('current_user')->language;

    $join = ["[>]language_strings(string)" => ["group_roles.string_constant" => "string_constant", "AND" => ["language_id" => $language_id]]];
    $columns = ['group_roles.group_role_id', 'string.string_value(name)'];
    $where = ['group_role_attribute[!]' => 'banned_users'];

    $group_roles = DB::connect()->select('group_roles', $join, $columns, $where);

    $group_roles = array_column($group_roles, 'name', 'group_role_id');

    $form['fields']->who_all_can_send_messages = [
        "title" => Registry::load('strings')->who_all_can_send_messages, "tag" => 'checkbox',
        "class" => 'field', 'options' => $group_roles, 'select_all' => true
    ];
}


if (!empty($group_id)) {
    $auto_join_group = $password_protect = $secret_group = $unleavable = $pin_group = 'no';

    if ((int)$group['auto_join_group'] === 1) {
        $auto_join_group = 'yes';
    }

    if (!empty($group['password'])) {
        $password_protect = 'yes';
    }

    if ((int)$group['secret_group'] === 1) {
        $secret_group = 'yes';
    }

    if ((int)$group['unleavable'] === 1) {
        $unleavable = 'yes';
    }

    if ((int)$group['pin_group'] === 1) {
        $pin_group = 'yes';
    }

    if (!empty($group['description'])) {
        $form['fields']->description["value"] = $group['description'];
    }

    if (!empty($group['meta_title']) && isset($form['fields']->meta_title)) {
        $form['fields']->meta_title["value"] = $group['meta_title'];
    }

    if (!empty($group['meta_description']) && isset($form['fields']->meta_description)) {
        $form['fields']->meta_description["value"] = $group['meta_description'];
    }

    if (!empty($group['slug']) && isset($form['fields']->slug)) {
        $form['fields']->slug["value"] = $group['slug'];
    }

    $form['fields']->group_name["value"] = $group['name'];

    if (isset($form['fields']->password_protect)) {
        $form['fields']->password_protect["value"] = $password_protect;
    }

    if (isset($form['fields']->auto_join_group)) {
        $form['fields']->auto_join_group["value"] = $auto_join_group;
    }

    if (isset($form['fields']->secret_group)) {
        $form['fields']->secret_group["value"] = $secret_group;
    }

    if (isset($form['fields']->unleavable)) {
        $form['fields']->unleavable["value"] = $unleavable;
    }

    if (isset($form['fields']->pin_group)) {
        $form['fields']->pin_group["value"] = $pin_group;
    }

    if (isset($form['fields']->who_all_can_send_messages)) {
        if ($group['who_all_can_send_messages'] !== 'all') {
            $form['fields']->who_all_can_send_messages["value"] = $group['who_all_can_send_messages'];
        }
    }

    if (isset($form['fields']->password)) {
        $form['fields']->password["title"] = Registry::load('strings')->new_password;

        if (!empty($group['password'])) {
            $form['fields']->password["class"] = 'field group_password';
            $form['fields']->confirm_password["class"] = 'field group_password';
        }
    }

}
?>