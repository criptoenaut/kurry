<?php
session_start();

include '../includes/init.php';

if(!file_exists('../controllers/'.$basec->params['controller'].'.php'))
{
  $basec->spitError(-1);
}

include '../controllers/'.$basec->params['controller'].'.php';

$classname = ucwords($basec->params['controller']).'Controller';

$obj = new $classname;

if(method_exists($obj, $basec->params['action']))
{
	$obj->{$basec->params['action']}();
}
else
{
  $obj->spitError(-2);
}

if(!$obj->getSession('flash'))
{
	$obj->pushFlashToSession();
}

switch($basec->params['type']) {
case 'xml':
  $obj->spitXML($obj->results);
  break;
case 'csv':
  $basec->spitCSV($obj->results);
  break;
case 'json':
default:
  $obj->spitJSON($obj->results);
  break;
}

$obj->cleanup();