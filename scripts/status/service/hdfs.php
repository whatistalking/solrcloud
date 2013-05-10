<?php
$base =  dirname(__FILE__);
$solr_root = substr($base, 0, strrpos($base,'scripts'));
$script_root = $solr_root."/scripts";
include_once($script_root . "/common.php");
include_once($base.'/lib/hdfs.php');

echo "========".date('Y-m-d H:i:s')."========\n";

$date = date('Ymd',strtotime('-1 day'));

$fromdir = '/user/corp/solr_slow_query/'.$date.'-avg';
$tmpdir = '/tmp/solr_slow_query-avg';
echo $fromdir.'>>>>'.$tmpdir.date('Y-m-d H:i:s')."\n";
$down = hdfs_merge($fromdir, $tmpdir);
/*将数据导入DB*/
if($down && file_exists($tmpdir)){
    $fq = fopen($tmpdir, "r");
    if ($fq) {
        $datetime = date('Y-m-d',strtotime($date));
        /*删除旧数据 & 当天的数据*/
        delete_old_slowquery($link_log);
        delete_slowquery($link_log, $date);
        /*insert*/
        $i = array();
        while (($buffer = fgets($fq)) !== false) {
            $arr = explode(' ', $buffer);
            if(!empty($arr) && count($arr)>=4){
                $service = $arr[0];
                $url = $arr[1];

                if(empty($i[$service])) $i[$service] = 1;
                else {
                    if($i[$service] >20) continue;
                    $i[$service]++;
                }

                $avg = round($arr[2],2);/*ms*/
                $count = $arr[3];

                insert_slowquery($link_log,$service, urldecode($url), $avg, $count, $datetime);
                $i++;
            }else{
                echo 'error line:'.$buffer;
            }
        }
        if (!feof($fq)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($fq);
    }
}
else{
    echo 'fail to download!';
}
unlink($tmpdir);


$fromdir = '/user/corp/solr_slow_query/'.$date.'-count';
$tmpdir = '/tmp/solr_slow_query-count';
echo $fromdir.'>>>>'.$tmpdir.date('Y-m-d H:i:s')."\n";
$down = hdfs_merge($fromdir, $tmpdir);
/*将数据导入DB*/
if($down && file_exists($tmpdir)){
    $fq = fopen($tmpdir, "r");
    if ($fq) {
        $datetime = date('Y-m-d',strtotime($date));
        /*删除旧数据 & 当天的数据*/
        delete_old_frequentquery($link_log);
        delete_frequentquery($link_log, $date);
        /*insert*/
        $i = array();
        while (($buffer = fgets($fq)) !== false) {
            $arr = explode(' ', $buffer);
            if(!empty($arr) && count($arr)>=4){
                $service = $arr[0];
                $url = $arr[1];

                if(empty($i[$service])) $i[$service] = 1;
                else {
                    if($i[$service] >20) continue;
                    $i[$service]++;
                }

                $avg = round($arr[2],2);/*ms*/
                $count = $arr[3];

                insert_frequentquery($link_log,$service, urldecode($url), $avg, $count, $datetime);
                $i++;
            }else{
                echo 'error line:'.$buffer;
            }
        }
        if (!feof($fq)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($fq);
    }
}
else{
    echo 'fail to download!';
}
unlink($tmpdir);

