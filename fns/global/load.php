<?php

function random_string($data = null) {
    $length = rand(8, 20);
    $result = null;
    $character_set = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

    if (isset($data['length']) && !empty($data['length'])) {
        $length = $data['length'];
    }

    if (isset($data['character_set']) && !empty($data['character_set'])) {
        $character_set = $data['character_set'];
    }

    $result = substr(str_shuffle($character_set), 1, $length);
    return $result;
}



function extract_json($data) {
    $result = array();

    if (isset($data['file']) && !empty($data['file'])) {
        $result = json_decode(file_get_contents($data['file']));
    } elseif (isset($data['json']) && !empty($data['json'])) {
        $result = json_decode($data['json']);
    }

    if (!empty($result)) {
        if (isset($data['extract']) && !empty($data['extract']) && isset($data['subkey']) && !empty($data['subkey'])) {
            $result = $result->$data['extract'];

            foreach ($result as $key => $val) {
                if ($key == $data['subkey']) {
                    foreach ($val as $subkey => $value) {
                        $result[$key][$subkey] = $value;
                    }
                } else {
                    foreach ($val as $subkey => $value) {
                        $result[$key][$subkey] = true;
                    }
                }
            }
        } elseif (isset($data['extract']) && !empty($data['extract'])) {
            $result = get_object_vars($result);
            $extract = $data['extract'];

            if (isset($result[$extract])) {
                $result = json_decode($result[$extract]);
                $result = get_object_vars($result);
            }
        }
    }

    return $result;
}

function rangeof_chars($string, $encoding = 'utf8') {
    $result = '';
    for ($i = 0, $len = mb_strlen($string); $i < $len; ++$i) {
        $l = mb_strtolower(mb_substr($string, $i, 1, $encoding));
        $u = mb_strtoupper(mb_substr($string, $i, 1, $encoding));
        if ($l != $u) {
            $result .= "[{$l}{$u}]";
        } else {
            $result .= mb_substr($string, $i, 1, $encoding);
        }
    }
    return $result;
}

function output($string, $todo = null) {
    if (empty($todo)) {
        $result = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    } elseif ($todo === 'skip') {
        $result = $string;
    } elseif ($todo === 'number') {
        $result = filter_var($string, FILTER_SANITIZE_NUMBER_INT);
    }
    echo $result;
}

function add_cookie($name, $value, $time = 0, $path = "/") {
    if (empty($time)) {
        $time = time() + (86400);
    }

    if (isset(Registry::load('config')->samesite_cookies) && Registry::load('config')->samesite_cookies !== 'default') {
        $samesite_cookies = Registry::load('config')->samesite_cookies;
        $httponly = false;

        if (isset(Registry::load('config')->http_only_cookies) && Registry::load('config')->http_only_cookies) {
            $httponly = true;
        }

        if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70300) {
            $cookie_options = array(
                'expires' => $time,
                'path' => $path,
                'secure' => true,
                'httponly' => $httponly,
                'samesite' => $samesite_cookies
            );

            if (isset(Registry::load('config')->cookie_domain) && !empty(Registry::load('config')->cookie_domain)) {
                $cookie_options['domain'] = Registry::load('config')->cookie_domain;
            }


            setcookie($name, $value, $cookie_options);
        } else {
            $samesite_cookies = "$path; SameSite=$samesite_cookies; Secure";
            setcookie($name, $value, $time, $samesite_cookies);
        }
    } else {
        setcookie($name, $value, $time, $path);
    }
}
function redirect($url = null, $external_link = false) {
    if (!$external_link && strpos($url, "http://") !== 0 && strpos($url, "https://") !== 0) {
        $url = Registry::load('config')->site_url.$url;
    }

    $url = htmlspecialchars(trim($url), ENT_QUOTES, 'UTF-8');

    if (headers_sent()) {
        echo '<script type="text/javascript"> document.location = "'.$url.'"; </script>';
        exit;
    } else {
        header('HTTP/1.1 307 Temporary Redirect');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        header('Pragma: no-cache');
        header("Location:".$url, true, 307);
        exit;
    }
}

function cleanOutput($value) {
    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function get_date($data = null) {
    $format = 'Y-m-d H:i:s';
    $result = $timeformat = $date = null;

    if (isset($data['format']) && !empty($data['format'])) {
        $format = $data['format'];
    }

    if (isset($data['auto_format']) && $data['auto_format']) {
        if (Registry::load('settings')->dateformat === 'mdy_format') {
            $format = "M-d-Y";
        } elseif (Registry::load('settings')->dateformat === 'ymd_format') {
            $format = "Y-M-d";
        } else {
            $format = "d-M-Y";
        }

        if (isset($data['include_time']) && $data['include_time']) {
            if (Registry::load('settings')->time_format === '24_format') {
                $timeformat = 'H:i';
            } else {
                $timeformat = 'h:i a';
            }
        } elseif (isset($data['time_alone']) && $data['time_alone']) {
            if (Registry::load('settings')->time_format === '24_format') {
                $format = 'H:i';
            } else {
                $format = 'h:i a';
            }
        }
    }

    if (isset($data['date']) && !empty($data['date'])) {
        $date = $data['date'];
    }

    if (isset($data['timezone']) && !empty($data['timezone'])) {
        $datetime = new DateTime($date);
        $timezone = new DateTimeZone($data['timezone']);
        $datetime->setTimezone($timezone);

        $result = $datetime->format($format);
        if (!empty($timeformat)) {
            $previous_result = $result;

            $result = array();
            $result['date'] = $previous_result;
            $result['time'] = $datetime->format($timeformat);

            if ($timeformat === 'h:i a') {
                $find_am_pm = ['am', 'pm'];
                $replace_am_pm = array();
                $replace_am_pm[] = Registry::load('strings')->time_am;
                $replace_am_pm[] = Registry::load('strings')->time_pm;

                $result['time'] = str_replace($find_am_pm, $replace_am_pm, $result['time']);
            }

            if (isset($data['compare_with_today']) && !empty($data['compare_with_today'])) {
                $today = new DateTime();
                $today->setTimezone($timezone);
                $yesterday = date($format, strtotime($today->format('Y-m-d H:i:s')) - (24 * 60 * 60));
                $today = $today->format($format);

                if ($result['date'] == $today) {
                    $result['date'] = 'today';
                } elseif ($result['date'] == $yesterday) {
                    $result['date'] = 'yesterday';
                }
            }
        }
    } else {
        if (isset($data['date']) && !empty($data['date'])) {
            $result = date($format, strtotime($date));
            if (!empty($timeformat)) {
                $previous_result = $result;

                $result = array();
                $result['date'] = $previous_result;
                $result['time'] = date($timeformat, strtotime($date));
            }
        } else {
            $result = date($format);
            if (!empty($timeformat)) {
                $previous_result = $result;

                $result = array();
                $result['date'] = $previous_result;
                $result['time'] = date($timeformat);
            }
        }
    }

    return $result;
}

function get_data($get = 'post') {
    if ($get === 'get') {
        return $_GET;
    } elseif ($get === 'file') {
        return $_FILES;
    } elseif ($get === 'request') {
        return $_REQUEST;
    } else {
        return $_POST;
    }
}

function abbreviateNumber($num) {
    if ($num >= 0 && $num < 1000) {
        $format = floor($num);
        $suffix = '';
    } elseif ($num >= 1000 && $num < 1000000) {
        $format = floor($num / 1000);
        $suffix = 'k';
    } elseif ($num >= 1000000 && $num < 1000000000) {
        $format = floor($num / 1000000);
        $suffix = 'm';
    } elseif ($num >= 1000000000 && $num < 1000000000000) {
        $format = floor($num / 1000000000);
        $suffix = 'b';
    } elseif ($num >= 1000000000000) {
        $format = floor($num / 1000000000000);
        $suffix = 't';
    }

    return !empty($format . $suffix) ? $format . $suffix : 0;
}


function is_ssl() {
    if (isset($_SERVER['HTTP_CF_VISITOR'])) {
        $cf_visitor = json_decode($_SERVER['HTTP_CF_VISITOR']);

        if (isset($cf_visitor->scheme) && $cf_visitor->scheme == 'https') {
            return true;
        }
    } elseif (isset($_SERVER['HTTPS'])) {
        if ('on' === strtolower($_SERVER['HTTPS'])) {
            return true;
        }

        if ('1' == $_SERVER['HTTPS']) {
            return true;
        }
    } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
        return true;
    }
    return false;
}

function get_url($todo = null) {
    $result = null;
    $non_latin = true;
    $path = $navigation_scope = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));

    if ($path == '/') {
        $path = '';
    }

    if (!empty($path)) {
        $path = substr($_SERVER["REQUEST_URI"], strlen($path)+1);
    } else {
        $path = substr($_SERVER["REQUEST_URI"], 1);
    }

    if (isset($todo['remove'])) {
        $result = substr(strstr($path, $todo['remove']), strlen($todo['remove']));
    } elseif (isset($todo['page']) && $todo['page']) {
        $result = preg_split('~[^a-z0-9.\\_\\-]~i', $path)[0];
    } elseif (isset($todo['path']) && $todo['path']) {
        $result = $path;
    } else {
        $result = new stdClass();
        if (isset(Registry::load('config')->force_url) && Registry::load('config')->force_url) {
            $result->site_url = Registry::load('config')->site_url;
        } else {
            if (is_ssl()) {
                $result->scheme = 'https://';
            } else {
                $result->scheme = 'http://';
            }

            $result->site_url = $result->scheme.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $result->url_path = $path;

            $result->navigation_scope = $navigation_scope;

            if ($non_latin) {
                $url_path = parse_url($path);

                if (isset($url_path['path'])) {
                    $url_path = $url_path['path'];
                } else {
                    $url_path = '';
                }

                $result->current_page = preg_split('/\//', $url_path)[0];
            } else {
                $result->current_page = preg_split('~[^a-z0-9.\\_\\-]~i', $path)[0];
            }

            if (!empty($path)) {
                $result->site_url = substr($result->site_url, 0, -strlen($path));
            }

            if (!filter_var($result->site_url, FILTER_VALIDATE_URL)) {
                $result->site_url = null;
            }
        }
    }

    return $result;
}

function get_image($data) {
    include('fns/global/get_image.php');
    return $result;
}

function language($data) {
    include('fns/global/languages.php');
    return $result;
}

function cache($data) {
    include('fns/global/cache.php');
    return $result;
}

function update_online_statuses() {
    include('fns/global/update_online_statuses.php');
}

function role($data) {
    if (isset($data['group_role_id'])) {
        include('fns/global/group_roles.php');
    } else {
        include('fns/global/site_roles.php');
    }
    return $result;
}