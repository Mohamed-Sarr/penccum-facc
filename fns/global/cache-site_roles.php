<?php
$siteroles = array();
$columns = [
    'site_roles.site_role_id', 'site_roles.permissions'
];
$roles = DB::connect()->select('site_roles', $columns);
foreach ($roles as $role) {
    $roleid = $role['site_role_id'];
    $siteroles[$roleid] = $role['permissions'];
}

$cache = json_encode($siteroles);
$cachefile = 'assets/cache/site_roles.cache';

if (file_exists($cachefile)) {
    unlink($cachefile);
}

$cachefile = fopen($cachefile, "w");
fwrite($cachefile, $cache);
fclose($cachefile);
$result = true;