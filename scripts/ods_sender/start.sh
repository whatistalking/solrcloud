#!/bin/bash

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

. ${solr_root}/scripts/config.sh

tail -F ${access_log_path}/access.log | ${PYTHON_BIN} ${this_path}/push.py -T solr_alog -H 10.10.3.43 -P 5554 >> ${this_path}/log &

#path='/home/www/solr/scripts/ods_sender';
#tail -F /data1/logs/solr_log/access.log | /usr/local/python2.7/bin/python2.7 ${path}/push.py -T solr_alog -H 10.10.3.43 -P 5554 >> ${path}/log &

