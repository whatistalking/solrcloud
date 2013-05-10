#!/bin/bash
#
# 0 1 * * * /home/www/solr/scripts/solr.stdout.rotate.sh
# solr的jetty中配置jetty-logging模块，操作日志会记录到/logs/stdout.log.XXXX_11_06,要打包压缩转存
# 注意cloud目录与/data1/cloud有软鏈
#


today=`date +%Y"_"%m"_"%d`
todaylog=stdout.log."$today"

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

clouddir=${solr_root}/cloud

for i in `ls "$clouddir"`
do
    logdir="$clouddir"/"$i"/server/logs
    if [ -d "$logdir" ];then
        cd "$logdir"
        for m in `ls stdout.log.*`
        do
            if [ ${m:${#m}-2} != 'gz' ] && [ "$m" != "$todaylog" ]; then
                echo $logdir/$m
                tar -czf $m.gz $m
                rm $logdir/$m
            fi
        done
    fi
done
