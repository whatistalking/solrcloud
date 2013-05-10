#!/bin/bash

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

. ${solr_root}/scripts/config.sh

if [ -f ${cmd_nginx_rh} ]
then
	if [ ! -z ${cmd_nginx_rh} ]
	then
	    echo "Nginx reload"
	    ${cmd_nginx_rh} reload
	    exit $?
	fi
fi

if [ ! -f ${cmd_nginx} ]
then
    exit "Nginx bin not exists"
    exit 1
fi

nginx_pid=$(get_nginx_pid)
if [ -z ${nginx_pid} ]
then
    echo "Nginx process not exists"
    exit 1
fi

${cmd_nginx} -t -c ${solr_root}/nginx/nginx.conf
retval=$?

if [ ${retval} == "0" ]
then
    echo "Nginx reload"
    kill -HUP ${nginx_pid}
    exit $?
else
    exit $retval
fi
