<?php

require 'fns/hybridauth/autoload.php';

use Hybridauth\Exception\Exception;
use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;
use Hybridauth\Storage\Session;

$debug = true;

$storage = new Session();
$error = false;
$social_login_provider_id = $identifier = null;
$provider_config = array();

$current_web_address = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$parse_url = parse_url($current_web_address);

if (isset($parse_url['query'])) {
    parse_str($parse_url['query'], $get_variables);

    if (!isset($_GET['code']) && isset($get_variables['code'])) {
        $_GET['code'] = $get_variables['code'];
    }

    if (!isset($_GET['state']) && isset($get_variables['state'])) {
        $_GET['state'] = $get_variables['code'];
    }
}

if ($social_login_provider_id = $storage->get('social_login_provider_id')) {
    $identifier = $storage->get('provider');
}

if (isset($_GET['social_login_provider_id'])) {

    $_GET['social_login_provider_id'] = filter_var($_GET['social_login_provider_id'], FILTER_SANITIZE_NUMBER_INT);

    if (!empty($_GET['social_login_provider_id'])) {
        $social_login_provider_id = $_GET['social_login_provider_id'];
    }
}

if (!empty($social_login_provider_id)) {
    $columns = $where = null;
    $columns = [
        'social_login_providers.social_login_provider_id', 'social_login_providers.identity_provider', 'social_login_providers.app_id',
        'social_login_providers.app_key', 'social_login_providers.secret_key', 'social_login_providers.create_user'
    ];

    $where["social_login_providers.disabled"] = 0;
    $where["social_login_providers.social_login_provider_id"] = $social_login_provider_id;

    $login_provider = DB::connect()->select('social_login_providers', $columns, $where);

    if (isset($login_provider[0])) {

        $login_provider = $login_provider[0];
        $identity_provider = $login_provider['identity_provider'];

        $provider_config['callback'] = Registry::load('config')->site_url.'entry/social_login/';
        $provider_config['providers'][$identity_provider]['enabled'] = true;

        if ($identity_provider === 'Twitter' || $identity_provider === 'Tumblr') {
            $provider_config['providers'][$identity_provider]['keys']['key'] = $login_provider['app_key'];
        } else {
            $provider_config['providers'][$identity_provider]['keys']['id'] = $login_provider['app_id'];
        }

        $provider_config['providers'][$identity_provider]['keys']['secret'] = $login_provider['secret_key'];

    }
} else {
    ?>

    <script>
        if (window.opener !== null && window.opener.closePopupWindow) {
            window.opener.location.reload();
            window.opener.closePopupWindow();
        } else {
            window.location = '<?php echo Registry::load('config')->site_url; ?>entry/';
        }
    </script>

    <?php
    exit;
}

try {

    $hybridauth = new Hybridauth($provider_config);

    if (isset($login_provider['social_login_provider_id'])) {
        $storage->set('provider', $login_provider['identity_provider']);
        $storage->set('social_login_provider_id', $login_provider['social_login_provider_id']);
    }

    if ($provider = $storage->get('provider')) {

        include 'fns/filters/load.php';
        include 'fns/add/load.php';

        $hybridauth->authenticate($provider);

        $storage->set('provider', null);
        $storage->set('social_login_provider_id', null);

        $adapter = $hybridauth->getAdapter($provider);
        $userProfile = $adapter->getUserProfile();
        $accessToken = $adapter->getAccessToken();
        $account_not_exists = false;

        $user = [
            'add' => 'site_users',
            'full_name' => $userProfile->displayName,
            'email_address' => $userProfile->emailVerified,
            'password' => random_string(['length' => 6]),
            'avatarURL' => strtok($userProfile->photoURL, '?'),
            'signup_page' => true,
            'return' => true
        ];

        if (empty($userProfile->displayName)) {
            $user['full_name'] = $userProfile->firstName;
        }

        if (empty($userProfile->emailVerified)) {
            $user['email_address'] = $userProfile->email;
        }

        if (empty($userProfile->email)) {
            $user['email_address'] = sanitize_username($userProfile->identifier).'@'.$login_provider['identity_provider'].'.sociallogin';
        }

        $email_exists = DB::connect()->select('site_users', 'site_users.user_id', ['site_users.email_address' => $user['email_address']]);

        if (isset($email_exists[0])) {
            $login_session = [
                'add' => 'login_session',
                'user' => $user['email_address'],
                'return' => true
            ];
            add($login_session, ['force_request' => true]);
        } else if (isset($login_provider['create_user']) && !empty($login_provider['create_user'])) {
            $user['username'] = sanitize_username($user['full_name']);

            if (empty($user['username'])) {
                $user['username'] = 'user_'.strtotime("now").'_'.random_string(['length' => 5]);
            }

            if (username_exists($user['username'])) {
                $user['username'] = $user['username'].'_'.random_string(['length' => 5]);
            }

            add($user, ['force_request' => true, 'exclude_filters_function' => true, 'auto_login' => true, 'social_login_provider_id' => $login_provider['social_login_provider_id']]);
        } else {
            $account_not_exists = true;
        }

        if (isset($adapter)) {
            $adapter->disconnect();
        }

        ?>
        <script>
            <?php
            if ($account_not_exists) {
                ?>
                alert('<?php echo Registry::load('strings')->account_not_found; ?>');
                <?php
            }
            ?>
            if (window.opener !== null && window.opener.closePopupWindow) {
                window.opener.location.reload();
                window.opener.closePopupWindow();
            } else {
                window.location = '<?php echo Registry::load('config')->site_url; ?>entry/';
            }
        </script>
        <?php

    }

} catch (Exception $e) {

    if (isset($adapter)) {
        $adapter->disconnect();
    }

    if ($debug) {
        echo $e->getMessage();
    } else {
        ?>
        <script>
            if (window.opener !== null && window.opener.closePopupWindow) {
                window.opener.location.reload();
                window.opener.closePopupWindow();
            } else {
                window.location = '<?php echo Registry::load('config')->site_url; ?>entry/';
            }
        </script>
        <?php
    }
}

exit;