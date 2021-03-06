<?php

function sanitize_slug($slug, $strict = false) {
    return sanitize_username($slug, $strict = false);
}


function sanitize_filename($filename) {
    $filename = preg_replace('~[<>:"/\\|?*]|[\x00-\x1F]|[\x7F\xA0\xAD]|[#\[\]@!$&\'()+,;=]|[{}^\~`]~x', '-', $filename);
    $filename = ltrim($filename, '.-');
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');
    return $filename;
}

function slug_exists($slug) {

    $reserved_slugs = ['group'];

    if (isset(Registry::load('settings')->disallowed_slugs) && !empty(Registry::load('settings')->disallowed_slugs)) {
        $disallowed_slugs = Registry::load('settings')->disallowed_slugs;
        foreach ($disallowed_slugs as $disallowed_slug) {
            $reserved_slugs[] = $disallowed_slug;
        }
    }

    $query = 'SELECT ';
    $query .= 'EXISTS (SELECT <user_id> FROM <site_users> WHERE <username> = :findslug) OR ';
    $query .= 'EXISTS (SELECT <page_id> FROM <custom_pages> WHERE <slug> = :findslug) OR ';
    $query .= 'EXISTS (SELECT <group_id> FROM <groups> WHERE <slug> = :findslug) AS result;';
    $slug_exists = DB::connect()->query($query, ['findslug' => $slug])->fetchAll();

    $file_exists = 'pages/'.$slug.'.php';

    if (in_array($slug, $reserved_slugs) || $slug_exists[0]['result'] || $slug_exists[0]['result'] === '1' || file_exists($file_exists) || file_exists($slug)) {
        return true;
    } else {
        return false;
    }
}

function sanitize_array($array) {
    function filter(&$value) {
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    array_walk_recursive($array, "filter");
    return $array;
}

function username_exists($username) {
    return slug_exists($username);
}

function isJson($string) {
    try {
        json_decode($string);
    }catch(TypeError $e) {
        return false;
    }
    return (json_last_error() == JSON_ERROR_NONE);
}

function isImage($img) {
    return (bool)getimagesize($img);
}

function sanitize_username($username, $strict = false) {
    $username = strip_all_tags($username);
    $username = remove_accents($username);
    $username = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $username);
    $username = preg_replace('/&.+?;/', '', $username);

    if ($strict) {
        $username = preg_replace('|[^a-z0-9 _.\-]|i', '', $username);
    }

    $username = str_replace(array('\'', '"', ',', '@', ';', '(', ')', '[', ']', '<', '>', '{', '}', '?', '&'), '', $username);
    $username = trim($username);
    $username = preg_replace('|\s+|', ' ', $username);
    $username = preg_replace('/\s+/', '-', $username);
    return $username;
}

function validate_date($date, $format = 'Y-m-d H:i:s') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function strip_all_tags($string, $remove_breaks = false) {
    $string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
    $string = strip_tags($string);

    if ($remove_breaks) {
        $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
    }

    return trim($string);
}

function reset_mbstring_encoding() {
    mbstring_binary_safe_encoding(true);
}

function mbstring_binary_safe_encoding($reset = false) {
    static $encodings = array();
    static $overloaded = null;

    if (is_null($overloaded)) {
        if (function_exists('mb_internal_encoding') && ((int) ini_get('mbstring.func_overload') & 2)) {
            $overloaded = true;
        } else {
            $overloaded = false;
        }
    }

    if (false === $overloaded) {
        return;
    }

    if (! $reset) {
        $encoding = mb_internal_encoding();
        array_push($encodings, $encoding);
        mb_internal_encoding('ISO-8859-1');
    }

    if ($reset && $encodings) {
        $encoding = array_pop($encodings);
        mb_internal_encoding($encoding);
    }
}


function seems_utf8($str) {
    mbstring_binary_safe_encoding();
    $length = strlen($str);
    reset_mbstring_encoding();
    for ($i = 0; $i < $length; $i++) {
        $c = ord($str[$i]);
        if ($c < 0x80) {
            $n = 0;
        } elseif (($c & 0xE0) == 0xC0) {
            $n = 1;
        } elseif (($c & 0xF0) == 0xE0) {
            $n = 2;
        } elseif (($c & 0xF8) == 0xF0) {
            $n = 3;
        } elseif (($c & 0xFC) == 0xF8) {
            $n = 4;
        } elseif (($c & 0xFE) == 0xFC) {
            $n = 5;
        } else {
            return false;
        }
        for ($j = 0; $j < $n; $j++) {
            // n bytes matching 10bbbbbb follow ?
            if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80)) {
                return false;
            }
        }
    }
    return true;
}

function remove_accents($string) {
    if (! preg_match('/[\x80-\xff]/', $string)) {
        return $string;
    }

    if (seems_utf8($string)) {
        $chars = array(
            '??' => 'a',
            '??' => 'o',
            '??' => 'A',
            '??' => 'A',
            '??' => 'A',
            '??' => 'A',
            '??' => 'A',
            '??' => 'A',
            '??' => 'AE',
            '??' => 'C',
            '??' => 'E',
            '??' => 'E',
            '??' => 'E',
            '??' => 'E',
            '??' => 'I',
            '??' => 'I',
            '??' => 'I',
            '??' => 'I',
            '??' => 'D',
            '??' => 'N',
            '??' => 'O',
            '??' => 'O',
            '??' => 'O',
            '??' => 'O',
            '??' => 'O',
            '??' => 'U',
            '??' => 'U',
            '??' => 'U',
            '??' => 'U',
            '??' => 'Y',
            '??' => 'TH',
            '??' => 's',
            '??' => 'a',
            '??' => 'a',
            '??' => 'a',
            '??' => 'a',
            '??' => 'a',
            '??' => 'a',
            '??' => 'ae',
            '??' => 'c',
            '??' => 'e',
            '??' => 'e',
            '??' => 'e',
            '??' => 'e',
            '??' => 'i',
            '??' => 'i',
            '??' => 'i',
            '??' => 'i',
            '??' => 'd',
            '??' => 'n',
            '??' => 'o',
            '??' => 'o',
            '??' => 'o',
            '??' => 'o',
            '??' => 'o',
            '??' => 'o',
            '??' => 'u',
            '??' => 'u',
            '??' => 'u',
            '??' => 'u',
            '??' => 'y',
            '??' => 'th',
            '??' => 'y',
            '??' => 'O',
            '??' => 'A',
            '??' => 'a',
            '??' => 'A',
            '??' => 'a',
            '??' => 'A',
            '??' => 'a',
            '??' => 'C',
            '??' => 'c',
            '??' => 'C',
            '??' => 'c',
            '??' => 'C',
            '??' => 'c',
            '??' => 'C',
            '??' => 'c',
            '??' => 'D',
            '??' => 'd',
            '??' => 'D',
            '??' => 'd',
            '??' => 'E',
            '??' => 'e',
            '??' => 'E',
            '??' => 'e',
            '??' => 'E',
            '??' => 'e',
            '??' => 'E',
            '??' => 'e',
            '??' => 'E',
            '??' => 'e',
            '??' => 'G',
            '??' => 'g',
            '??' => 'G',
            '??' => 'g',
            '??' => 'G',
            '??' => 'g',
            '??' => 'G',
            '??' => 'g',
            '??' => 'H',
            '??' => 'h',
            '??' => 'H',
            '??' => 'h',
            '??' => 'I',
            '??' => 'i',
            '??' => 'I',
            '??' => 'i',
            '??' => 'I',
            '??' => 'i',
            '??' => 'I',
            '??' => 'i',
            '??' => 'I',
            '??' => 'i',
            '??' => 'IJ',
            '??' => 'ij',
            '??' => 'J',
            '??' => 'j',
            '??' => 'K',
            '??' => 'k',
            '??' => 'k',
            '??' => 'L',
            '??' => 'l',
            '??' => 'L',
            '??' => 'l',
            '??' => 'L',
            '??' => 'l',
            '??' => 'L',
            '??' => 'l',
            '??' => 'L',
            '??' => 'l',
            '??' => 'N',
            '??' => 'n',
            '??' => 'N',
            '??' => 'n',
            '??' => 'N',
            '??' => 'n',
            '??' => 'n',
            '??' => 'N',
            '??' => 'n',
            '??' => 'O',
            '??' => 'o',
            '??' => 'O',
            '??' => 'o',
            '??' => 'O',
            '??' => 'o',
            '??' => 'OE',
            '??' => 'oe',
            '??' => 'R',
            '??' => 'r',
            '??' => 'R',
            '??' => 'r',
            '??' => 'R',
            '??' => 'r',
            '??' => 'S',
            '??' => 's',
            '??' => 'S',
            '??' => 's',
            '??' => 'S',
            '??' => 's',
            '??' => 'S',
            '??' => 's',
            '??' => 'T',
            '??' => 't',
            '??' => 'T',
            '??' => 't',
            '??' => 'T',
            '??' => 't',
            '??' => 'U',
            '??' => 'u',
            '??' => 'U',
            '??' => 'u',
            '??' => 'U',
            '??' => 'u',
            '??' => 'U',
            '??' => 'u',
            '??' => 'U',
            '??' => 'u',
            '??' => 'U',
            '??' => 'u',
            '??' => 'W',
            '??' => 'w',
            '??' => 'Y',
            '??' => 'y',
            '??' => 'Y',
            '??' => 'Z',
            '??' => 'z',
            '??' => 'Z',
            '??' => 'z',
            '??' => 'Z',
            '??' => 'z',
            '??' => 's',
            '??' => 'S',
            '??' => 's',
            '??' => 'T',
            '??' => 't',
            '???' => 'E',
            '??' => '',
            '??' => 'O',
            '??' => 'o',
            '??' => 'U',
            '??' => 'u',
            '???' => 'A',
            '???' => 'a',
            '???' => 'A',
            '???' => 'a',
            '???' => 'E',
            '???' => 'e',
            '???' => 'O',
            '???' => 'o',
            '???' => 'O',
            '???' => 'o',
            '???' => 'U',
            '???' => 'u',
            '???' => 'Y',
            '???' => 'y',
            '???' => 'A',
            '???' => 'a',
            '???' => 'A',
            '???' => 'a',
            '???' => 'A',
            '???' => 'a',
            '???' => 'E',
            '???' => 'e',
            '???' => 'E',
            '???' => 'e',
            '???' => 'I',
            '???' => 'i',
            '???' => 'O',
            '???' => 'o',
            '???' => 'O',
            '???' => 'o',
            '???' => 'O',
            '???' => 'o',
            '???' => 'U',
            '???' => 'u',
            '???' => 'U',
            '???' => 'u',
            '???' => 'Y',
            '???' => 'y',
            '???' => 'A',
            '???' => 'a',
            '???' => 'A',
            '???' => 'a',
            '???' => 'E',
            '???' => 'e',
            '???' => 'E',
            '???' => 'e',
            '???' => 'O',
            '???' => 'o',
            '???' => 'O',
            '???' => 'o',
            '???' => 'U',
            '???' => 'u',
            '???' => 'Y',
            '???' => 'y',
            '???' => 'A',
            '???' => 'a',
            '???' => 'A',
            '???' => 'a',
            '???' => 'E',
            '???' => 'e',
            '???' => 'O',
            '???' => 'o',
            '???' => 'O',
            '???' => 'o',
            '???' => 'U',
            '???' => 'u',
            '???' => 'A',
            '???' => 'a',
            '???' => 'A',
            '???' => 'a',
            '???' => 'A',
            '???' => 'a',
            '???' => 'E',
            '???' => 'e',
            '???' => 'E',
            '???' => 'e',
            '???' => 'I',
            '???' => 'i',
            '???' => 'O',
            '???' => 'o',
            '???' => 'O',
            '???' => 'o',
            '???' => 'O',
            '???' => 'o',
            '???' => 'U',
            '???' => 'u',
            '???' => 'U',
            '???' => 'u',
            '???' => 'Y',
            '???' => 'y',
            '??' => 'a',
            '??' => 'U',
            '??' => 'u',
            '??' => 'U',
            '??' => 'u',
            '??' => 'A',
            '??' => 'a',
            '??' => 'I',
            '??' => 'i',
            '??' => 'O',
            '??' => 'o',
            '??' => 'U',
            '??' => 'u',
            '??' => 'U',
            '??' => 'u',
            '??' => 'U',
            '??' => 'u',
        );

        $locale = 'en_US';

        if (in_array($locale, array('de_DE', 'de_DE_formal', 'de_CH', 'de_CH_informal', 'de_AT'), true)) {
            $chars['??'] = 'Ae';
            $chars['??'] = 'ae';
            $chars['??'] = 'Oe';
            $chars['??'] = 'oe';
            $chars['??'] = 'Ue';
            $chars['??'] = 'ue';
            $chars['??'] = 'ss';
        } elseif ('da_DK' === $locale) {
            $chars['??'] = 'Ae';
            $chars['??'] = 'ae';
            $chars['??'] = 'Oe';
            $chars['??'] = 'oe';
            $chars['??'] = 'Aa';
            $chars['??'] = 'aa';
        } elseif ('ca' === $locale) {
            $chars['l??l'] = 'll';
        } elseif ('sr_RS' === $locale || 'bs_BA' === $locale) {
            $chars['??'] = 'DJ';
            $chars['??'] = 'dj';
        }

        $string = strtr($string, $chars);
    } else {
        $chars = array();
        $chars['in'] = "\x80\x83\x8a\x8e\x9a\x9e"
        . "\x9f\xa2\xa5\xb5\xc0\xc1\xc2"
        . "\xc3\xc4\xc5\xc7\xc8\xc9\xca"
        . "\xcb\xcc\xcd\xce\xcf\xd1\xd2"
        . "\xd3\xd4\xd5\xd6\xd8\xd9\xda"
        . "\xdb\xdc\xdd\xe0\xe1\xe2\xe3"
        . "\xe4\xe5\xe7\xe8\xe9\xea\xeb"
        . "\xec\xed\xee\xef\xf1\xf2\xf3"
        . "\xf4\xf5\xf6\xf8\xf9\xfa\xfb"
        . "\xfc\xfd\xff";

        $chars['out'] = 'EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy';

        $string = strtr($string, $chars['in'], $chars['out']);
        $double_chars = array();
        $double_chars['in'] = array("\x8c", "\x9c", "\xc6", "\xd0", "\xde", "\xdf", "\xe6", "\xf0", "\xfe");
        $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
        $string = str_replace($double_chars['in'], $double_chars['out'], $string);
    }

    return $string;
}

?>