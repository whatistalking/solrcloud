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

echo "Nginx reconfigure"

service_id=${1}
service_status=$(get_service_status ${service_id})

if [ "${service_status}" == "1" ]
then
    ${cmd_php} ${solr_root}/scripts/action_queue/lib/upstream.php ${service_id} \
    > ${solr_root}/nginx/conf.d/upstream.${service_id}.conf

    ${cmd_php} ${solr_root}/scripts/action_queue/lib/location.php ${service_id} \
    > ${solr_root}/nginx/conf.d/location.${service_id}.conf
else
    rm -f ${solr_root}/nginx/conf.d/upstream.${service_id}.conf
    rm -f ${solr_root}/nginx/conf.d/location.${service_id}.conf
fi
