#!/bin/bash

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

. ${solr_root}/scripts/config.sh

if [ -z ${1} ]
then
    echo "Usage: ${prog} [service_id]"
    exit 1
fi

service_id=${1}
service_name=$(get_service_name ${service_id})
if [ -z ${service_name} ]
then
    echo "Service ${service_id} not exists"
    exit 1
fi

log_time=$(date +"%F %H:%M:00")
tmp=/tmp/tmpc.${service_name}
w1=${service_name}/select
w2=${service_name}/update

rm -f ${tmp}
for a in `seq 1 5`; do
    t=$(date +"%d/%b/%Y:%H:%-M:" -d "${a} minutes ago"|awk -F : '{print $2}')
    m=$(date +"%d/%b/%Y:%H:%-M:" -d "${a} minutes ago"|awk -F : '{print $3}')
    
    ${this_path}/count.sh ${t} ${m} ${w1} >> ${tmp}
    ${this_path}/count.sh ${t} ${m} ${w2} >> ${tmp}
done
postall=$(grep -c "\/update" ${tmp})
getall=$(grep -c "\/select" ${tmp})
#awk -F , '{print $2/1000}' ${tmp} > /tmp/log.log
result=$(grep -r "\/select" ${tmp}|awk -F , '{print $2/1000}'|${cmd_php} ${this_path}/calculate.percent.php 1000 10 3)


ms=$(echo ${result}|awk '{print $1}')
per=$(echo ${result}|awk '{print $2}')

rm -f ${tmp}

sql_log="replace into status_log_service_calculate set log_idc='${idc}',log_host='${default_host}',log_type='service',log_name='millisecond_90',target_id='${service_id}',log_value='${ms}',log_time='${log_time}'"
${cmd_mysql_log} -e "${sql_log}"

sql_log="replace into status_log_service_calculate set log_idc='${idc}',log_host='${default_host}',log_type='service',log_name='percent_100',target_id='${service_id}',log_value='${per}',log_time='${log_time}'"
${cmd_mysql_log} -e "${sql_log}"


sql_log="replace into status_log_service_access set log_idc='${idc}',log_host='${default_host}',log_type='service',log_name='select',target_id='${service_id}',log_value='${getall}',log_time='${log_time}'"
${cmd_mysql_log} -e "${sql_log}"
sql_log="replace into status_log_service_access set log_idc='${idc}',log_host='${default_host}',log_type='service',log_name='update',target_id='${service_id}',log_value='${postall}',log_time='${log_time}'"
${cmd_mysql_log} -e "${sql_log}"


sql_log="replace into status_current set log_type='service',log_name='select',target_id='${service_id}',log_value='${getall}',log_time='${log_time}'"
${cmd_mysql_log} -e "${sql_log}"
sql_log="replace into status_current set log_type='service',log_name='update',target_id='${service_id}',log_value='${postall}',log_time='${log_time}'"
${cmd_mysql_log} -e "${sql_log}"


