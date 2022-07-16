<?php

$permissions = array();

if (isset($data['group_role_id'])) {
    $group_role_id = filter_var($data['group_role_id'], FILTER_SANITIZE_NUMBER_INT);
    $global_variable = 'group_role_id_'.$group_role_id;

    if (!empty($group_role_id)) {
        if (isset($GLOBALS[$global_variable])) {
            $permissions = $GLOBALS[$global_variable];
        } else {
            $permissions = $GLOBALS[$global_variable] = extract_json(['file' => 'assets/cache/group_roles.cache', 'extract' => $group_role_id]);
        }
    }
}

if (isset($data['permissions'])) {
    $result = false;
    $condition = 'AND';

    if (isset($data['condition'])) {
        $condition = $data['condition'];
    }

    foreach ($data['permissions'] as $index => $check_permissions) {

        if (isset($permissions[$index])) {

            if (!is_array($check_permissions)) {
                $check_permissions = array($check_permissions);
            }

            foreach ($check_permissions as $permission) {
                if (in_array($permission, $permissions[$index])) {
                    $result = true;
                    if ($condition == 'OR') {
                        break;
                    }

                } else {
                    $result = false;
                    if ($condition != 'OR') {
                        break;
                    }
                }
            }
        } else {
            $result = false;

            if ($condition != 'OR') {
                break;
            }
        }
    }
} else if (isset($data['find'])) {
    if (!is_array($data['find'])) {
        if (isset($permissions[$data['find']])) {
            $result = $permissions[$data['find']];
        } else {
            $result = null;
        }
    } else {
        $result = array();
        foreach ($data['find'] as $find) {
            if (isset($permissions[$find])) {
                $result[$find] = $permissions[$find];
            } else {
                $result[$find] = null;
            }
        }
    }
}
