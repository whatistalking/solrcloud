#!/bin/bash - 

h=${1}
m=${2}
s=${3}
date=$(date +%m%d)

tmp='/tmp/solr.mongo.script'

sql="db.solr_alog.find({'A':${1},'D':${2},'W':'${3}'},{'E':1,'W':1}).forEach(function(item) {var str='';for(var i in item){str += item[i] + ',';}print(str);});"
echo ${sql} > ${tmp}
mongo 10.10.3.103:27017/access_log_${date} --quiet ${tmp}
rm ${tmp}
#mongo 10.10.3.103:27017/access_log_${date} --eval "${sql}"
