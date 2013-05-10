<?php
$mfactor = $argv[1];
$step = $argv[2];
$upperlimit = $argv[3];
$times = array();
$total = 0;

$stdin = fopen('php://stdin', 'r');
while (!feof($stdin)) {
    $line = trim(fgets($stdin));
    $key = ceil($line*$mfactor/$step);
    if($line>$upperlimit){
        $key = ceil($upperlimit*$mfactor/$step);
    }
    $times[$key] = @$times[$key] + 1;
    $total++;
}
//print_r($times);exit;
ksort($times);
$sum = 0;
$ms_res=null;
$per_res=null;
foreach ($times as $key => $num) {
    $per = $num * 100 / $total;
    $sum += $per;
    if($sum>90 && $ms_res==null){
        $ms_res = $key*$step;
    }
    if($key*$step>=100 && $per_res==null){
    	$per_res = $sum;
    }
    #echo sprintf("%s\t%s\t%.2f%%\t%.2f%%\n", $key*$step , $num , $per, $sum);
}
if($ms_res==null)$ms_res=0;
if($per_res==null)$per_res=100;
echo $ms_res.' '.floor($per_res+0.5);
