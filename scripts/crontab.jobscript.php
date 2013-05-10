<?php
$base =  dirname(__FILE__);
$solr_root = substr($base, 0, strrpos($base,'scripts'));
$script_root = $solr_root."/scripts";
include_once($script_root . "/common.php");

$to_run_jobs = array();
$job_list = get_job_list($link);

//find to run jobs
$current_time = time();
$date_time = date('Y-m-d H:i:s',$current_time);
foreach($job_list as $job){
	
	if(!in_array($job['job_type'],$cfg['execute_cron']))continue;
	
	$last_run_time = strtotime($job['last_run_time']);
	if($job['plan_run_interval']){
		if(($current_time-$last_run_time)>=$job['plan_run_interval']*60){
			$to_run_jobs[] = $job['job_script'];
			upd_job_run_time($link,$job['job_id'],$date_time);
		}
	}elseif($job['plan_run_time']){
		$current_hour = date('G',$current_time);
		$last_run_hour = date('G',$last_run_time);
		$plan_run_time = explode(',',$job['plan_run_time']);
		if(in_array($current_hour,$plan_run_time)&&$current_hour!=$last_run_hour){
			$to_run_jobs[] = $job['job_script'];
			upd_job_run_time($link,$job['job_id'],$date_time);
		}
	}	
}

echo implode("\n",$to_run_jobs);
