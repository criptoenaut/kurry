<?php

class RestController extends BaseController
{
  function __construct()
  {
    parent::__construct();
  }

  public function index()
  {
    if(strpos($this->params['method'],'.'))
      {
	$action = explode('.',$this->params['method']);

	if(file_exists(BASE_DIR.'/controllers/'.$action[0].'.php'))
	  {
	    include($action[0].'.php');
	    
	    $classname = ucwords($action[0].'Controller');
				 
	    $obj = new $classname;
	    
	    if(method_exists($obj, $action[1]))
	      {
		$this->results = $obj->{$action[1]}();
	      }
	  }	
      }
  }
}