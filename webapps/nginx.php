<?php
require_once './libraries/common.lib.php';

$content = cat_confd(SC_PATH . "/../nginx/conf.d");

$current_nav='nginx';
$template = 'nginx';
require_once './libraries/decorator.inc.php';
