#!/bin/bash

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

. ${solr_root}/scripts/config.sh

usage="Usage: ${prog} [instance_id]"

if [ -z ${1} ]
then
    echo ${usage}
    exit 2
fi

instance_id=${1}
port=$(get_port_num ${instance_id})
if [ -z ${port} ]
then
    echo "Instance[${instance_id}] not exists"
    exit 1
fi

instance_root=$(build_instance_root ${solr_root} ${port})
if [ ! -d ${instance_root} ]
then
    echo "Instance[${instance_id}] not deploied"
    exit 1
fi

echo "Solr reconfigure schema instance[${instance_id}]"

${cmd_php} ${solr_root}/scripts/action_queue/lib/solr.schema.php ${instance_id} \
    > ${instance_root}/idx/conf/schema.xml
