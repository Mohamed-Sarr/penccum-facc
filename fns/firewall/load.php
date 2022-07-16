<?php

class Firewall {
    private $ipBlocks = array();
    private $ipAllows = array();
    private $configScript = '';
    const GET = 2;
    const POST = 4;
    const COOKIE = 8;
    const SESSION = 16;
    const FILES = 32;
    const SERVER = 64;
    public function __construct($configscript = false) {
        $this->configScript = $configscript;

        if ($this->configScript)$this->loadSettingsFromFile();

    }
    public function printSettings() {
        echo "<h1>Debug firewall</h1>";
        echo "<h2>Hi, your ip address is: ", $this->getUserIP(), "</h2>";
        echo "<h2>Ips that are currently blocked:</h2><ul>";
        if (count($this->ipAllows) > 0) {
            echo "<li>ALL IPS EXCEPT FROM Allowed IP's.....</li>";
        } else {
            foreach ($this->ipBlocks as $it) {
                echo "<li>$it</li>";
            }
        }
    }
    private function loadSettingsFromFile() {
        if (!file_exists($this->configScript))throw new Exception('Could not find config script provided: '.$this->configScript);

        $xml = simplexml_load_file($this->configScript);

        if (isset($xml->IP->block) && count($xml->IP->block) > 0) {
            foreach ($xml->IP->block->item as $it) {
                $this->blockIP((String)$it);
            }
        }
        if (isset($xml->IP->allow) && count($xml->IP->allow) > 0) {
            foreach ($xml->IP->allow->item as $it) {
                $this->allowIP((String)$it);
            }
        }
    }
    public function blockIP($ip) {
        if (is_array($ip)) {
            $this->ipBlocks = array_merge($this->ipBlocks, $ip);
        } else {
            $this->ipBlocks[] = $ip;
        }
    }
    public function allowIP($ip) {
        if (is_array($ip)) {
            $this->ipAllows = array_merge($this->ipAllows, $ip);
        } else {
            $this->ipAllows[] = $ip;
        }
    }
    public function run() {
        $clientip = $this->getUserIP();


        $ipblock = false;
        $ipallow = true;

        $iptriggerblock = false;
        if (count($this->ipBlocks) > 0) {

            foreach ($this->ipBlocks as $block) {
                $bit = explode('/', $block);
                if (!isset($bit[1]))$bit[1] = '255.255.255.255';
                if ($this->ipCompare($clientip, $bit[0], $bit[1])) {
                    $ipblock = true;
                    $iptriggerblock = true;
                    continue;
                } else {
                    if (!$iptriggerblock)$ipblock = false;
                }
                unset($bit);
            }
        }

        $iptriggerallow = false;

        if (count($this->ipAllows) > 0) {

            foreach ($this->ipAllows as $block) {
                $bit = explode('/', $block);
                if (!isset($bit[1]))$bit[1] = '255.255.255.255';
                if ($this->ipCompare($clientip, $bit[0], $bit[1])) {
                    $ipallow = true;
                    $iptriggerallow = true;
                    continue;
                } else {
                    if (!$iptriggerallow)$ipallow = false;
                }
                unset($bit);
            }
        }

        if ($ipblock)throw new Exception('Client blocked from php web application. Reason: IP Blocked.', 1);
        if (!$ipallow)throw new Exception('Client blocked from php web application. Reason: IP not in allow list.', 2);
    }
    public function getUserIP() {
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'];
        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }
        if ($ip == '') {
            $ip = '127.0.0.1';
        } else if ($ip == '::1') {
            $ip = '127.0.0.1';
        }
        return $ip;
    }
    private function ipCompare ($ip1, $ip2, $mask) {
        $masked1 = ip2long($ip1) & ip2long($mask);
        $masked2 = ip2long($ip2) & ip2long($mask);
        if ($masked1 == $masked2) return true;
        else return false;
    }
    public function getUserAgent($agent = 0) {

        if (empty($agent)) {
            $agent = $_SERVER ['HTTP_USER_AGENT'];
        }
        $result = array();
        $result['browser'] = $result['version'] = $result['platform'] = '?';
        $result['user_agent'] = $agent;
        $regexfound = 'Chrome';

        $browser_array = array('/msie/i' => 'Internet Explorer',
            '/Mobile/i' => 'Handheld Browser',
            '/Firefox/i' => 'Firefox',
            '/Safari/i' => 'Safari',
            '/Chrome/i' => 'Chrome',
            '/Opera/i' => 'Opera',
            '/Edge/i' => 'Edge',
            '/Edg/i' => 'Edge',
            '/Opr/i' => 'Opera',
            '/Netscape/i' => 'Netscape',
            '/Maxthon/i' => 'Maxthon',
            '/Konqueror/i' => 'Konqueror');

        foreach ($browser_array as $regex => $value) {
            if (preg_match($regex, $agent, $output)) {
                $result['browser'] = $value;
                $regexfound = str_replace('/', '', str_replace('/i', '', $regex));
            }
        }

        $platform_array = array(
            '/windows nt 10/i' => 'Windows 10',
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i' => 'Windows XP',
            '/windows xp/i' => 'Windows XP',
            '/windows nt 5.0/i' => 'Windows 2000',
            '/windows me/i' => 'Windows ME',
            '/win98/i' => 'Windows 98',
            '/win95/i' => 'Windows 95',
            '/win16/i' => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile'
        );

        foreach ($platform_array as $regex => $value) {
            if (preg_match($regex, $agent)) {
                $result['platform'] = $value;
            }
        }

        $known = array('Version', $regexfound, 'other');
        $pattern = '#(?<browser>' . join('|', $known).')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $agent, $matches)) {}

        $i = count($matches['browser']);
        if ($i != 1) {
            if (strripos($agent, "Version") < strripos($agent, $result['browser'])) {
                if (isset($matches['version'][0])) {
                    $result['version'] = $matches['version'][0];
                }
            } else {
                if (isset($matches['version'][1])) {
                    $result['version'] = $matches['version'][1];
                }
            }
        } else {
            if (isset($matches['version'][0])) {
                $result['version'] = $matches['version'][0];
            }
        }

        if (!isset($result['version']) || $result['version'] == null || $result['version'] == "") {
            $result['version'] = "?";
        }
        return $result;
    }

}


?>