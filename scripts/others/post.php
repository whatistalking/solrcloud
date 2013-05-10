<?php
$now = time();

$url = "http://sysdev.dev.anjuke.com:8986/bbsthread/update/";
$xml = "<add>
    <doc>
        <field name=\"id\">$now</field>
        <field name=\"title\">title title title caption desc</field>
        <field name=\"desc\">desc desc desc caption title</field>
    </doc>
</add>";

if (service_post($url, $xml)) exit(0);
exit(1);
//service_post($url, "<commit/>"))

function service_post($url, $xml) {
    $curl = curl_init();
    $options = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => array("Content-type:text/xml;charset=utf-8"),
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $xml
    );
    curl_setopt_array($curl, $options);

    $result = curl_exec($curl);
    $info = curl_getinfo($curl);
    curl_close($curl);

    if ($info["http_code"] == 200) {
        return true;
    } else {
        print_r($info);
        return false;
    }
}
