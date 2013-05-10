#!/bin/bash

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

. ${solr_root}/scripts/config.sh

sql="delete from status_current where log_type='service' and log_name='select'"
${cmd_mysql_log} -e "${sql}"
sql="delete from status_current where log_type='service' and log_name='update'"
${cmd_mysql_log} -e "${sql}"

ids=$(get_service_ids)
for id in ${ids}
do
    ${this_path}/status.access/access.log.sh ${id} >> ${solr_root}/logs/nginx.log.$(date +%F).log 2>&1
done
