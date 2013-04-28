<?php
include 'template.php';
define('DS',DIRECTORY_SEPARATOR);
define('ROOT_PATH',dirname(__FILE__).DS);


$tpl = new Template();

$tpl->assign('data', 'hello world!');

$tpl->show('index');
