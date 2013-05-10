#!/bin/bash -

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)
. ${solr_root}/scripts/config.sh

#${cron_run} || exit 0

jobs=$(${cmd_php} ${solr_root}/scripts/crontab.jobscript.php)
for job in ${jobs}
do
    echo -ne "$(date "+%F %T") "
    j=$(echo ${job} | awk -F'&' '{print $1}')

    if [ ! -f "${solr_root}/scripts/${j}" ]
    then
        echo "file ${j} not exist"
        continue;
    fi 

    is_php=$(echo ${job}|grep -e '.php')
    if [ ${is_php} > 0 ]
    then
        echo "execute ${job}"
        ${cmd_php} ${solr_root}/scripts/${job}
    fi
    
    is_sh=$(echo ${job}|grep -e '.sh')
    if [ ${is_sh} > 0 ]
    then
        echo "execute ${job}"
        ${solr_root}/scripts/${job}
    fi
    
done
