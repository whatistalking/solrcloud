<?php 
$base =  dirname(__FILE__);
$solr_root = substr($base, 0, strrpos($base,'scripts'));
$script_root = $solr_root."/scripts";
include_once($script_root . "/common.php");

delete_status_current($link_log,'service','docnumber');
$service_list = get_service_list_running($link);
foreach($service_list as $service){
	$service_url = $cfg['search_url'].'/'.$service['service_name'].'/select/?q=*:*&rows=0&wt=json';
	$result = curl_get_content($service_url);
	$result = @json_decode($result,true);
	$docnumber = @intval($result['response']['numFound']);
        
        add_status_log($link_log,$cfg['idc'],$cfg['default_host'],'service','docnumber',$service['service_id'],$docnumber,date('Y-m-d H:i:00'), 'status_log_service_docnumber');
        add_status_current($link_log,'service','docnumber',$service['service_id'],$docnumber,date('Y-m-d H:i:00'));
}
