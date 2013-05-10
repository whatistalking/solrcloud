<?php
$script_root = dirname(__FILE__).'/../..';
include_once($script_root . "/common.php");

$strSQL='select host_id from host';
$arrHosts=pdo_fetch_all($link,$strSQL);

foreach($arrHosts as $arrHost){
	$strSQL='select log_value from status_logs where target_id=? and log_type=? and log_name=? order by id desc limit 1';
	$arrLastMem=pdo_fetch($link_log,$strSQL,array( 
			$arrHost['host_id'],
			'host',
			'memoryused' 
	));
	$arrLastLoad=pdo_fetch($link_log,$strSQL,array( 
			$arrHost['host_id'],
			'host',
			'loadaverage' 
	));
	$arrHost['memoryused']=$arrLastMem['log_value'];
	$arrHost['loadaverage']=$arrLastLoad['log_value'];
	
	$strSQL='replace into report set r_type=?,target_id=?,r_data=?';
	$sth=$link_log->prepare($strSQL);
	$arrHost['lastupd']=date('Y-m-d H:i:s');
	$sth->execute(array( 
			'host',
			$arrHost['host_id'],
			json_encode($arrHost) 
	));
}

$strSQL='select service_id from service';
$arrServices=pdo_fetch_all($link,$strSQL);

foreach($arrServices as $arrService){
	$strSQL='select log_value from status_logs where target_id=? and log_type=? and log_name=? order by id desc limit 20';
	$arrLastDocNumber=pdo_fetch_all($link_log,$strSQL,array( 
			$arrService['service_id'],
			'service',
			'docnumber' 
	));
	$arrLastSelCnt=pdo_fetch_all($link_log,$strSQL,array( 
			$arrService['service_id'],
			'service',
			'select' 
	));
	$arrLastUpdCnt=pdo_fetch_all($link_log,$strSQL,array( 
			$arrService['service_id'],
			'service',
			'update' 
	));
	$arrMilliSecond=pdo_fetch_all($link_log,$strSQL,array(
			$arrService['service_id'],
			'service',
			'millisecond_90'
	));
	if(false===$arrLastDocNumber){
		$arrService['docnumber']='-';
	}else{
		$intTotal=$intTime=0;
		foreach($arrLastDocNumber as $arrTmp){
			$intTotal=$intTotal+$arrTmp['log_value'];
			++$intTime;
		}
		$arrService['docnumber']=round($intTotal/$intTime);
	}
	if(false===$arrLastSelCnt){
		$arrService['selcnt']='-';
	}else{
		$intTotal=$intTime=0;
		foreach($arrLastSelCnt as $arrTmp){
			$intTotal=$intTotal+$arrTmp['log_value'];
			++$intTime;
		}
		$arrService['selcnt']=round($intTotal/$intTime);
	}
	if(false===$arrLastUpdCnt){
		$arrService['updcnt']='-';
	}else{
		$intTotal=$intTime=0;
		foreach($arrLastUpdCnt as $arrTmp){
			$intTotal=$intTotal+$arrTmp['log_value'];
			++$intTime;
		}
		$arrService['updcnt']=round($intTotal/$intTime);
	}
	if(false===$arrMilliSecond){
		$arrService['millsec']='-';
	}else{
		$intTotal=$intTime=0;
		foreach($arrMilliSecond as $arrTmp){
			$intTotal=$intTotal+$arrTmp['log_value'];
			++$intTime;
		}
		$arrService['millsec']=round($intTotal/$intTime);
	}
	$arrService['lastupd']=date('Y-m-d H:i:s');
	
	$strSQL='replace into report set r_type=?,target_id=?,r_data=?';
	$sth=$link_log->prepare($strSQL);
	$sth->execute(array( 
			'service',
			$arrService['service_id'],
			json_encode($arrService) 
	));
}
