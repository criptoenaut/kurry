<?php
  /*!
   * \brief Class to provide common functionality needed by other Controllers
   */
class BaseController
{
  public $params = array();
  public $display_partial;
  public $scripts;
  public $json_results;
  public $js_arrays;
  public $flash;
  public $results;
  protected $smarty;
  protected $constants;
	
  function __construct()
  {
    global $smarty, $constants;
    global $constants;
    $this->getParams();
    $this->constants = &$constants;
    $this->smarty = &$smarty;
    $this->display_partial = False;
    $this->flash = array();
    $this->results = array();
  }

  /*!
   * \brief Method to get POST/GET variables. Stores filtered variables in class attribute $params
   * \return Array of key value pairs received using POST/GET
   */
  public function getParams()
  {
    foreach($_GET as $key => $value)
      {
	if(is_array($value))
	  {
	    $this->params[$key] = $value;
	  }
	else
	  {
	    $this->params[$key] = trim(trim($value),'/');
	  }
      }
    foreach($_POST as $key => $value)
      {
	if(is_array($value))
	  {
	    $this->params[$key] = $value;
	  }
	else
	  {
	    $this->params[$key] = trim(trim($value),'/');
	  }
      }
  }

  /*!
   * \brief Method to check if user is logged in
   * \return Boolean True on success, False on Failure
   */
  public function isLoggedIn()
  {
    $logged_in = false;
    if($this->getSession('login'))
      {
	$logged_in = $this->getSession('login');
      }

    if($logged_in)
      {
	return True;
      }
    return False;
  }

	
  /*!
   * \brief Method to get value of a session variable
   * \param $key - Session variable name
   * \return Value of the Session variable
   * \return Boolean False if variable does not exist in session
   */
  public function getSession($key)
  {
    if(isset($_SESSION[$key]))
      {
	return $_SESSION[$key];
      }
    return False;
  }
	
  /*!
   * \brief Method to set the value for a variable in Session
   * \param $key - Session variable
   * \param $value - Value for the Session variable. If set to boolean False the variable is unset from Session.
   * \return Boolean True on success, False on failure.
   */
  public function setSession($key = False, $value = False)
  {
    if(!$key)
      {
	return False;
      }
    else
      {
	if(!$value)
	  {
	    unset($_SESSION[$key]);
	  }
	else
	  {
	    $_SESSION[$key] = $value;
	  }
	return True;
      }
  }

  /*!
   * \brief Method to set Flash variables. Variables remain alive for the current page and next page (in the case of a redirect)
   * \param $key - Variable to be set
   * \param $value - Value for the variable
   * \return Boolean True on success
   */
  public function setFlash($key, $value)
  {
    $this->flash[$key] = $value;
    return True;
  }

  /*!
   * \brief Method to set Flash variables as session variable
   * \return Boolean True on success
   */
  public function pushFlashToSession()
  {
    $this->setSession('flash', $this->flash);
    return true;
  }

  /*!
   * \brief Method to remove Flash variables from session
   * \return Boolean True on success
   */
  public function cleanUp()
  {
    unset($_SESSION['flash']);
    return true;
  }
 
  /*!
   * \brief Method to redirect user within the app
   * \param $action - Action to which user is redirected.
   * \param $controller - Controller to which user is redirected. If no controller is specified user is redirected to the $action specified in the current controller.
   * \param $querystring - If query string e.g. "?name=kapil&age=30" is specified it is appended to the redirect URL.
   */
  public function redirect($action = '', $controller = '', $querystring = '')
  {
    $this->pushFlashToSession();

    if(empty($action) && empty($controller))
      {
	header("Location: /");
      }
    elseif(empty($controller) && !empty($action))
      {
	$controller = get_class($this);
	$pos = strpos($controller, 'Controller');
	$controller = strtolower(get_class($this));
	if($pos !== False)
	  {
	    $controller = substr($controller, 0, $pos);
	  }
	header("Location: /".$controller."/".$action.$querystring);
      }
    elseif(empty($action) && !empty($controller))
      {
	header("Location: /".$controller.$querystring);
      }
    else
      {
	header("Location: /".$controller.'/'.$action.$querystring);
      }
    exit;
  } 


  /*!
   * \brief Method to include JS and CSS scripts for a particular action
   * \param $script - Name of the script to be included
   * \param $type - 'js' for Javascript files, 'jscode' for embedding js code, 'css' for Stylesheet
   * \return Boolean true on success
   */
  public function includeScript($script = '', $type = 'js')
  {
    if(empty($script))
      {
	return false;
      }

    if(empty($this->scripts))
      {
	$this->scripts = '';
      }

    switch($type)
      {
      case 'jscode':
	$this->scripts .= "\n<script type=\"text/javascript\">\n".$script.";\n</script>\n"; // Minor hack. Appending a semi-colon towards the end of script.

	break;
      case 'css':
	$this->scripts .= "
<link rel='stylesheet' href='".CSS_URL.$script.".css' type='text/css' />
";
	break;
      case 'js':
      default:
	$this->scripts .= "
<script language='javascript' type='text/javascript' src='".JS_URL.$script.".js'></script>
";
	break;
      }

    return True;
  }


  /*!
   * \brief Method to spit out JSON for webservice requests
   * \param $results - Result set to be converted to JSON 
   */
  public function spitJSON($results)
  {
    header("Content-type:text/json");
    echo json_encode($results);
    exit;
  }

  /*!
   * \brief Method to convert PHP array to JS object
   * \param $php_array - PHP array to be converted
   * \param $js_object_name - Name to be used for the JS object
   * \param $level - Used for indentation. Leave it alone :-|
   * \return JS object as string which can be embedded in the page
   * \return Boolean False on failure
   */
  public function arrayToJs($php_array, $js_object_name='', $level = 0)
  {
    if(empty($php_array))
      {
	return False;
      }
    if(empty($js_object_name))
      {
	$js_object_name = $php_array;
      }

    for ($i=0; $i<$level; $i++)
      {
	$pre .= '    ';
      }

    $this->js_arrays[$js_object_name] .= $pre.$js_object_name.' = new Object();'."\n";
    foreach ($php_array as $key => $value) 
      {
	if (!is_int($key))
	  {
	    $key = '"'.addslashes($key).'"';
	  }
	if (is_array($value))
	  {
	    $this->arrayToJs($value, $js_object_name.'['.$key.']', $level+1);
	  }
	else
	  {
	    $this->js_arrays[$js_object_name] .= $pre.'    '.$js_object_name.'['.$key.']'.' = ';

	    if (is_int($value) or is_float($value))
	      {
		$this->js_arrays[$js_object_name] .= $value;
	      }
	    elseif (is_bool($value))
	      {
		$this->js_arrays[$js_object_name] .= $value ? "true" : "false";
	      }
	    elseif (is_string($value))
	      {
		$this->js_arrays[$js_object_name] .= '"'.addslashes($value).'"';
	      }
	    else
	      {
		return False;
	      }
	    $this->js_arrays[$js_object_name] .= ";\n";
	  }
      }

    $output_string = '';
    foreach ($this->js_arrays as $array)
      {
	$output_string .= $array;
      }

    $this->js_arrays = array();

    return $output_string;
  }

  /*!
   * \brief Method to convert JSON string to XML
   * \params $json - JSON string
   * \return String data in XML format
   */
  public function json2xml($json)
  {
    $data = json_decode($json, true);
    
    // An array of serializer options
    $serializer_options = array (
				 'addDecl' => TRUE,
				 'encoding' => 'ISO-8859-1',
				 'indent' => '  ',
				 'rootName' => 'json',
				 'mode' => 'simplexml'
				 ); 
    
    $Serializer = new XML_Serializer($serializer_options);
    $status = $Serializer->serialize($data);
    
    if (PEAR::isError($status))
      {
	return false;
      }
    else
      {
	return htmlspecialchars($Serializer->getSerializedData());
      }
  }

  /*!
   * \brief Method to convert array string to XML
   * \params $params - PHP array
   * \return String data in XML format
   */
  public function spitXML($params = array())
  {    
    require 'XML/Serializer.php';

    // An array of serializer options
    $serializer_options = array (
				 'addDecl' => TRUE,
				 'encoding' => 'ISO-8859-1',
				 'indent' => '  ',
				 'rootName' => 'response',
				 'mode' => 'simplexml'
				 ); 
    
    $Serializer = new XML_Serializer($serializer_options);
    $status = $Serializer->serialize($params);
    
    header("Content-Type:text/xml"); 
    echo $Serializer->getSerializedData();
    exit;
  }

  /*!
   * \brief Method to display error messages
   * \params $error_code - String, error code
   * \return String error code and message in XML/JSON/CSV format
   */
  public function spitError($error_code = 0)
  {
    global $_message;

    if(!isset($_message[$error_code]))
      {
	$error_code = 0;
      }

      $response = array('status' => $error_code, 'message' => $_message[$error_code]);
      switch($this->params['type'])
	{
	case 'xml':
	  $this->spitXML($response);
	  break;
	case 'csv':
	  $this->spitCSV($response);
	  break;
	case 'json':
	default:
	  $this->spitJSON($response);
	  break;
	}
  }


  /*!
   * \brief Method to be called for actions in which login is mandatory
   * \params $actions - comma separated list of actions, if empty login needed for all actions of the controller
   * \params $redirect_controller - (optional) controller to which the user is redirected to
   * \params $redirect_action - (optional) action to which the user is redirected to
   * \return Boolean True if user is logged in, else redirects the user
   */
  public function loginMandatory($actions = '', $redirect_controller = 'auth', $redirect_action = 'index')
  {
    $login = $this->isLoggedIn();

    if(empty($actions))
      {
	if($login == False)
	  {
	    $this->setSession('back_params', $this->params);
	    $this->redirect($redirect_action, $redirect_controller);
	  }
	else
	  {
	    return True;
	  }
      }

    if(strpos($actions, $this->params['action']) !== False)
      {
	if($login == False)
	  {
	    $this->setSession('back_params', $this->params);
	    $this->redirect($redirect_action, $redirect_controller);
	  }
      }
    return True;
  }

  /*!
   * \brief Method to fetch feed from Memcache
   * \params $hash - md5 hash of string '<site>/<client>/<feed>'. e.g. md5("cricinfo/moblica/moblica_scorecard");
   * \return String - JSON representation of feed
   * \return Boolean - False if feed data is not found on memcache
   */
  public function memcacheFetch($hash)
  {
    $memobj = new Memcache;
    $memobj->connect(MEMCACHE_HOST, MEMCACHE_PORT);
    return $memobj->get($hash);
  }

  /*!
   * \brief Method to store feed data in memcache
   * \params $key - md5 hash of string '<site>/<client>/<feed>'. e.g. md5("cricinfo/moblica/moblica_scorecard");
   * \params $value - JSON representation of feed
   * \params $expiry - Optional, number of seconds (integer) for which tehentry should be kept alive, defaults to 86400 (1 day)
   * \return Boolean - True on Success, False on failure
   */
  public function memcacheStore($key, $value, $expiry = MEMCACHE_EXPIRY)
  {
    $memobj = new Memcache;
    $memobj->connect(MEMCACHE_HOST, MEMCACHE_PORT);
    return $memobj->set($key, $value, 0, $expiry);
  }

  /*!
   * \brief Method to compute difference between two associative arrays
   * \params $array1 - primary associative array
   * \params $array2 - secondary associative array (in most cases this will be a subset of the primary array)
   * \return Difference between the two arrays as an associative array
   */
  public function arrayDiff($array1, $array2)
  {
    foreach($array1 as $key => $value)
      {
	if(is_array($value))
	  {
	    if(!isset($array2[$key]))
	      {
		$difference[$key] = $value;
	      }
	    elseif(!is_array($array2[$key]))
	      {
		$difference[$key] = $value;
	      }
	    else
	      {
		$new_diff = $this->arrayDiff($value, $array2[$key]);
		if($new_diff != FALSE)
		  {
		    $difference[$key] = $new_diff;
		  }
	      }
	  }
	elseif(!isset($array2[$key]) || $array2[$key] != $value)
	  {
	    $difference[$key] = $value;
	  }
      }
    return !isset($difference) ? 0 : $difference;
  }

  /**
   *
   * Convert an object to an array
   *
   * @param    object  $object The object to convert
   * @return   array
   *
   */
  public function objectToArray($object)
  {
    if(!is_object($object) && !is_array($object))
      {
	return $object;
      }
    if(is_object($object))
      {
	$object = get_object_vars($object);
      }
    return array_map('objectToArray', $object);
  }

  /*!
   * \brief Method to spit out CSV for webservice requests
   * \param $data - Result set to be converted to CSV
   */
  public function spitCSV($data) 
  {
    $filename = $this->params['controller'].'_'.$this->params['action'];
    if(isset($this->params['id']))
      {
	$filename .= '_'.$this->params['id'];
      }
    header('Content-type: text/csv');
    header('Content-Disposition: attachment; filename="'.$filename.'_'.date("dmY").'.csv"');
    header("Cache-Control: no-cache, must-revalidate");

    $outstream = fopen("php://output", 'w');    
    function __outputCSV(&$vals, $key, $filehandler) {
      fputcsv($filehandler, $vals, ',', '"');
    }
    array_walk($data, '__outputCSV', $outstream);

    fclose($outstream);
    exit;
  }

  /*!
   * \brief Method to generate Barcode
   * \param $string - string for which barcode is to be generated
   * \return <image> - returns image for barcode in PNG format
   */
  public function generateBarcode($string)
  {
    $this->barcode39($string,500,120,100,'PNG',1);
  }

  //-----------------------------------------------------------------------------
  // Generate a Code 3 of 9 barcode
  //-----------------------------------------------------------------------------
  protected function barcode39 ($barcode, $width, $height, $quality, $format, $text)
  {
    switch ($format)
      {
      default:
	$format = "JPEG";
      case "JPEG": 
	header ("Content-type: image/jpeg");
	break;
      case "PNG":
	header ("Content-type: image/png");
	break;
      case "GIF":
	header ("Content-type: image/gif");
	break;
      }


    $im = ImageCreate ($width, $height)
      or die ("Cannot Initialize new GD image stream");
    $White = ImageColorAllocate ($im, 255, 255, 255);
    $Black = ImageColorAllocate ($im, 0, 0, 0);
    //ImageColorTransparent ($im, $White);
    ImageInterLace ($im, 1);



    $NarrowRatio = 20;
    $WideRatio = 55;
    $QuietRatio = 35;


    $nChars = (strlen($barcode)+2) * ((6 * $NarrowRatio) + (3 * $WideRatio) + ($QuietRatio));
    $Pixels = $width / $nChars;
    $NarrowBar = (int)(20 * $Pixels);
    $WideBar = (int)(55 * $Pixels);
    $QuietBar = (int)(35 * $Pixels);


    $ActualWidth = (($NarrowBar * 6) + ($WideBar*3) + $QuietBar) * (strlen ($barcode)+2);
        
    if (($NarrowBar == 0) || ($NarrowBar == $WideBar) || ($NarrowBar == $QuietBar) || ($WideBar == 0) || ($WideBar == $QuietBar) || ($QuietBar == 0))
      {
	ImageString ($im, 1, 0, 0, "Image is too small!", $Black);
	OutputImage ($im, $format, $quality);
	exit;
      }
        
    $CurrentBarX = (int)(($width - $ActualWidth) / 2);
    $Color = $White;
    $BarcodeFull = "*".strtoupper ($barcode)."*";
    settype ($BarcodeFull, "string");
        
    $FontNum = 9;
    $FontHeight = ImageFontHeight ($FontNum+5);
    $FontWidth = ImageFontWidth ($FontNum+5);
    if ($text != 0)
      {
	$CenterLoc = (int)(($width) / 2) - (int)(($FontWidth * strlen($BarcodeFull)) / 2);
	ImageString ($im, $FontNum, $CenterLoc, $height-$FontHeight, "$barcode", $Black);
      }
    else
      {
	$FontHeight=-2;
      }


    for ($i=0; $i<strlen($BarcodeFull); $i++)
      {
	$StripeCode = $this->code39 ($BarcodeFull[$i]);


	for ($n=0; $n < 9; $n++)
	  {
	    if ($Color == $White) $Color = $Black;
	    else $Color = $White;


	    switch ($StripeCode[$n])
	      {
	      case '0':
		ImageFilledRectangle ($im, $CurrentBarX, 0, $CurrentBarX+$NarrowBar, $height-1-$FontHeight-2, $Color);
		$CurrentBarX += $NarrowBar;
		break;


	      case '1':
		ImageFilledRectangle ($im, $CurrentBarX, 0, $CurrentBarX+$WideBar, $height-1-$FontHeight-2, $Color);
		$CurrentBarX += $WideBar;
		break;
	      }
	  }


	$Color = $White;
	ImageFilledRectangle ($im, $CurrentBarX, 0, $CurrentBarX+$QuietBar, $height-1-$FontHeight-2, $Color);
	$CurrentBarX += $QuietBar;
      }


    $this->outputImage($im, $format, $quality);
  }


  //-----------------------------------------------------------------------------
  // Output an image to the browser
  //-----------------------------------------------------------------------------
  protected function outputImage($im, $format, $quality)
  {
    switch ($format)
      {
      case "JPEG": 
	ImageJPEG ($im, "", $quality);
	break;
      case "PNG":
	ImagePNG ($im);
	break;
      case "GIF":
	ImageGIF ($im);
	break;
      }
  }


  //-----------------------------------------------------------------------------
  // Returns the Code 3 of 9 value for a given ASCII character
  //-----------------------------------------------------------------------------
  protected function code39 ($Asc)
  {
    switch ($Asc)
      {
      case ' ':
	return "011000100";     
      case '$':
	return "010101000";             
      case '%':
	return "000101010"; 
      case '*':
	return "010010100"; // * Start/Stop
      case '+':
	return "010001010"; 
      case '|':
	return "010000101"; 
      case '.':
	return "110000100"; 
      case '/':
	return "010100010"; 
      case '-':
	return "010000101";
      case '0':
	return "000110100"; 
      case '1':
	return "100100001"; 
      case '2':
	return "001100001"; 
      case '3':
	return "101100000"; 
      case '4':
	return "000110001"; 
      case '5':
	return "100110000"; 
      case '6':
	return "001110000"; 
      case '7':
	return "000100101"; 
      case '8':
	return "100100100"; 
      case '9':
	return "001100100"; 
      case 'A':
	return "100001001"; 
      case 'B':
	return "001001001"; 
      case 'C':
	return "101001000";
      case 'D':
	return "000011001";
      case 'E':
	return "100011000";
      case 'F':
	return "001011000";
      case 'G':
	return "000001101";
      case 'H':
	return "100001100";
      case 'I':
	return "001001100";
      case 'J':
	return "000011100";
      case 'K':
	return "100000011";
      case 'L':
	return "001000011";
      case 'M':
	return "101000010";
      case 'N':
	return "000010011";
      case 'O':
	return "100010010";
      case 'P':
	return "001010010";
      case 'Q':
	return "000000111";
      case 'R':
	return "100000110";
      case 'S':
	return "001000110";
      case 'T':
	return "000010110";
      case 'U':
	return "110000001";
      case 'V':
	return "011000001";
      case 'W':
	return "111000000";
      case 'X':
	return "010010001";
      case 'Y':
	return "110010000";
      case 'Z':
	return "011010000";
      default:
	return "011000100"; 
      }
  }

}
