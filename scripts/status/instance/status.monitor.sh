#!/bin/bash
#
# 检查所有service绑定的instance是否正常工作
#
cd ${0%/*}
base=$(pwd)
cd ${base%scripts*}
solr_root=$(pwd)

. ${solr_root}/scripts/config.sh


for i in $(get_service_ids)
do
    instance_ids=$(get_service_instances ${i})
    for j in ${instance_ids}
    do
        for k in 1 2 3
        do
            log="${solr_root}/logs/solr.monitor.$(date +"%F").log"
            log_time=$(date +"%F %T")
            rst=$(${base}/lib/solr.select.sh ${j})
            code=$?
            if [ ${code:0:1} -ne "2" ]
            then
                break;
            fi

            sleep 1
        done

        echo -e "${log_time}\t${code}\t${rst}\t${j}" >> ${log}
        set_monitor_status ${j} ${code}
    done
done
