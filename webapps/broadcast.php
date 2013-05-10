<?php
require_once './libraries/common.lib.php';

function parseInput(){
	$arrInput=array();
	$arrHeader=array();
	$arrHeader['CONTENT-TYPE']=$_SERVER['CONTENT_TYPE'];
	$arrHeader['CONTENT-LENGTH']=$_SERVER['CONTENT_LENGTH'];
	$arrInput['HEADER']=$arrHeader;
	$arrInput['INPUT']=file_get_contents('php://input');
	$arrCstParam=array();
	$arrCstParam['REQUEST_URI']=$_GET['uri'];
	$arrCstParam['ACTION']=$_GET['act'];
	$arrInput['CSTPARAM']=$arrCstParam;
	return $arrInput;
}

$arrInput=parseInput();
// echo 'server: '; print_r($_SERVER); echo '<hr />';
// echo 'parsed input: ';print_r($arrInput); echo '<hr />';
broadCast($pdo,$arrInput);

function broadCast($pdo,$p_arrInput){
	if('select'==$p_arrInput['CSTPARAM']['ACTION']){

	}elseif('update'==$p_arrInput['CSTPARAM']['ACTION']){
		// echo 'array input: '; print_r($p_arrInput); echo '<hr />';
		$strSQL='select h.host_ip,i.port_num from service s inner join service_mapping m on s.service_id=m.service_id inner join instance i on m.instance_id=i.instance_id inner join host h on h.host_id=i.host_id where service_name=?';
		$sth=$pdo->prepare($strSQL);
		/*
		 * $sth->execute(array( 'jp-office-rent' ));
		 */
		$sth->execute(array( 
				$p_arrInput['CSTPARAM']['REQUEST_URI'] 
		));
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$rst=$sth->fetchAll();
		if(empty($rst)){
			return;
		}
		// echo 'rst: '; print_r($rst); echo '<hr />';
		$arrTime=array();
		$arrTime[]=$floStartTime=microtime(true);
		$arrHeader=array();
		foreach($p_arrInput['HEADER'] as $strKey=>$strValue){
			$arrHeader[]=$strKey.': '.$strValue;
		}
		// echo 'curl header: ';print_r($arrHeader); echo '<hr />';
		$curl=curl_init();
		curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,5);
		curl_setopt($curl,CURLOPT_TIMEOUT,5);
		curl_setopt($curl,CURLOPT_HEADER,true);
		curl_setopt($curl,CURLOPT_POST,true);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_POSTFIELDS,$p_arrInput['INPUT']);
		curl_setopt($curl,CURLOPT_HTTPHEADER,$arrHeader);
		foreach($rst as $rs){
			$strURL='http://'.$rs['host_ip'].'/solr/'.$p_arrInput['CSTPARAM']['ACTION'].'/';
			// echo 'url: '.$strURL.'<hr />';
			curl_setopt($curl,CURLOPT_URL,$strURL);
			curl_setopt($curl,CURLOPT_PORT,$rs['port_num']);
			for($i=0;$i<3;++$i){
				$rs=curl_exec($curl);
				if(false!==$rs){
					break;
				}
			}
			if(false===$rs){ // 广播模块网络错误,构造502 Bad Gateway错误数据
				/*
				 * $arrHeaderSend=headers_list(); foreach($arrHeaderSend as
				 * $strContent){ $arrTmp=explode(':',$strContent);
				 * header_remove($arrTmp[0]); }
				 */
				$strServerSig='ajk_solr_cloud_broadcast/0.0.1';
				$strHTML='<html><head><title>502 Bad Gateway</title></head><body bgcolor="white"><center><h1>502 Bad Gateway</h1></center><hr><center>'.$strServerSig.'</center></body></html>';
				$intLen=strlen($strHTML);
				header('HTTP/1.1 100 Continue',true,100);
				header('HTTP/1.1 502 Bad Gateway',true,502);
				// header('Server: '.$strServerSig,true);
				// header('Date: '.date(DATE_RFC822),true);
				header('Content-Type: text/html');
				header('Content-Length: '.$intLen);
				header('Connection: keep-alive');
				echo $strHTML;
				exit();
			}else{ // 后端模块返回内容
			
			}
			// echo 'curl info: '; print_r(curl_getinfo($curl)); echo '<hr />';
			$arrTime[]=microtime(true);
		}
		curl_close($curl);
		
		$floEndTime=microtime(true);
		/*
		 * $arrReturn=array( 'total'=>($floEndTime-$floStartTime) );
		 * $intCnt=count($arrTime); for($i=1;$i<$intCnt;++$i){
		 * $arrReturn[$rst[$i-1]['host_ip'].':'.$rst[$i-1]['port_num']]=$arrTime[$i]-$arrTime[$i-1];
		 * } print_r($arrReturn);
		 */
	}
}