<?php

$base_dir = str_replace('/public/', '/', $_SERVER['DOCUMENT_ROOT']);
define('BASE_DIR', $base_dir);

define('JS_URL','http://'.$_SERVER['SERVER_NAME'].'/javascript/');
define('CSS_URL','http://'.$_SERVER['SERVER_NAME'].'/stylesheets/');

define('AUTH_SERVER','http://'.$_SERVER['SERVER_NAME'].'/');


