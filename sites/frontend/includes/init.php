<?php

require 'config.inc';
require 'defines.inc';
//require 'platforms/basemodel.php';
require 'platforms/basecontroller.php';
require 'toolkit/smarty/Smarty.class.php';

//$basem = new BaseModel();
$basec = new BaseController();

$smarty = new Smarty();

$smarty->template_dir = BASE_DIR.'views/';
$smarty->compile_dir = BASE_DIR.'Smarty/templates_c';
$smarty->cache_dir = BASE_DIR.'Smarty/cache';
$smarty->config_dir = BASE_DIR.'Smarty/configs';

$smarty->left_delimiter = '<{';
$smarty->right_delimiter = '}>';

$smarty->compile_check = True;
//$smarty->debugging = True;

$smarty->assign('params',$basec->params);
$smarty->assign('js_url',JS_URL);
$smarty->assign('css_url',CSS_URL);
