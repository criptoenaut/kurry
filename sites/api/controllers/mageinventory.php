<?php
include "platforms/elves/magento.php";
include "platforms/elves.php";

class MageinventoryController extends BaseController
{
  private $elf, $magento;
  function __construct()
  {
    $this->elf = new Elves();
    $this->magento = new Magento();

    parent::__construct();
  }

  public function listProducts()
  {
    $fields = array(
		    'stock_status' => $this->params['stock_status'],
		    );

    $details = $this->magento->listProducts($fields);

    $todays_date = date('Y-m-d').' 00:00:00';
    foreach($details as $key => $value)
      {
	$value->discounted_price = 0;
	$value->url_path = 'http://zivame.com/'.$value->url_path;
	if($todays_date >= $value->special_from_date && $todays_date <= $value->special_to_date)
	  {
	    $value->discounted_price = $value->special_price;
	  }  
	unset($value->special_price);
	unset($value->special_to_date);
	unset($value->special_from_date);
	$results['product'][] = $details[$key];
      }

    return $results;
  }
}