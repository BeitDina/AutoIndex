<?php
/**
 * @package AutoIndex
 *
* @copyright (c) 2002-2023 Markus Petrux, John Olson, FlorinCB aka orynider at github.com
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
* @version $Id: RequestVars.php,v 0.92 2023/11/25 22:51:42 orynider Exp $
* @link http://mxpcms.sourceforge.net/
* @link http://autoindex.sourceforge.net
 */

/*
   AutoIndex PHP Script is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   AutoIndex PHP Script is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (!defined('IN_AUTOINDEX') || !IN_AUTOINDEX)
{
	die();
}


/**#@+
 * Class mx_request_vars specific definitions
 *
 * Following flags are options for the $type parameter in method _read()
 *
 */
define('TYPE_ANY'		, 0);		// Retrieve the get/post var as-is (only stripslashes() will be applied).
define('TYPE_INT'		, 1);		// Be sure we get a request var of type INT.
define('TYPE_FLOAT'		, 2);		// Be sure we get a request var of type FLOAT.
define('TYPE_NO_HTML'	, 4);		// Be sure we get a request var of type STRING (htmlspecialchars).
define('TYPE_NO_TAGS'	, 8);		// Be sure we get a request var of type STRING (strip_tags + htmlspecialchars).
define('TYPE_NO_STRIP'	, 16);		// By default strings are slash stripped, this flag avoids this.
define('TYPE_SQL_QUOTED'	, 32);		// Be sure we get a request var of type STRING, safe for SQL statements (single quotes escaped)
define('TYPE_POST_VARS'	, 64);		// Read a POST variable.
define('TYPE_GET_VARS'	, 128);		// Read a GET variable.
define('NOT_EMPTY'		, true);	//
/**#@-*/

/**
 * Class: mx_request_vars.
 *
 * This is the CORE request vars object. Encapsulate several functions related to GET/POST variables.
 * More than one flag can specified by OR'ing the $type argument. Examples:
 * - For instance, we could use ( TYPE_POST_VARS | TYPE_GET_VARS ), see method request().
 * - or we could use ( TYPE_NO_TAGS | TYPE_SQL_QUOTED ).
 * - However, TYPE_NO_HTML and TYPE_NO_TAGS can't be specified at a time (defaults to TYPE_NO_TAGS which is more restritive).
 * - Also, TYPE_INT and TYPE_FLOAT ignore flags TYPE_NO_*
 * Usage examples:
 * - $mode = $mx_request_vars->post('mode', TYPE_NO_TAGS, '');
 * - $page_id = $mx_request_vars->get('page', TYPE_INT, 1);
 * This class IS instatiated in common.php ;-)
 *
 * @access public
 * @author Markus Petrux, John Olson, FlorinCB
 * @package Core
 */
class RequestVars
{
	/**#@+
	* Constant identifying the super global with the same name.
	*/
	const _POST = 0;
	const _GET = 1;
	const _REQUEST = 2;
	const _COOKIE = 3;
	const _SERVER = 4;
	const _FILES = 5;
		
	const POST = 0;
	const GET = 1;
	const REQUEST = 2;
	const COOKIE = 3;
	const SERVER = 4;
	const FILES = 5;
	/**#@-*/		
	
	//
	// Implementation Conventions:
	// Properties and methods prefixed with underscore are intented to be private. ;-)
	//
	
	/**
	* @var	array	The names of super global variables that this class should protect if super globals are disabled.
	*/
	protected $super_globals = array(
		self::POST 		=> '_POST',
		self::GET 		=> '_GET',
		self::REQUEST 	=> '_REQUEST',
		self::COOKIE 	=> '_COOKIE',
		self::SERVER 	=> '_SERVER',
		self::FILES 	=> '_FILES',
	);
	
	/**
	* @vars	arrays	Stores count() of $GLOBALS arrays.
	*/	
	var $post_array = 0;	
	var $get_array = 0;
	var $request_array = 0;	
	var $cookie_array = 0;	
	var $server_array = 0;	
	var $files_arrays = 0;
	
	/**
	* @var	array	Stores original contents of $_REQUEST array.
	*/
	protected $original_request = null;
	
	/**
	* @var
	*/
	protected $super_globals_disabled = false;
	
	/**
	* @var	array	An associative array that has the value of super global constants as keys and holds their data as values.
	*/
	protected $input;
	
	/**
	* @var	\phpbb\request\type_cast_helper_interface	An instance of a type cast helper providing convenience methods for type conversions.
	* borrowed from github.comb
	*/
	protected $type_cast_helper;		
	
	// ------------------------------
	// Properties
	//
	
	/* ------------------------------
	* Constructor
	* Initialises the request class, that means it stores all input data in {@link $input input}
	* and then calls {@link deactivated_super_global deactivated_super_global}
	*/
	public function __construct($disable_super_globals = false)
	{
		foreach ($this->super_globals as $const => $super_global)
		{
			$this->input[$const] = isset($GLOBALS[$super_global]) ? $GLOBALS[$super_global] : array();
		}
		
		// simulate request_order = GP
		$this->original_request = $this->input[self::REQUEST];
		$this->input[self::REQUEST] = $this->input[self::POST] + $this->input[self::GET];
		
		$this->post_array = isset($GLOBALS['_POST']) ? count($GLOBALS['_POST']) : 0;		
		$this->get_array = isset($GLOBALS['_GET']) ? count($GLOBALS['_GET']) : 0;	
		$this->request_array = isset($GLOBALS['_REQUEST']) ? count($GLOBALS['_REQUEST']) : 0;
		$this->cookie_array = isset($GLOBALS['_COOKIE']) ? count($GLOBALS['_COOKIE']) : 0;
		$this->server_array = isset($GLOBALS['_SERVER']) ? count($GLOBALS['_SERVER']) : 0;
		$this->files_arrays = isset($GLOBALS['_FILES']) ? count($GLOBALS['_FILES']) : 0;
		
		if ($disable_super_globals)
		{
			$this->disable_super_globals();
		}
	}
	
	/**
	* Getter for $super_globals_disabled
	*
	* @return	bool	Whether super globals are disabled or not.
	* borrowed from github.comb
	*/
	public function super_globals_disabled()
	{
		return $this->super_globals_disabled;
	}
	
	/**
	* Disables access of super globals specified in $super_globals.
	* This is achieved by overwriting the super globals with instances of {@link \autoindex\request\deactivated_super_global \autoindex\request\deactivated_super_global}
	* borrowed from github.comb
	*/
	public function disable_super_globals()
	{
		if (!$this->super_globals_disabled)
		{
			foreach ($this->super_globals as $const => $super_global)
			{
				unset($GLOBALS[$super_global]);
				$GLOBALS[$super_global] = new deactivated_super_global($this, $super_global, $const);
			}

			$this->super_globals_disabled = true;
		}
	}

	/**
	* Enables access of super globals specified in $super_globals if they were disabled by {@link disable_super_globals disable_super_globals}.
	* This is achieved by making the super globals point to the data stored within this class in {@link $input input}.
	* borrowed from github.comb
	*/
	public function enable_super_globals()
	{
		if ($this->super_globals_disabled)
		{
			foreach ($this->super_globals as $const => $super_global)
			{
				$GLOBALS[$super_global] = $this->input[$const];
			}

			$GLOBALS['_REQUEST'] = $this->original_request;

			$this->super_globals_disabled = false;
		}
	}
	
	// ------------------------------
	// Public Methods
	//
	
	/**
	* This function allows overwriting or setting a value in one of the super global arrays.
	*
	* Changes which are performed on the super globals directly will not have any effect on the results of
	* other methods this class provides. Using this function should be avoided if possible! It will
	* consume twice the the amount of memory of the value
	*
	* @param	string	$var_name	The name of the variable that shall be overwritten
	* @param	mixed	$value		The value which the variable shall contain.
	* 								If this is null the variable will be unset.
	* @param	mx_request_vars::POST|GET|REQUEST|COOKIE	$super_global
	* 								Specifies which super global shall be changed
	*/
	public function overwrite($var_name, $value, $super_global = self::REQUEST)
	{
		if (!isset($this->super_globals[$super_global]))
		{
			return;
		}

		$this->type_cast_helper->add_magic_quotes($value);

		// setting to null means unsetting
		if ($value === null)
		{
			unset($this->input[$super_global][$var_name]);
			if (!$this->super_globals_disabled())
			{
				unset($GLOBALS[$this->super_globals[$super_global]][$var_name]);
			}
		}
		else
		{
			$this->input[$super_global][$var_name] = $value;
			if (!$this->super_globals_disabled())
			{
				$GLOBALS[$this->super_globals[$super_global]][$var_name] = $value;
			}
		}
	}
	
	// ------------------------------
	// Private Methods
	//

	/**
	 * Function: _read().
	 *
	 * Get the value of the specified request var (post or get) and force the result to be
	 * of specified type. It might also transform the result (stripslashes, htmlspecialchars) for security
	 * purposes. It all depends on the $type argument.
	 * If the specified request var does not exist, then the default ($dflt) value is returned.
	 * Note the $type argument behaves as a bit array where more than one option can be specified by OR'ing
	 * the passed argument. This is tipical practice in languages like C, but it can also be done with PHP.
	 *
	 * @access private
	 * @param unknown_type $var
	 * @param unknown_type $type
	 * @param unknown_type $dflt
	 * @return unknown
	 */
	public function _read($var, $type = TYPE_ANY, $dflt = '', $not_null = false)
	{
		if( ($type & (TYPE_POST_VARS|TYPE_GET_VARS)) == 0 )
		{
			$type |= (TYPE_POST_VARS|TYPE_GET_VARS);
		}

		if( ($type & TYPE_POST_VARS) && isset($_POST[$var]) ||
			($type & TYPE_GET_VARS)  && isset($_GET[$var]) )
		{
			$val = ( ($type & TYPE_POST_VARS) && isset($_POST[$var]) ? $_POST[$var] : $_GET[$var] );
			if( !($type & TYPE_NO_STRIP) )
			{
				if( is_array($val) )
				{
					foreach( $val as $k => $v )
					{
						$val[$k] = trim(stripslashes($v));
					}
				}
				else
				{
					$val = trim(stripslashes($val));
				}
			}
		}
		else
		{
			$val = $dflt;
		}

		if( $type & TYPE_INT )		// integer
		{
			return $not_null && empty($val) ? $dflt : intval($val);
		}

		if( $type & TYPE_FLOAT )		// float
		{
			return $not_null && empty($val) ? $dflt : floatval($val);
		}

		if( $type & TYPE_NO_TAGS )	// ie username
		{
			if( is_array($val) )
			{
				foreach( $val as $k => $v )
				{
					$val[$k] = htmlspecialchars(strip_tags(ltrim(rtrim($v, " \t\n\r\0\x0B\\"))));
				}
			}
			else
			{
				$val = htmlspecialchars(strip_tags(ltrim(rtrim($val, " \t\n\r\0\x0B\\"))));
			}
		}
		elseif( $type & TYPE_NO_HTML )	// no slashes nor html
		{
			if( is_array($val) )
			{
				foreach( $val as $k => $v )
				{
					$val[$k] = htmlspecialchars(ltrim(rtrim($v, " \t\n\r\0\x0B\\")));
				}
			}
			else
			{
				$val = htmlspecialchars(ltrim(rtrim($val, " \t\n\r\0\x0B\\")));
			}
		}

		if( $type & TYPE_SQL_QUOTED )
		{
			if( is_array($val) )
			{
				foreach( $val as $k => $v )
				{
					$val[$k] = str_replace(($type & TYPE_NO_STRIP ? "\'" : "'"), "''", $v);
				}
			}
			else
			{
				$val = str_replace(($type & TYPE_NO_STRIP ? "\'" : "'"), "''", $val);
			}
		}

		return $not_null && empty($val) ? $dflt : $val;
	}

	// ------------------------------
	// Public Methods
	//

	/**
	* Central type safe input handling function.
	* All variables in GET or POST requests should be retrieved through this function to maximise security.
	*
	* @param	string|array	$var_name	The form variable's name from which data shall be retrieved.
	* 										If the value is an array this may be an array of indizes which will give
	* 										direct access to a value at any depth. E.g. if the value of "var" is array(1 => "a")
	* 										then specifying array("var", 1) as the name will return "a".
	* @param	mixed			$default	A default value that is returned if the variable was not set.
	* 										This function will always return a value of the same type as the default.
	* @param	bool			$multibyte	If $default is a string this parameter has to be true if the variable may contain any UTF-8 characters
	*										Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks
	* @param	mx_request_vars::POST|GET|REQUEST|COOKIE	$super_global
	* 										Specifies which super global should be used
	*
	* @return	mixed	The value of $_REQUEST[$var_name] run through {@link set_var set_var} to ensure that the type is the
	*					the same as that of $default. If the variable is not set $default is returned.
	*/
	public function variable($var_name, $default, $multibyte = false, $super_global = self::REQUEST)
	{
		return $this->_variable($var_name, $default, $multibyte, $super_global, true);
	}

	/**
	* Get a variable, but without trimming strings.
	* Same functionality as variable(), except does not run trim() on strings.
	* This method should be used when handling passwords.
	*
	* @param	string|array	$var_name	The form variable's name from which data shall be retrieved.
	* 										If the value is an array this may be an array of indizes which will give
	* 										direct access to a value at any depth. E.g. if the value of "var" is array(1 => "a")
	* 										then specifying array("var", 1) as the name will return "a".
	* @param	mixed			$default	A default value that is returned if the variable was not set.
	* 										This function will always return a value of the same type as the default.
	* @param	bool			$multibyte	If $default is a string this parameter has to be true if the variable may contain any UTF-8 characters
	*										Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks
	* @param	mx_request_vars::POST|GET|REQUEST|COOKIE	$super_global
	* 										Specifies which super global should be used
	*
	* @return	mixed	The value of $_REQUEST[$var_name] run through {@link set_var set_var} to ensure that the type is the
	*					the same as that of $default. If the variable is not set $default is returned.
	*/
	public function untrimmed_variable($var_name, $default, $multibyte = false, $super_global = self::REQUEST)
	{
		return $this->_variable($var_name, $default, $multibyte, $super_global, false);
	}

	/**
	 * 
	 */
	public function raw_variable($var_name, $default, $super_global = self::REQUEST)
	{
		$path = false;

		// deep direct access to multi dimensional arrays
		if (is_array($var_name))
		{
			$path = $var_name;
			// make sure at least the variable name is specified
			if (empty($path))
			{
				return (is_array($default)) ? array() : $default;
			}
			// the variable name is the first element on the path
			$var_name = array_shift($path);
		}

		if (!isset($this->input[$super_global][$var_name]))
		{
			return (is_array($default)) ? array() : $default;
		}
		$var = $this->input[$super_global][$var_name];

		if ($path)
		{
			// walk through the array structure and find the element we are looking for
			foreach ($path as $key)
			{
				if (is_array($var) && isset($var[$key]))
				{
					$var = $var[$key];
				}
				else
				{
					return (is_array($default)) ? array() : $default;
				}
			}
		}

		return $var;
	}

	/**
	* Shortcut method to retrieve SERVER variables.
	*
	* Also fall back to getenv(), some CGI setups may need it (probably not, but
	* whatever).
	*
	* @param	string|array	$var_name		See \request\request_interface::variable
	* @param	mixed			$Default		See \request\request_interface::variable
	*
	* @return	mixed	The server variable value.
	*/
	public function server($var_name, $default = '')
	{
		$multibyte = true;

		if ($this->is_set($var_name, self::SERVER))
		{
			return $this->variable($var_name, $default, $multibyte, self::SERVER);
		}
		else
		{
			$var = getenv($var_name);
			$this->recursive_set_var($var, $default, $multibyte);
			return $var;
		}
	}

	/**
	* Shortcut method to retrieve the value of client HTTP headers.
	*
	* @param	string|array	$header_name	The name of the header to retrieve.
	* @param	mixed			$default		See \request\request_interface::variable
	*
	* @return	mixed	The header value.
	*/
	public function header($header_name, $default = '')
	{
		$var_name = 'HTTP_' . str_replace('-', '_', strtoupper($header_name));
		return $this->server($var_name, $default);
	}

	/**
	* Shortcut method to retrieve $_FILES variables
	*
	* @param string $form_name The name of the file input form element
	*
	* @return array The uploaded file's information or an empty array if the
	* variable does not exist in _FILES.
	*/
	public function file($form_name)
	{
		return $this->variable($form_name, array('name' => 'none'), true, self::FILES);
	}
	
	/**
	 * Request POST variable.
	 *
	 * _read() wrappers to retrieve POST, GET or any REQUEST (both) variable.
	 *
	 * @access public
	 * @param string $var
	 * @param integer $type
	 * @param string $dflt
	 * @return string
	 */
	public function post($var, $type = TYPE_ANY, $dflt = '', $not_null = false)
	{
		if (!$this->super_globals_disabled())
		{
			return $this->_read($var, ($type | TYPE_POST_VARS), $dflt, $not_null);
		}
		else
		{
			$super_global = self::POST;
			$multibyte = false; //UTF-8 ?
			$default = $dflt;
			return $this->_variable($var_name, $default, $multibyte, $super_global, true);
		}
	}
	
	/** ** /
	public function post($var_name, $default, $multibyte = false, $super_global = self::POST)
	{
		return $this->_variable($var_name, $default, $multibyte, $super_global, true);
	}
	/** **/	
	
	/**
	 * Request GET variable.
	 *
	 * _read() wrappers to retrieve POST, GET or any REQUEST (both) variable.
	 *
	 * @access public
	 * @param string $var
	 * @param integer $type
	 * @param string $dflt
	 * @return string
	 */
	public function get($var, $type = TYPE_ANY, $dflt = '', $not_null = false)
	{
		if (!$this->super_globals_disabled())
		{
			return $this->_read($var, ($type | TYPE_GET_VARS), $dflt, $not_null);
		}
		else	
		{
			$super_global = self::GET;
			$multibyte = false; //UTF-8 ?
			$default = $dflt;
			return $this->_variable($var_name, $default, $multibyte, $super_global, true);
		}		
	}
	
	/** ** /
	public function get($var_name, $default, $multibyte = false, $super_global = self::GET)
	{
		return $this->_variable($var_name, $default, $multibyte, $super_global, true);
	}
	/** **/
	
	/**
	 * Request GET or POST variable.
	 *
	 * _read() wrappers to retrieve POST, GET or any REQUEST (both) variable.
	 *
	 * @access public
	 * @param string $var
	 * @param integer $type
	 * @param string $dflt
	 * @return string
	 */
	public function request($var, $type = TYPE_ANY, $dflt = '', $not_null = false)
	{
		if (!$this->super_globals_disabled())
		{
			return $this->_read($var, ($type | TYPE_POST_VARS | TYPE_GET_VARS), $dflt, $not_null);	
		}
		else
		{
			$super_global = self::REQUEST;
			$multibyte = false; //UTF-8 ?
			$default = $dflt;
			return $this->_variable($var_name, $default, $multibyte, $super_global, true);
		}	
	}

	/**
	 * Is POST var?
	 *
	 * Boolean method to check for existence of POST variable.
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	public function is_post($var)
	{
		// Note: _x and _y are used by (at least IE) to return the mouse position at onclick of INPUT TYPE="img" elements.
		return ($this->is_set_post($var) || $this->is_set_post($var.'_x') && $this->is_set_post($var.'_y')) ? 1 : 0;
	}

	/**
	 * Is GET var?
	 *
	 * Boolean method to check for existence of GET variable.
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	public function is_get($var)
	{
		//return isset($_GET[$var]) ? 1 : 0 ;
		return $this->is_set($var, self::GET);
	}

	/**
	 * Is REQUEST (either GET or POST) var?
	 *
	 * Boolean method to check for existence of any REQUEST (both) variable.
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	public function is_request($var)
	{
		return ($this->is_get($var) || $this->is_post($var)) ? 1 : 0;
		//return $this->is_set($var, self::REQUEST);
	}	
	
	/**
	 * Is POST var empty?
	 *
	 * Boolean method to check if POST variable is empty
	 * as it might be set but still be empty.
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	public function is_empty_post($var)
	{
		//return (empty($_POST[$var]) && ( empty($_POST[$var.'_x']) || empty($_POST[$var.'_y']))) ? 1 : 0 ;
		return ($this->is_empty($var, self::POST) && ($this->is_empty($var.'_x', self::POST) || $this->is_empty($var.'_y', self::POST))) ? 1 : 0;		
	}
	/**
	 * Is POST var not empty?
	 *
	 * Boolean method to check if POST variable is empty
	 * as it might be set but still be empty.
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	public function is_not_empty_post($var)
	{
		//return (!empty($_POST[$var]) && ( !empty($_POST[$var.'_x']) || !empty($_POST[$var.'_y']))) ? 1 : 0 ;
		return ($this->is_not_empty($var, self::POST) && ($this->is_not_empty($var.'_x', self::POST) || $this->is_not_empty($var.'_y', self::POST))) ? 1 : 0;		
	}	
	/**
	 * Is GET var empty?
	 *
	 * Boolean method to check if GET variable is empty
	 * as it might be set but still be empty
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	public function is_empty_get($var)
	{
		//return empty($_GET[$var]) ? 1 : 0;
		return $this->is_empty($var, self::GET);
	}
	/**
	 * Is GET var not empty?
	 *
	 * Boolean method to check if GET variable is empty
	 * as it might be set but still be empty
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	public function is_not_empty_get($var)
	{
		//return !empty($_GET[$var]) ? 1 : 0;
		return $this->is_not_empty($var, self::GET);
	}
	/**
	 * Is REQUEST empty (GET and POST) var?
	 *
	 * Boolean method to check if REQUEST (both) variable is empty.
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	public function is_empty_request($var)
	{
		return ($this->is_empty_get($var) && $this->is_empty_post($var)) ? 1 : 0;
	}
	/**
	 * Is REQUEST not empty (GET and POST) var?
	 *
	 * Boolean method to check if REQUEST (both) variable is empty.
	 *
	 * @access public
	 * @param string $var
	 * @return boolean
	 */
	public function is_not_empty_request($var)
	{
		return ($this->is_not_empty_get($var) && $this->is_not_empty_post($var)) ? 1 : 0;
	}	
	/**
	* Checks whether a certain variable was sent via POST.
	* To make sure that a request was sent using POST you should call this function
	* on at least one variable.
	*
	* @param	string	$name	The name of the form variable which should have a
	*							_p suffix to indicate the check in the code that creates the form too.
	*
	* @return	bool			True if the variable was set in a POST request, false otherwise.
	*/
	public function is_set_post($var)
	{		
		if (is_array($var))
		{
			return $this->is_array_set($var, self::POST);
		}
		else
		{
			return $this->is_set($var, self::POST);
		}
	}
	/**
	* Checks whether a certain variable was not sent via POST.
	* To make sure that a request was not sent using POST you should call this function
	* on at least one variable.
	*
	* @param	string	$name	The name of the form variable which should have a
	*							_p suffix to indicate the check in the code that creates the form too.
	*
	* @return	bool			True if the variable was not set in a POST request, false otherwise.
	*/
	public function is_not_set_post($name)
	{
		return $this->is_not_set($name, self::POST);
	}	
	/**
	* Checks whether a certain variable was sent via GET.
	* To make sure that a request was sent using GET you should call this function
	* on at least one variable.
	*
	* @param	string	$name	The name of the form variable which should have a
	*							_p suffix to indicate the check in the code that creates the form too.
	*
	* @return	bool			True if the variable was set in a GET request, false otherwise.
	*/
	public function is_set_get($var)
	{
		if (is_array($var))
		{
			return $this->is_array_set($var, self::GET);
		}
		else
		{
			return $this->is_set($var, self::GET);
		}				
	}	
	/**
	* Checks whether a certain variable was not sent via GET.
	* To make sure that a request was not sent using GET you should call this function
	* on at least one variable.
	*
	* @param	string	$name	The name of the form variable which should have a
	*							_p suffix to indicate the check in the code that creates the form too.
	*
	* @return	bool			True if the variable was not set in a GET request, false otherwise.
	*/
	public function is_not_set_get($name)
	{
		return $this->is_not_set($name, self::GET);
	}
	/*
	*
	*
	*/
	public function post_array()
	{
		return ($this->post_array > 0) ? $this->post_array : 0;
	}	
	/*
	*
	*
	*/
	public function get_array()
	{

		return ($this->get_array > 0) ? $this->get_array : 0;
	}
	/*
	*
	*
	*/
	public function request_array()
	{
		return ($this->request_array > 0) ? $this->request_array : 0;
	}		
	/*
	*
	*
	*/
	public function cookie_array()
	{
		return ($this->cookie_array > 0) ? $this->cookie_array : 0;
	}	
	/*
	*
	*
	*/
	public function server_array()
	{
		return ($this->server_array > 0) ? $this->server_array : 0;
	}	
	/*
	*
	*
	*/
	public function files_array()
	{
		return ($this->files_array > 0) ? $this->files_array : 0;
	}
	/**
	* Checks whether a certain variable is empty in one of the super global
	* arrays.
	*
	* @param	string	$var	Name of the variable
	* @param	mx_request_vars::POST|GET|REQUEST|COOKIE	$super_global
	*							Specifies the super global which shall be checked
	*
	* @return	bool			True if the variable was sent as input
	*/
	public function is_empty($var, $super_global = self::REQUEST)
	{
		return empty($this->input[$super_global][$var]);
	}	
	/**
	* Checks whether a certain variable is not empty in one of the super global
	* arrays.
	*
	* @param	string	$var	Name of the variable
	* @param	mx_request_vars::POST|GET|REQUEST|COOKIE	$super_global
	*							Specifies the super global which shall be checked
	*
	* @return	bool			True if the variable was sent as input
	*/
	public function is_not_empty($var, $super_global = self::REQUEST)
	{
		return !empty($this->input[$super_global][$var]);
	}	
	/**
	* Checks whether a certain variable is set in one of the super global
	* arrays.
	*
	* @param	string	$var	Name of the variable
	* @param	mx_request_vars::POST|GET|REQUEST|COOKIE	$super_global
	*							Specifies the super global which shall be checked
	*
	* @return	bool			True if the variable was sent as input
	*/
	public function is_set($var, $super_global = self::REQUEST)
	{
		return isset($this->input[$super_global][$var]);
	}
	/**
	* Checks whether a certain array of variables are set in one of the super global
	* arrays.
	*
	* @param	string	$var1	Name of the variable
	* @param	string	$var2	Name of the variable	
	* @param	mx_request_vars::POST|GET|REQUEST|COOKIE	$super_global
	*		Specifies the super global which shall be checked
	*
	* @return	bool			True if the variable was sent as input
	*/
	public function is_array_set($arr, $super_global = self::REQUEST)
	{
		$n = count($arr);
		$lim = 4;
		
		for ($i = 0; $i < $n; $i++)
		{		
			foreach($arr as $var)
			{
				$arr[$i] = isset($this->input[$super_global][$var][$i]) ? $this->input[$super_global][$var][$i] : $var;
			}		
			
			if ($i < 3)
			{	
				return isset($this->input[$super_global][$var][0], $this->input[$super_global][$var][2]);
			}
			elseif ($i < 4)
			{	
				return isset($this->input[$super_global][$var][0], $this->input[$super_global][$var][2], $this->input[$super_global][$var][3]);
			}
			elseif ($i < 5)
			{	
				return isset($this->input[$super_global][$var][0], $this->input[$super_global][$var][2], $this->input[$super_global][$var][3], $this->input[$super_global][$var][4]);
			}
			elseif ($lim < $n)
			{	
				print('Warning: Request vars class only accepts a number of arguments in an array: ' . $lim . ', but now are: ' . $n . ' arguments.');
			}
		}
	}	
	/**
	* Checks whether a certain variable is not set in one of the super global
	* arrays.
	*
	* @param	string	$var	Name of the variable
	* @param	mx_request_vars::POST|GET|REQUEST|COOKIE	$super_global
	*							Specifies the super global which shall be checked
	*
	* @return	bool			True if the variable was sent as input
	*/
	public function is_not_set($var, $super_global = self::REQUEST)
	{
		return !isset($this->input[$super_global][$var]);
	}	
	/**
	* Checks whether the current request is an AJAX request (XMLHttpRequest)
	*
	* @return	bool			True if the current request is an ajax request
	*/
	public function is_ajax()
	{
		return $this->header('X-Requested-With') == 'XMLHttpRequest';
	}

	/**
	* Checks if the current request is happening over HTTPS.
	*
	* @return	bool			True if the request is secure.
	*/
	public function is_secure()
	{
		$https = $this->server('HTTPS');
		$https = $this->server('HTTP_X_FORWARDED_PROTO') === 'https' ? 'on' : $https;
		return !empty($https) && $https !== 'off';
	}

	/**
	* Returns all variable names for a given super global
	*
	* @param	mx_request_vars::POST|GET|REQUEST|COOKIE	$super_global
	*					The super global from which names shall be taken
	*
	* @return	array	All variable names that are set for the super global.
	*					Pay attention when using these, they are unsanitised!
	*/
	public function variable_names($super_global = self::REQUEST)
	{
		if (!isset($this->input[$super_global]))
		{
			return array();
		}

		return array_keys($this->input[$super_global]);
	}

	/**
	* Helper function used by variable() and untrimmed_variable().
	*
	* @param	string|array	$var_name	The form variable's name from which data shall be retrieved.
	* 										If the value is an array this may be an array of indizes which will give
	* 										direct access to a value at any depth. E.g. if the value of "var" is array(1 => "a")
	* 										then specifying array("var", 1) as the name will return "a".
	* @param	mixed			$default	A default value that is returned if the variable was not set.
	* 										This function will always return a value of the same type as the default.
	* @param	bool			$multibyte	If $default is a string this parameter has to be true if the variable may contain any UTF-8 characters
	*										Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks
	* @param	mx_request_vars::POST|GET|REQUEST|COOKIE	$super_global
	* 										Specifies which super global should be used
	* @param	bool			$trim		Indicates whether trim() should be applied to string values.
	*
	* @return	mixed	The value of $_REQUEST[$var_name] run through {@link set_var set_var} to ensure that the type is the
	*					the same as that of $default. If the variable is not set $default is returned.
	*/
	protected function _variable($var_name, $default, $multibyte = false, $super_global = self::REQUEST, $trim = true)
	{
		$var = $this->raw_variable($var_name, $default, $super_global);

		// Return prematurely if raw variable is empty array or the same as
		// the default. Using strict comparison to ensure that one can't
		// prevent proper type checking on any input variable
		if ($var === array() || $var === $default)
		{
			return $var;
		}

		$this->recursive_set_var($var, $default, $multibyte, $trim);

		return $var;
	}

	/**
	*
	*/
	public function get_super_global($super_global = self::REQUEST)
	{
		return $this->input[$super_global];
	}

	/**
	 *
	 */
	public function escape($var, $multibyte)
	{
		if (is_array($var))
		{
			$result = array();
			foreach ($var as $key => $value)
			{
				$this->set_var($key, $key, gettype($key), $multibyte);
				$result[$key] = $this->escape($value, $multibyte);
			}
			$var = $result;
		}
		else
		{
			$this->set_var($var, $var, 'string', $multibyte);
		}

		return $var;
	}

	/**
	* Check GET POST vars exists
	*/
	function check_http_var_exists($var_name, $empty_var = false)
	{
		if ($empty_var)
		{
			if (isset($_GET[$var_name]) || isset($_POST[$var_name]))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			if (!empty($_GET[$var_name]) || !empty($_POST[$var_name]))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		return false;
	}

	/**
	* Check variable value against default array
	*/
	function check_var_value($var, $var_array, $var_default = false)
	{
		if (!is_array($var_array) || empty($var_array))
		{
			return $var;
		}
		$var_default = (($var_default === false) ? $var_array[0] : $var_default);
		$var = in_array($var, $var_array) ? $var : $var_default;
		return $var;
	}

	/**
	* Set variable $result to a particular type.
	*
	* @param mixed	&$result		The variable to fill
	* @param mixed	$var			The contents to fill with
	* @param mixed	$type			The variable type. Will be used with {@link settype()}
	* @param bool	$multibyte		Indicates whether string values may contain UTF-8 characters.
	* 								Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks.
	* @param bool	$trim			Indicates whether trim() should be applied to string values.
	* 								Default is true.
	*/
	public function set_var(&$result, $var, $type, $multibyte = false, $trim = true)
	{
		settype($var, $type);
		$result = $var;

		if ($type == 'string')
		{
			$result = str_replace(array("\r\n", "\r", "\0"), array("\n", "\n", ''), $result);

			if ($trim)
			{
				$result = trim($result);
			}

			$result = htmlspecialchars($result, ENT_COMPAT, 'UTF-8');

			if ($multibyte)
			{
				if (is_array($result))
				{
					foreach ($result as $key => $string)
					{
						if (is_array($string))
						{
							foreach ($string as $_key => $_string)
							{
								$result = $result[$key][$_string];
							}
						}
						else
						{
							$result = $strings[$key];
						}
					}
				}
			}

			if (!empty($result))
			{
				// Make sure multibyte characters are wellformed
				if ($multibyte)
				{
					if (!preg_match('/^./u', $result))
					{
						$result = '';
					}
				}
				else
				{
					// no multibyte, allow only ASCII (0-127)
					$result = preg_replace('/[\x80-\xFF]/', '?', $result);
				}
			}
		}
	}

	/**
	* Recursively sets a variable to a given type using {@link set_var set_var}
	*
	* @param	string	$var		The value which shall be sanitised (passed by reference).
	* @param	mixed	$default	Specifies the type $var shall have.
	* 								If it is an array and $var is not one, then an empty array is returned.
	* 								Otherwise var is cast to the same type, and if $default is an array all
	* 								keys and values are cast recursively using this function too.
	* @param	bool	$multibyte	Indicates whether string keys and values may contain UTF-8 characters.
	* 								Default is false, causing all bytes outside the ASCII range (0-127) to
	* 								be replaced with question marks.
	* @param	bool	$trim		Indicates whether trim() should be applied to string values.
	* 								Default is true.
	*/
	public function recursive_set_var(&$var, $default, $multibyte, $trim = true)
	{
		if (is_array($var) !== is_array($default))
		{
			$var = (is_array($default)) ? array() : $default;
			return;
		}

		if (!is_array($default))
		{
			$type = gettype($default);
			$this->set_var($var, $var, $type, $multibyte, $trim);
		}
		else
		{
			// make sure there is at least one key/value pair to use get the
			// types from
			if (empty($default))
			{
				$var = array();
				return;
			}

			list($default_key, $default_value) = each($default);
			$key_type = gettype($default_key);

			$_var = $var;
			$var = array();

			foreach ($_var as $k => $v)
			{
				$this->set_var($k, $k, $key_type, $multibyte);

				$this->recursive_set_var($v, $default_value, $multibyte, $trim);
				$var[$k] = $v;
			}
		}
	} 
}	// class RequestVars
?>
