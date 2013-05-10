# server
cat evans:x:506 >> /etc/group
yum install mysql-server.x86_64 php-fpm.x86_64 php-cli.x86_64 php-mysql.x86_64 php-pdo.x86_64 nginx-stable.x86_64

mkdir /home/www/solr
cd /home/www/solr
mkdir -p cloud nginx/conf.d logs /data1/logs/nginx

# idx01-006
scp -r cloud/7700/ idx02-003.i.ajkdns.com:/home/www/solr/cloud/
scp config.prod.* idx02-003.i.ajkdns.com:/home/www/solr/
scp nginx/nginx.conf idx02-003.i.ajkdns.com:/home/www/solr/nginx/
scp /etc/php-fpm.conf idx02-003.i.ajkdns.com:/tmp/

vim config.prod.php
vim config.prod.sh

# server
cd /etc/nginx/
rm -f nginx.conf
ln -s /home/www/solr/nginx/nginx.conf
cd /etc/
mv php-fpm.conf php-fpm.conf.back
mv /tmp/php-fpm.conf .
/etc/init.d/php-fpm start

# donkey
/home/www/release/solr.sh #96