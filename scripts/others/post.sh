#!/bin/bash

orig_dir=$(pwd)
cd ${0%/*}/..
solr_root=$(pwd)
cd ${orig_dir}

exit

while true
do
    echo -ne "$(date +"%F %T") "
    php ${solr_root}/scripts/post.php
    echo $?
    sleep 3
done
