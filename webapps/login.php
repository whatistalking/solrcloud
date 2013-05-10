<?php
require_once './libraries/common.lib.php';
global $cfg;
$url = $cfg['sc_url'];
$oauth_url = $cfg['oauth']['oauth_url'];
$cookie_time = $cfg['cookie_time'];
$cookie_name = $cfg['AuthCookieName'];
//access_token
$custom = '';
if (isset($_REQUEST['custom']) && $_REQUEST['custom']) {
    $custom = $_REQUEST['custom'];
}
$userinfo = login_oauth($custom);
$user_arr = json_decode($userinfo, true);
$username = $user_arr['username'];
$token = $user_arr['access_token'];
$info = get_info_from_oauth($token, $oauth_url);
$arr = json_decode($info, true);
$chinese_name = urlencode($arr['chinese_name']);
$cookie_str = "$username\t$chinese_name\t$token\t$cookie_time";
SetCookie($cookie_name, $cookie_str, time()+$cookie_time);
if (isset($_REQUEST['custom']) && $_REQUEST['custom']) {
    header("HTTP/1.1 302 Found");
    header("Location: " . 'http://' . $_REQUEST['custom']);
} else {
    header("HTTP/1.1 302 Found");
    header("Location: " . $url);
}