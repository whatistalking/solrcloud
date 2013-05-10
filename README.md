# Solr Cloud

## Install

### Solr Instance

    sudo aptitude install subversion mysql-client php5-cli php5-mysql
    svn checkout http://projects.dev.anjuke.com/svn/sites/utils/solr-cloud/ solr/ --username=deployer
    rm -rf solr/cloud/7700
    svn checkout http://projects.dev.anjuke.com/svn/sites/search/cloud/ solr/cloud/7700/ --username=deployer
    chmod +x solr/scripts/*.sh
    touch solr/config.prod.sh solr/config.prod.php
    nohup solr/scripts/solr.cron.sh 1>>solr/logs/solr.cron.log 2>>solr/logs/solr.cron.error.log &
    solr/scripts/execator.enable.sh

## config

* machine specified configurations

```
cp solr.config.sh.example /home/www/conf/solr.config.sh # modifiy to meet your need
cp solr.config.php.example /home/www/conf/solr.config.php # modifiy to meet your need
```
* production configurations

pick up the example and modify to meet your need

```
cp config.prod.sh.example config.prod.sh
cp config.prod.php.example config.prod.php
```

### some options need to be configured.


