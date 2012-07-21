<?php
include 'toolkit/ez_sql/ez_sql_core.php';
include 'toolkit/ez_sql/ez_sql_mysql.php';

/*!
 * \brief Parent class for all models.
 * Provides methods for functionality that is required across all models.
 */
class BaseModel
{
  public $dbm, $dbs, $dbconn;

  function __construct()
  {
  }

  public function dbConnect($master = array(), $slave = array())
  {
    if($this->checkPresenceOf($master))
      {
	$this->dbm = new ezSQL_mysql($master['user'], $master['password'], $master['db'], $master['host']);
	$this->dbconn = True;
      }

    if($this->checkPresenceOf($slave))
      {
	$this->dbs = new ezSQL_mysql($slave['user'], $slave['password'], $slave['db'], $slave['host']);
	$this->dbconn = True;
      }
  }
 
  public function checkPresenceOf($needle)
  {
    if(!isset($needle) || empty($needle))
      {
	if($needle === 0 || $needle === "0")
	  {
	    return True;
	  }
	return False;
      }
    return True;
  }
 
  public function checkLengthOf($needle, $minimum = 0, $maximum = 100)
  {
    if(!$this->checkPresenceOf($needle))
      {
	return False;
      }
    
    if(is_numeric($needle))
      {
	$needle_length = $needle;
      }
    elseif(is_string($needle))
      {
	$needle_length = strlen($needle);
      }
    elseif(is_array($needle))
      {
	$needle_length = count($needle);
      }

    if($needle_length >= $minimum && $needle_length <= $maximum)
      {
	return True;
      }
    else
      {
	return False;
      }

    return False;
  }
 
  public function checkUniquenessOf($field, $model = '')
  {
    if(!$this->checkPresenceOf($model))
      {
	$model = strtolower(get_class($this));
      }

    $column = array_keys($field);
    $sql = "SELECT SQL_NO_CACHE count(*) as occurance FROM ".$model." WHERE ".$column[0]."='".$field[$column[0]]."'";

    $count = $this->dbs->get_var($sql);

    if($count > 0)
      {
	return false;
      }

    return true;
  }

  
  /*!
   * \brief Method to check if Email address is valid
   * \param $email - Email address to be checked
   * \return Boolean True on success, False on failure
   */
  public function checkEmail($email)
  {
    if(filter_var($email, FILTER_VALIDATE_EMAIL) === false)
      {
	return false;
      }
    return true;
  }
 
  /*!
   * \brief Method to insert data in a Model
   * \param $fields Associative array containing column names as keys and data as values. Set key 'insert_delayed' => True to use delayed inserts.
   * \param $table string (optional) if present it over-rides the model and inserts data into the specified table
   * \return True boolean on success
   */
  public function insertData($fields, $model = '')
  {
    $created_at = $updated_at = date("Y-m-d H:i:s");
    if(empty($model))
      {
	$model = strtolower(get_class($this));
      }
 
    if(isset($fields['insert_delayed']) && $fields['insert_delayed'] === True)
      {
	$sql = "INSERT DELAYED into ".$model." SET ";
      }
    else
      {
	$sql = "INSERT into ".$model." SET ";
      }
 
    $counter = 0;
    $fields['created_at'] = $created_at;
    $fields['updated_at'] = $updated_at;
 
    foreach($fields as $key => $value)
      {
	if($counter == 1)
	  {
	    $sql .= ", ";
	  }
	$sql .= " ".$key." = '".addslashes($value)."'";
	$counter = 1;
      }
    $this->dbm->query($sql);

    return true;
  }
 
  /*!
   * \brief Method to update data for a Model
   * \param $fields Associative array containing column names as keys and data as values. Must have 'id' as key.
   * \param If 'id' is not present should have the key: 'where' e.g. 'where' => "age=30 AND name='kapil'".
   * \param $table string (optional) if present it over-rides the model and updates data in the specified table
   * \return True boolean on success
   */
  public function updateData($fields, $model = '')
  {
    $updated_at = date("Y-m-d H:i:s");
    
    if(empty($model))
      {
	$model = strtolower(get_class($this));
      }
 
    $sql = "UPDATE ".$model." SET ";
 
    $counter = 0;
    foreach($fields as $key => $value)
      {
	if($key != 'id' && $key != 'where' && $key != 'debug')
	  {
	    if($counter == 1)
	      {
		$sql .= ", ";
	      }
	    $sql .= " ".$key." = '".addslashes($value)."'";
	    $counter = 1;
	  }
      }
 
    $sql .= ',';
 
    $sql .= " updated_at = '".$updated_at."'";
    if(isset($fields['id']))
      {
	$sql .= " WHERE id = '".$fields['id']."' ";
      }
    elseif($this->checkPresenceOf($fields['where']))
      {
	$sql .= " WHERE ".$fields['where']." ";
      }
    else
      {
	return false;
      }
    
    if(isset($fields['debug']) == 'true')
      {
	var_dump($sql);
      }

    $this->dbm->query($sql);

    return true;
  }

  
  /*!
   * \brief Method to fetch data from a Model
   * \param $params Associative array with attributes of data to be fetched:
   * Attributes:
   * \param 'select' => By default, this is "*" as in "SELECT * FROM", can be changed to comma seperated list of model attributes to fetch.
   * \param 'from' => By default, this is the table name of the class, can be changed to an alternate table name
   (or even the name of a database view).
   * \param 'conditions' => An SQL fragment like "administrator = 1" (WHERE clause).
   * \param 'group' => An attribute name by which the result should be grouped. (GROUP BY clause).
   * \param 'order' => An SQL fragment like "created_at DESC, name". (ORDER BY clause)
   * \param 'limit' => An integer determining the limit on the number of rows that should be returned.
   * \param 'offset' => An integer determining the offset from where the rows should be fetched. So at 5, it would skip rows 0 through 4.
   * \param 'return_type' => string, optional, valid options: 'ARRAY_A' (Associative array), 'ARRAY_N' (Numerical array). Default: an object is returned
   * \param $table string (optional) if present it over-rides the model and fetches data from the specified table
   */
  public function find($params = array(), $model = '')
  {
    
    if(!isset($params['select']) || empty($params['select']))
      {
	$params['select'] = ' * ';
      }

    if(!isset($params['from']))
      {
	if(empty($model))
	  {
	    $model = strtolower(get_class($this));
	  }
	$params['from'] = $model;
      }

    $where = '';
    if(isset($params['conditions']))
      {
	$where = " WHERE ".$params['conditions']." ";
      }
 
    $group = '';
    if(isset($params['group']))
      {
	$group = " GROUP BY ".$params['group']." ";
      }
 
    $order = '';
    if(isset($params['order']))
      {
	$order = " ORDER BY ".$params['order']." ";
      }
 
    $limit = '';
    if(isset($params['limit']))
      {
	$limit = ' LIMIT '.$params['limit'].' ';
	if(isset($params['offset']))
	  {
	    $limit .= 'OFFSET '.$params['offset'].' ';
	  }
      }
 
    $sql = "SELECT ".$params['select'].
      " FROM ".$params['from']." ".
      $where.
      $group.
      $order.
      $limit;

    if(isset($params['debug']) && $params['debug'] == true)
      {
	var_dump($sql);
      }
    if(!isset($params['return_type']))
      {
	$params['return_type'] = 'OBJECT';
      }
    $return_type = '';
    switch($params['return_type'])
      {
      case 'ARRAY_A':
	$results = $this->dbs->get_results($sql, ARRAY_A);
	break;
      case 'ARRAY_N':
	$results = $this->dbs->get_results($sql, ARRAY_N);
	break;
      case 'OBJECT':
	$results = $this->dbs->get_results($sql);
	break;
      default:
	$results = $this->dbs->get_results($sql, ARRAY_A);
	break;
      }

    if ($results != null)
      {
	if(!isset($params['format_by']) || !$this->checkPresenceOf($params['format_by']))
	  {
	    return $results;
	  }
	else
	  {
	    $temp = array();
	    foreach($results as $key => $value)
	      {
		if(is_array($value))
		  {
		    $temp[$value[$params['format_by']]] = $value;
		  }
		else
		  {
		    $temp[$value->{$params['format_by']}] = $value;
		  }
	      }
	    return $temp;
	  }
      }

    return false;
  }

  public function removeSlashes($results)
  {
    $clean = $results;
    $results_type = gettype($results);
    foreach($results as $key => $value)
      {
	$type = gettype($value);
	if($type == 'array')
	  {
	    foreach($value as $x => $y)
	      {
		$ytype = gettype($y);
		if($ytype == 'array' || $ytype == 'object')
		  {
		    if($results_type == 'object')
		      {
			$clean->{$key}[$x] = $y;
		      }
		    else
		      {
			$clean[$key][$x] = $y;
		      }
		  }
		else
		  {
		    if($results_type == 'object')
		      {
			$clean->{$key}[$x] = stripslashes($y);
		      }
		    else
		      {
			$clean[$key][$x] = stripslashes($y);
		      }
		  }
	      }
	  }
	elseif($type == 'object')
	  {
	    foreach($value as $x => $y)
	      {
		$ytype = gettype($y);
		if($ytype == 'array' || $ytype == 'object')
		  {
		    if($results_type == 'object')
		      {
			$clean->{$key}->{$x} = $y;
		      }
		    else
		      {
			$clean[$key]->{$x} = $y;
		      }
		  }
		else
		  {
		    if($results_type == 'object')
		      {
			$clean->{$key}->{$x} = stripslashes($y);
		      }
		    else
		      {
			$clean[$key]->{$x} = stripslashes($y);
		      }
		  }
	      }
	  }
	else
	  {
	    if($results_type == 'object')
	      {
		$clean->$key = stripslashes($value);
	      }
	    else
	      {
		$clean[$key] = stripslashes($value);
	      }
	  }
      }
    return $clean;
  }
  
  /*!
   * \brief Method to fetch data from a Model based on the primary key (id)
   * \param $ids - a single id or a comma separated list of ids
   */
  public function findById($ids, $model = '')
  {
    if(empty($model))
      {
	$model = strtolower(get_class($this));
      }

    if(strpos($ids, ','))
      {
	$sql = "SELECT * FROM ".$model." WHERE id IN (".$ids.")";
	$results = $this->dbs->get_results($sql, ARRAY_A);
	if($this->checkPresenceOf($results))
	  {
	    $results = $this->removeSlashes($results);
	  }
	else
	  {
	    $results = array();
	  }
			   
	$newresults = array();
	foreach($results as $key => $value)
	  {
	    $newresults[$value['id']] = $value;
	  }
	return $newresults;
      }
    else
      {
	$sql = "SELECT * FROM ".$model." WHERE id = ".$ids."";
	$results = $this->dbs->get_row($sql, ARRAY_A);
	if($this->checkPresenceOf($results))
	  {
	    $results = $this->removeSlashes($results);
	  }

	return $results;
      }
  }
 
  /*!
   * \brief Executes a custom SQL query on the database
   * \param $sql - SQL query string
   * \param $method - (optional) Method to use for retriving the result set if $sql is a SELECT query
   * \return - Associative array of results
   */
  public function execSQL($sql, $method = '', $model = '')
  {
    if(stripos($sql, 'select') !== False)
      {
	switch($method)
	  {
	  case 'get_row':
	    $results = $this->dbs->get_row($sql);
	    break;
	  default:
	    $results = $this->dbs->get_results($sql);
	    break;
	  }
	return $results;
      }
    else
      {
	$this->dbm->query($sql);
	return true;
      }
  }


  public function generatePassword()
  {
    // start with a blank password
    $password = "";
    $length = 6;

    // define possible characters
    $possible = "0123456789bcdfghjkmnpqrstvwxyz";
    
    // set up a counter
    $i = 0; 
    
    // add random characters to $password until $length is reached
    while ($i < $length) { 
      // pick a random character from the possible ones
      $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
      
      // we don't want this character if it's already in the password
      if (!strstr($password, $char)) { 
	$password .= $char;
	$i++;
      }
    }

    // done!
    return $password;
  }

  /*!
   * \brief Method to sanitize phone numbers
   * \params $phone - Phone Number
   * \return string Phone number on Success, Boolean false on Failure
   */
  public function sanitizePhoneNumber($phone)
  {
    $phone = str_replace(array('+','-',' ','(',')'), '', $phone);
    
    $length = strlen($phone);
    switch($length)
      {
      case 10:
	$phone = '91'.$phone;
	break;
      case 12:
	$phone = $phone;
	break;
      default:
	return $phone;
	break;
      }
    return $phone;
  }

  protected function createURL($string)
  {
    $search = array("/", "\\", " ", "?", "-", "_", "'", "\"", "&");
    $replace = '';
    $result = str_replace($search, $replace, $string);
    return $result;
  }

}
