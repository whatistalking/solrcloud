<?php
$base =  dirname(__FILE__);
$solr_root = substr($base, 0, strrpos($base,'scripts'));
$script_root = $solr_root."/scripts";
include_once($script_root . "/common.php");

$strSQL='show tables like "status_log\_%"';
$tables=pdo_fetch_all($link_log,$strSQL,array());

foreach($tables as $table){
   $table = array_pop($table); 
   
   $strSQL="select id from {$table} where log_time<? limit 10000";
    $arrArc=pdo_fetch_all($link_log,$strSQL,array( 
    		date('Y-m-d H:i:00',time()-345600) 
    ));

    if(!empty($arrArc)){
    	$arrAllIDs=array();
    	foreach($arrArc as $arrData){
    		$arrAllIDs[]=$arrData['id'];
    	}
    	$strPattern=join(',',array_fill(0,count($arrAllIDs),'?'));
        $strSQL='insert into status_logs_arc select * from '.$table.' where id in ('.$strPattern.')';
    	$sth=$link_log->prepare($strSQL);
    	$sth->execute($arrAllIDs);
    
    	$strSQL='delete from '.$table.' where id in ('.$strPattern.')';
    	$sth=$link_log->prepare($strSQL);
    	$sth->execute($arrAllIDs);
    }
    
}
