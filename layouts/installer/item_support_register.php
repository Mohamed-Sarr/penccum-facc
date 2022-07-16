<?php
$register_token = 'aHR0cHM6Ly9iYWV2b3guY29tL2FwcGxvZ2dlci8=';
$register_token = urldecode(base64_decode($register_token));
$data_fields = array(
    'purchase_code' => urlencode($data['purchase_code']),
    'email_address' => urlencode($data['email_address']),
    'website' => urlencode(Registry::load('config')->site_url),
);

if (isset($data['discover_grupo']) && !empty($data['discover_grupo'])) {
    $data_fields['website'] .= ' - '.$data['discover_grupo'];
}

$register_data = '';

foreach ($data_fields as $key => $value) {
    $register_data .= $key.'='.$value.'&';
}

rtrim($register_data, '&');

$register_request = curl_init();
curl_setopt($register_request, CURLOPT_HEADER, 0);
curl_setopt($register_request, CURLOPT_URL, $register_token);
curl_setopt($register_request, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($register_request, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($register_request, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($register_request, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1');
curl_setopt($register_request, CURLOPT_AUTOREFERER, true);
curl_setopt($register_request, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($register_request, CURLOPT_CONNECTTIMEOUT, 0);
curl_setopt($register_request, CURLOPT_POST, count($data_fields));
curl_setopt($register_request, CURLOPT_POSTFIELDS, $register_data);
$register_item_support = curl_exec($register_request);
curl_close($register_request);
?>