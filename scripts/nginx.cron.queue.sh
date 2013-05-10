#!/bin/bash

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

while true
do
    ${solr_root}/scripts/nginx.execator.sh
    echo -e "$(date "+%F %T")\t0\tnginx.cron sleep"
    sleep 10
done
