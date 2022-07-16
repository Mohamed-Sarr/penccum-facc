<?php

error_reporting(0);

include 'fns/filters/load.php';
include 'fns/sql/Medoo.php';
use Medoo\Medoo;

$result = array();
$result['success'] = false;
$result['error_message'] = 'The input value is invalid';
$result['error_variables'] = [];
$noerror = true;

if (!isset($data["purchase_code"]) || empty($data["purchase_code"])) {
    $noerror = false;
    $result['error_message'] = 'Enter your Envato Purchase code';
} else if (!isset($data["database_hostname"]) || empty($data["database_hostname"])) {
    $noerror = false;
    $result['error_message'] = 'Invalid Database Hostname';
} else if (!isset($data["database_name"]) || empty($data["database_name"])) {
    $noerror = false;
    $result['error_message'] = 'Invalid Database Name';
} else if (!isset($data["database_username"]) || empty($data["database_username"])) {
    $noerror = false;
    $result['error_message'] = 'Invalid Database Username';
} else if (!isset($data["email_address"]) || empty($data["email_address"])) {
    $noerror = false;
    $result['error_message'] = 'Invalid Email Address';
} else if (!filter_var($data["email_address"], FILTER_VALIDATE_EMAIL)) {
    $noerror = false;
    $result['error_message'] = 'Invalid Email Address';
} else if (!isset($data["username"]) || empty($data["username"])) {
    $noerror = false;
    $result['error_message'] = 'Type in a preferred Username';
} else if (!isset($data["password"]) || empty($data["password"])) {
    $noerror = false;
    $result['error_message'] = 'Type in a preferred Password';
}

if (!isset($data["database_password"])) {
    $data["database_password"] = '';
}

if (isset($data["database_type"]) && $data["database_type"] === 'mariadb') {
    $data["database_type"] = 'mariadb';
} else {
    $data["database_type"] = 'mysql';
}

if (!isset($data["database_port"]) || empty($data["database_port"])) {
    $data["database_port"] = '3306';
}

if ($noerror) {

    $data['purchase_code'] = trim($data['purchase_code']);

    if (!preg_match("/^(\w{8})-((\w{4})-){3}(\w{12})$/", $data['purchase_code'])) {
        $noerror = false;
        $result['error_message'] = 'Purchase code is Invalid';
    } else {

        try {
            $db_instance = new Medoo([
                'type' => $data["database_type"],
                'host' => $data["database_hostname"],
                'database' => $data["database_name"],
                'username' => $data["database_username"],
                'password' => $data["database_password"],
                'port' => $data["database_port"],
                'error' => PDO::ERRMODE_SILENT,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
            ]);
        } catch (PDOException $exception) {
            $noerror = false;
            $result['error_message'] = 'Invalid Database Credentials';
        }

        if ($noerror) {
            $config_file = 'include/config.php';
            if (is_writable($config_file)) {
                $file_contents = file_get_contents($config_file);
                $file_contents = preg_replace("/'type' => '([^']+(?='))'/", "'type' => '".$data['database_type']."'", $file_contents);
                $file_contents = preg_replace("/'host' => '([^']+(?='))'/", "'host' => '".$data['database_hostname']."'", $file_contents);
                $file_contents = preg_replace("/'database' => '([^']+(?='))'/", "'database' => '".$data['database_name']."'", $file_contents);
                $file_contents = preg_replace("/'username' => '([^']+(?='))'/", "'username' => '".$data['database_username']."'", $file_contents);
                $file_contents = preg_replace("/'password' => '([^']+(?='))'/", "'password' => '".$data['database_password']."'", $file_contents);
                $file_contents = preg_replace("/'port' => '([^']+(?='))'/", "'port' => '".$data['database_port']."'", $file_contents);
                file_put_contents($config_file, $file_contents);
            } else {
                $noerror = false;
                $result['error_message'] = 'Permission Denied : Unable to write to include/config.php file';
            }
        }

        if ($noerror) {
            include('layouts/installer/item_support_register.php');
            $import_sql = file_get_contents('layouts/installer/installer.sql');

            try {
                $db_instance->query($import_sql);
            } catch (PDOException $exception) {
                $noerror = false;
                $result['error_message'] = 'Database Import Failed';
            }
        }

        if ($noerror) {

            $data["username"] = sanitize_username($data["username"]);

            if (empty($data["username"])) {
                $data["username"] = 'admin';
            }

            try {

                $db_instance = new Medoo([
                    'type' => $data["database_type"],
                    'host' => $data["database_hostname"],
                    'database' => $data["database_name"],
                    'username' => $data["database_username"],
                    'password' => $data["database_password"],
                    'port' => $data["database_port"],
                    'error' => PDO::ERRMODE_SILENT,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_general_ci',
                ]);

                $update_data = array();
                $update_data["email_address"] = $data['email_address'];
                $update_data["username"] = $data['username'];
                $update_data["password"] = password_hash($data['password'], PASSWORD_BCRYPT);
                $update_data["encrypt_type"] = 'php_password_hash';
                $update_data["salt"] = '';
                $db_instance->update("gr_site_users", $update_data, ["OR" => ["username" => "admin", "user_id" => 1]]);

            } catch (PDOException $exception) {
                $data["username"] = 'admin';
                $data["password"] = 'pass';
            }

            $api_secret_key = random_string('15');
            $db_instance->update("gr_settings", ['value' => $api_secret_key], ["setting" => "api_secret_key"]);

            if (file_exists('layouts/installer/cache_rebuild.php')) {
                include 'layouts/installer/cache_rebuild.php';
            }

            if (file_exists('pages/installer.php')) {
                $rename_to = 'pages/installer_'.strtolower(random_string('10')); '.php';
                rename('pages/installer.php', $rename_to);
            }

            if (file_exists('htaccess.backup')) {
                unlink('htaccess.backup');
            }

            $result = array();
            $result['success'] = true;
            $result['alert_message'] = "Installation Complete. \n\nYour Login Details:\n";
            $result['alert_message'] .= "Username : ".$data["username"]."\n";
            $result['alert_message'] .= "Password : ".strip_tags($data["password"])."\n";

        }



    }


}

$result = json_encode($result);
echo $result;
?>