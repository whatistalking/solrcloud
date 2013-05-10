#!/bin/bash

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

. ${solr_root}/scripts/config.sh

usage="Usage: ${prog} [instance_id]"

if [ -z "${1}" ]
then
    echo ${usage}
    exit 2
fi

solr_version=$(get_solr_version ${1})

port=$(get_port_num ${1})
if [ -z "${port}" ]
then
    echo "Instance ${1} not exists"
    exit 1
fi

instance_root=$(build_instance_root ${solr_root} ${port})
if [ -d ${instance_root} ]
then
    echo "${instance_root} already exists"
    exit 0
fi

echo "Solr deploy instance[${1}]"
case ${solr_version} in
    1)
        version_dir='7700'
        ;;
    2)
        version_dir='7700-35'
        ;;
    3)
        version_dir='7700-36'
        ;;
    4)
        version_dir='7700-40'
        ;;
    5)
        version_dir='7700-41'
        ;;
    *)
        version_dir='7700-36'
esac
src=$(build_instance_root ${solr_root} ${version_dir})
cp -ar ${src} ${instance_root}
