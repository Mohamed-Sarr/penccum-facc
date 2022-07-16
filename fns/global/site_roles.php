<?php

$permissions = Registry::load('permissions');

if (isset($data['role_id'])) {
    $data['site_role_id'] = $data['role_id'];
}

if (isset($data['site_role_id'])) {
    $site_role_id = filter_var($data['site_role_id'], FILTER_SANITIZE_NUMBER_INT);
    $global_variable = 'site_role_id_'.$site_role_id;

    if (!empty($site_role_id)) {
        if (isset($GLOBALS[$global_variable])) {
            $permissions = $GLOBALS[$global_variable];
        } else {
            $permissions = $GLOBALS[$global_variable] = extract_json(['file' => 'assets/cache/site_roles.cache', 'extract' => $site_role_id]);
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
                        break 2;
                    }

                } else {
                    $result = false;
                    if ($condition != 'OR') {
                        break 2;
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
