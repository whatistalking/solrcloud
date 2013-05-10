#!/bin/bash

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

# . /etc/profile
. ${solr_root}/scripts/config.sh

for table in $(get_tables)
do
    if [ -z "$(echo ${table} | grep "^log_")" ]
    then
        tables="${tables} ${table}"
    fi
done

dump_dir=${solr_root}/dumps  
if [ ! -d ${dump_dir} ]; then
    mkdir ${dump_dir} 
fi

mysqldump \
  -h ${mysql_db_host} \
  -u ${mysql_db_user} \
  -p${mysql_db_pass} \
  ${mysql_db_name} \
  ${tables} | grep -v "^\/\|^DROP\|^$\|^--" \
  > ${dump_dir}/$(date +"%F").sql
