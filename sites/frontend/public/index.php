<?php
include '../includes/init.php';

$environment = getenv('PLATFORM_ENV');

if($environment == 'production' && file_exists(BASE_DIR.'html/'.$basec->params['controller'].'/'.$basec->params['action'].'.html'))
  {
    echo file_get_contents(BASE_DIR.'html/'.$basec->params['controller'].'_'.$basec->params['action'].'.html');
    exit;
  }
else
  {
    $smarty->assign('params',$basec->params);
    $markup = $smarty->fetch('index.tpl',null,null,false);
    file_put_contents(BASE_DIR.'html/'.$basec->params['controller'].'_'.$basec->params['action'].'.html',$markup);
    echo $markup;
    exit;
  }