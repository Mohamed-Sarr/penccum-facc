<?php
$grouproles = array();
$columns = [
    'group_roles.group_role_id', 'group_roles.permissions'
];
$roles = DB::connect()->select('group_roles', $columns);
foreach ($roles as $role) {
    $roleid = $role['group_role_id'];
    $grouproles[$roleid] = $role['permissions'];
}

$cache = json_encode($grouproles);
$cachefile = 'assets/cache/group_roles.cache';

if (file_exists($cachefile)) {
    unlink($cachefile);
}

$cachefile = fopen($cachefile, "w");
fwrite($cachefile, $cache);
fclose($cachefile);
$result = true;