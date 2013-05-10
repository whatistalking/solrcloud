#!/bin/bash

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

. ${solr_root}/scripts/config.sh

ids=$(get_service_ids)
for id in ${ids}
do
    service_name=$(get_service_name ${id})
    instanceIds=$(get_service_instances ${id})
    for i in ${instanceIds}
    do
        portnum=$(get_port_num ${i})
        hostIp=$(get_host_ip ${i})

        log_time=$(date +"%F %H:%M:00")
        tmp=/tmp/tmpc.${service_name}.${portnum}.${id}
        w1=${service_name}/select
        w2=${service_name}/update
        
        rm -f ${tmp}

        for a in `seq 1 5`; do
            t=$(date +"%d/%b/%Y:%H:%-M:" -d "${a} minutes ago"|awk -F : '{print $2}')
            m=$(date +"%d/%b/%Y:%H:%-M:" -d "${a} minutes ago"|awk -F : '{print $3}')
            ${this_path}/lib/count.sh ${t} ${m} ${w1} ${portnum} ${hostIp} >> ${tmp}
            ${this_path}/lib/count.sh ${t} ${m} ${w2} ${portnum} ${hostIp} >> ${tmp}
        done
        postall=$(grep -c "\/update" ${tmp})
        getall=$(grep -c "\/select" ${tmp})
    
        result=$(grep -r "\/select" ${tmp}|awk -F , '{print $2/1000}'|${cmd_php} ${this_path}/lib/calculate.percent.php 1000 10 3)
        ms=$(echo ${result}|awk '{print $1}')

        rm -f ${tmp}

        sql_log="replace into status_log_instance_access set log_idc='${idc}',log_host='${default_host}',log_type='instance',log_name='millisecond_90',target_id='${i}',log_value='${ms}',log_time='${log_time}'"
        ${cmd_mysql_log} -e "${sql_log}"

        sql_log="replace into status_log_instance_access set log_idc='${idc}',log_host='${default_host}',log_type='instance',log_name='select',target_id='${i}',log_value='${getall}',log_time='${log_time}'"
        #echo ${sql_log}
        ${cmd_mysql_log} -e "${sql_log}"
        sql_log="replace into status_log_instance_access set log_idc='${idc}',log_host='${default_host}',log_type='instance',log_name='update',target_id='${i}',log_value='${postall}',log_time='${log_time}'"
        #echo ${sql_log}
        ${cmd_mysql_log} -e "${sql_log}"
    done
done
