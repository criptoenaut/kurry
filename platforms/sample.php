<?php

class Sample extends BaseModel
{
  private $db_master, $db_slave;

  function __construct()
  {
    $db = explode(",",$_ENV['M_DB_SAMPLE']);
    $this->db_master = array('user' => $db[0],'password' => $db[1],'db' => $db[2],'host' => $db[3]);

    $db = explode(",",$_ENV['S_DB_SAMPLE']);
    $this->db_slave = array('user' => $db[0],'password' => $db[1],'db' => $db[2],'host' => $db[3]);

    parent::dbConnect($this->db_master, $this->db_slave);
    parent::__construct();
  }  
}