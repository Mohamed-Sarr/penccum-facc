<?php

$data = get_data();

if (isset($data["install"]) && $data["install"] == 'install') {
    include('layouts/installer/installer.php');
    exit;
}

?>