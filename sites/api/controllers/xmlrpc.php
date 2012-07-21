<?php

class XMLRPCController extends BaseController
{
  public $xmlrpcserver;

  function __construct()
  {
    parent::__construct();
  }

  public function index()
  {
    $request_xml = file_get_contents("php://input");
    $method = null; 
    $params = xmlrpc_decode_request($request_xml, &$method);

    if(strpos($method,'.'))
      {
	$action = explode('.',$method);

	if(file_exists(BASE_DIR.'controllers/'.$action[0].'.php'))
	  {
	    include($action[0].'.php');
	    
	    $classname = ucwords($action[0].'Controller');
				 
	    $obj = new $classname;
	    
	    if(method_exists($obj, $action[1]))
	      {
		$results = $obj->{$action[1]}($params[0]);
		header("Content-type:text/xml");
		echo xmlrpc_encode_request(NULL, $results);
		exit;
	      }
	  }	
      }
  }
}

/*
  Sample XML-RPC invocation
  $request = xmlrpc_encode_request('inventory.listproducts', array('brand' => 'Triumph', 'size' => 'small', 'in_stock' => 1));
  
  $url = "http://api.zivame.dev/xmlrpc";
  $header[] = "Content-type: text/xml";
  $header[] = "Content-length: ".strlen($request);
  
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
  
  $data = curl_exec($ch);
  echo $data; exit;
  if (curl_errno($ch)) {
    print curl_error($ch);
  } else {
    curl_close($ch);
    return $data;
  }
*/
