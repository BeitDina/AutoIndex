<?php
/**
 * Handles all requests by the browser. This is the only file that can be
 * accessed directly.
 *
 * @package AutoIndex
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>, FlorinCB <orynider@users.sourceforge.net>
 * @version 2.4.5 (January 01, 2019)
 *
 * @copyright Copyright (C) 2002-2007 Justin Hagstrom
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 *
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

@ini_set('display_errors', '1');
//@error_reporting(E_ALL & ~E_NOTICE); 
@session_cache_expire (1440);
@set_time_limit (1500);

/**
 * OPTIONAL SETTINGS:
 */

//filenames and paths for configuration related files
/*EDIT*/$CONFIG_PATH = './';
/*EDIT*/define('CONFIG_STORED', $CONFIG_PATH . 'AutoIndex.conf.php');
/*EDIT*/define('CONFIG_GENERATOR', $CONFIG_PATH . 'config.php');


//paths for files that will be included
/*EDIT*/define('PATH_TO_CLASSES', $CONFIG_PATH . 'classes/');
/*EDIT*/define('PATH_TO_LANGUAGES', $CONFIG_PATH . 'languages/');
define('LANGUAGE_FILE_EXT', '.txt');

//filenames of template files
define('GLOBAL_HEADER', 'global_header.tpl');
define('GLOBAL_FOOTER', 'global_footer.tpl');
define('TABLE_HEADER', 'table_header.tpl');
define('TABLE_FOOTER', 'table_footer.tpl');
define('EACH_FILE', 'each_file.tpl');

/**
 * When ENABLE_CACHE is true, the indexes of directories will be stored in
 * files in the folder CACHE_STORAGE_DIR. You will notice a speed improvement
 * when viewing folders that contain a few thousand files. However, the contents
 * of the indexed folders will not be updated until you delete the cache file.
 */
define('ENABLE_CACHE', false);

/**
 * This is the folder cache data will be stored in. PHP needs write permission
 * in this directory. You can use an absolute path or a relative path, just
 * make sure there is a slash at the end.
 */
/*EDIT*/define('CACHE_STORAGE_DIR', '../AutoIndex/cache/');
/**
 * Format to display dates in.
 * @see date()
 */
define('DATE_FORMAT', 'Y-M-d');

/**
 * Sets debug mode. Off (false) by default.
 */
define('DEBUG', false);

/* END OPTIONAL SETTINGS */


/** The time this script began to execute. */
define('START_TIME', microtime(true));

/** Level for disabled/banned accounts. */
define('BANNED', -1);

/** Level for Guest users (users who are not logged in). */
define('GUEST', 0);

/** Level for regular user accounts. */
define('USER', 1);

/** Level for moderator ("super user") accounts. */
define('MODERATOR', 2);

/** Level for Admin users. */
define('ADMIN', 3);

/** 
 * Minimum user level allowed to upload files.
 * Use the ADMIN, MODERATOR, USER, GUEST constants.
 * GUEST will allow non-logged-in users to upload.
 */
/*EDIT*/define('LEVEL_TO_UPLOAD', ADMIN);
//define('LEVEL_TO_UPLOAD', USER);

/** The version of AutoIndex PHP Script (the whole release, not based on individual files). */
define('VERSION', '2.4.5-REV.2');

/**
 * This must be set to true for other included files to run. Setting it to
 * false could be used to temporarily disable the script.
 */
define('IN_AUTOINDEX', true);

//compensate for compressed output set in php.ini
if (ini_get('zlib.output_compression') == '1')
{
	header('Content-Encoding: gzip');
}

/*
 * Uncomment the following code to turn on strict XHTML 1.1 compliance in
 * users' browsers. If you do this, make sure any changes you make to the
 * template do not break XHTML 1.1 compliance.
 */
/*if (!empty($_SERVER['HTTP_ACCEPT']) && preg_match('#application/(xhtml\+xml|\*)#i', $_SERVER['HTTP_ACCEPT']))
{
	header('Content-Type: application/xhtml+xml');
}*/

session_name('AutoIndex2');
session_start();

//echo 'PHP_VERSION: '.PHP_VERSION;
/**
 * Formats $text within valid XHTML 1.1 tags and doctype.
 *
 * @param string $text
 * @param string $title
 * @return string
 */
function simple_display($text, $title = 'Error on Page')
{
	return '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>' . $title . '</title>
	<style type="text/css" title="AutoIndex Default">
		html, body
		{
			font-family: verdana, lucidia, sans-serif;
			font-size: 13px;
			background-color: #F0F0F0;
			color: #000000;
		}
	</style>
</head>
<body>
<p>' . $text . '</p></body></html>

<!--

Powered by AutoIndex PHP Script (version ' . VERSION . ')
Copyright (C) 2002-2007 Justin Hagstrom, (C) 2019-2021 FlorinCB
http://autoindex.sourceforge.net/

-->
';
}

/**
 * This function is automatically called by PHP when an undefined class is
 * called.
 *
 * A file with the classname followed by .php is included to load the class.
 * The class should start with an upper-case letter with each new word also in
 * upper-case. The filename must match the class name (including case).
 *
 * @param string $class The name of the undefined class
 */
function __autoload($class)
{
	if ($class != 'self')
	{
		$file = PATH_TO_CLASSES . $class . '.php';
		/** Try to load the class file. */
		if (!include_once($file))
		{
			die(simple_display('Error including file <em>' . htmlentities($file) . '</em> - cannot load class.'));
		}
	}
}

/*
* Instantiate the mx_request_vars class
* make sure to do before it's ever used
*/
$request = new RequestVars('', false);

// this is needed to prevent unicode normalization
$super_globals_disabled = $request->super_globals_disabled();

// enable super globals to get literal value
if (!$super_globals_disabled)
{
	//$request->disable_super_globals();
}



/*
*/
$_GET = array_change_key_case($_GET, CASE_LOWER);
$_POST = array_change_key_case($_POST, CASE_LOWER);


/**
 * This is used to report a fatal error that we cannot display with the Display
 * class. All Exceptions used in AutoIndex should inherit from this class.
 *
 * @package AutoIndex
 */
class ExceptionFatal extends Exception {}
try
{
	//now we need to include either the stored settings, or the config generator:
	if (is_file(CONFIG_STORED))
	{
		if (!is_readable(CONFIG_STORED))
		{
			throw new ExceptionFatal('Make sure PHP has permission to read the file <em>' . Url::html_output(CONFIG_STORED) . '</em>');
		}
		$config = new ConfigData(CONFIG_STORED);
	}
	else if (is_file(CONFIG_GENERATOR))
	{
		/** Include the config generator so a new config file can be created. */
		if (!include_once(CONFIG_GENERATOR))
		{
			throw new ExceptionFatal('Error including file <em>' . Url::html_output(CONFIG_GENERATOR) . '</em>');
		}
		exit();
		die('exit.. ?');
	}
	else
	{
		throw new ExceptionFatal('Neither <em>' . Url::html_output(CONFIG_GENERATOR) . '</em> nor <em>' . Url::html_output(CONFIG_STORED) . '</em> could be found.');
	}
	
	
	//find and store the user's IP address and hostname:
	$ip = (!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'N/A');
	if (!empty($_SESSION['host']))
	{
		$host = $_SESSION['host'];
	}
	else
	{
		$_SESSION['host'] = $host = gethostbyaddr($ip);
	}

	
	//Create a language object:
	$words = new Language();
	
	/*
	* Instantiate the Mobile Device Detect class
	* make sure to do before it's ever used
	*/
	$mobile_device_detect = new MobileDeviceDetect();
	$status = $mobile_device_detect->mobile_device_detect();

	//Create a logging object:
	$log = new Logging($config -> __get('log_file'));
	
	/**
	* Go through each config setting, and set a constant with each setting's
	 * name to either true or false depending on if the config setting is
	 * enabled.
	 */
	foreach ($config as $key => $item)
	{
		$key = strtoupper($key);
		if (defined($key))
		{
			throw new ExceptionFatal(Url::html_output($key) . ' is already defined in <em>' . basename(Url::html_output($_SERVER['PHP_SELF'])) . '</em>, and should not be in the config file.');
		}
		define($key, ($item != 'false' && $item != '0'));
	}
	
	
	//make sure all required settings are set in the config file
	foreach (array('base_dir', 'icon_path', 'flag_path', 'language', 'template', 'log_file', 'description_file', 'user_list', 'download_count', 'hidden_files', 'banned_list', 'show_dir_size', 'use_login_system', 'force_download', 'search_enabled', 'anti_leech', 'entries_per_page', 'must_login_to_download', 'archive', 'days_new', 'thumbnail_height', 'bandwidth_limit', 'md5_show', 'parse_htaccess') as $set)
	{
		if (!defined(strtoupper($set)))
		{
			throw new ExceptionFatal('Required setting <em>' . $set . '</em> is not set in <em>' . Url::html_output(CONFIG_STORED) . '</em>');
		}
	}
	
	
	/**
	* From this point on, we can // throw ExceptionDisplay rather than
	 * Exception since all the configuration is done.
	 */
	
	$b_list = $only_these_ips = $banned_ips = array();
	if (BANNED_LIST && is_file($config -> __get('banned_list')))
	//make sure the user is not banned
	{
		$b_list = file($config -> __get('banned_list'));
		if ($b_list === false)
		{
			throw new ExceptionDisplay('Error reading from banned_list file.');
		}
		for ($i = 0; $i < count($b_list); $i++)
		{
			$b_list[$i] = rtrim($b_list[$i], "\r\n");
			if (ConfigData::line_is_comment($b_list[$i]))
			{
				continue;
			}
			if ($b_list[$i]{0} === ':')
			{
				$only_these_ips[] = substr($b_list[$i], 1);
			}
			else
			{
				$banned_ips[] = $b_list[$i];
			}
		}
		if (count($only_these_ips) > 0)
		{
			if (!(DirectoryList::match_in_array($ip, $only_these_ips) || DirectoryList::match_in_array($host, $only_these_ips)))
			{
				throw new ExceptionDisplay($words -> __get('the administrator has blocked your ip address or hostname') . '.');
			}
		}
		else if (DirectoryList::match_in_array($ip, $banned_ips) || DirectoryList::match_in_array($host, $banned_ips))
		{
			throw new ExceptionDisplay($words -> __get('the administrator has blocked your ip address or hostname') . '.');
		}
	}
	
	$show_only_these_files = $hidden_files = array();
	if (HIDDEN_FILES && is_file($config -> __get('hidden_files')))
	//store the hidden file list in $hidden_list
	{
		$hidden_list = file($config -> __get('hidden_files'));
		if ($hidden_list === false)
		{
			throw new ExceptionDisplay('Error reading from "hidden_files" file.');
		}
		for ($i = 0; $i < count($hidden_list); $i++)
		{
			$hidden_list[$i] = rtrim($hidden_list[$i], "\r\n");
			if (ConfigData::line_is_comment($hidden_list[$i]))
			{
				continue;
			}
			if ($hidden_list[$i]{0} === ':')
			{
				$show_only_these_files[] = substr($hidden_list[$i], 1);
			}
			else
			{
				$hidden_files[] = $hidden_list[$i];
			}
		}
	}
	
	
	//size of the "chunks" that are read at a time from the file (when $force_download is on)
	$speed = (BANDWIDTH_LIMIT ? $config -> __get('bandwidth_limit') : 8);
	
	if (DOWNLOAD_COUNT)
	{
		if (!is_file($config -> __get('download_count')))
		{
			$h = fopen($config -> __get('download_count'), 'wb');
			if ($h === false)
			{
				throw new ExceptionDisplay('Could not open download count file for writing.' 	. ' Make sure PHP has write permission to this file.');
			}
			fclose($h);
		}
		$downloads = new ConfigData($config -> __get('download_count'));
	}
	
	//create a user object:
	$log_login = false;
	if (USE_LOGIN_SYSTEM && !empty($_POST['username']) && ($_POST['username'] != '') && ($_POST['password'] != ''))
	{
		$you = new UserLoggedIn($_POST['username'], sha1($_POST['password']));
		$log_login = true;
		$_SESSION['password'] = sha1($_POST['password']);
		unset($_POST['password']);
		$_SESSION['username'] = $_POST['username'];
	}
	else if (USE_LOGIN_SYSTEM && !empty($_SESSION['username']))
	{
		$you = new UserLoggedIn($_SESSION['username'], $_SESSION['password']);
	}
	else
	{
		$you = new User();
		if (MUST_LOGIN_TO_DOWNLOAD && USE_LOGIN_SYSTEM)
		{
			$str = '<p>You must login to view and download files.</p>'
			. '<table border="0" cellpadding="8" cellspacing="0">'
			. '<tr class="paragraph"><td class="autoindex_td">'
			.  $you -> login_box() . '</td></tr></table>';
			echo new Display($str);
			die();
		}
	}
	
	//set the logged in user's home directory:
	$dir = Item::make_sure_slash((($you -> home_dir == '') ? $config -> __get('base_dir') : $you -> home_dir));
	$config -> set('base_dir', $dir);
	$subdir = '';
	
	if (!empty($_GET['dir']))
	{
		$dir .= Url::clean_input($_GET['dir']);
		$dir = Item::make_sure_slash($dir);
		if (!is_dir($dir))
		{
			header('HTTP/1.0 404 Not Found');
			$_GET['dir'] = ''; //so the "continue" link will work
			throw new ExceptionDisplay('The directory <em>' . Url::html_output($dir) . '</em> does not exist.');
		}
		$subdir = substr($dir, strlen($config -> __get('base_dir')));
		if (!empty($_GET['file']) && ($file = $_GET['file']))
		{
			while (preg_match('#\\\\|/$#', $file))
			//remove all slashes from the end of the name
			{
				$file = substr($file, 0, -1);
			}
			$file = Url::clean_input($file);
			if (!is_file($dir . $file))
			{
				header('HTTP/1.0 404 Not Found');
				throw new ExceptionDisplay('The file <em>' . Url::html_output($file) . '</em> does not exist.');
			}
			if (ANTI_LEECH && !!empty($_SESSION['ref']) && (!!empty($_SERVER['HTTP_REFERER']) || stripos($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) === false))
			{
				$log -> add_entry('Leech Attempt');
				$self = $_SERVER['SERVER_NAME'] . Url::html_output($_SERVER['PHP_SELF']) . '?dir=' . Url::translate_uri($subdir);
				throw new ExceptionDisplay('<h3>This PHP Script has an Anti-Leech feature turned on.</h3>' . ' <p>Make sure you are accessing this file directly from <a class="autoindex_a" href="http://' . $self . '">http://' . $self . '</a></p>');
			}
			$log -> add_entry($file);
			if (DOWNLOAD_COUNT)
			{
				$downloads -> add_one($dir . $file);
			}
			$url = new Url($dir . $file, true);
			$url -> download();
		}
	}
	
	if ($log_login)
	{
		$log -> add_entry('Successful login (Username: ' . $_SESSION['username'] . ')');
	}
	
	if (DESCRIPTION_FILE)
	{
		$descriptions = new ConfigData((is_file($config -> __get('description_file')) ? $config -> __get('description_file') : false));
	}
	
	if (PARSE_HTACCESS)
	{
		//parse .htaccess file(s)
		new Htaccess($dir, '.htaccess');
	}
	
	if (MD5_SHOW && !empty($_GET['md5']) && $_GET['md5'] != '')
	{
		$file = $dir . Url::clean_input($_GET['md5']);
		if (!is_file($file))
		{
			header('HTTP/1.0 404 Not Found');
			throw new ExceptionDisplay('Cannot calculate md5sum: the file <em>' . Url::html_output($file) . '</em> does not exist.');
		}
		$size = (int)filesize($file);
		if ($size <= 0 || $size / 1048576 > $config -> __get('md5_show'))
		{
			throw new ExceptionDisplay('Empty file, or file too big to calculate the' . 'md5sum of (according to the $md5_show variable).');
		}
		die(simple_display(md5_file($file), 'md5sum of ' . Url::html_output($file)));
	}
	
	if (THUMBNAIL_HEIGHT && isset($_GET['thumbnail']))
	{
		$fn = Url::clean_input($_GET['thumbnail']);
		if ($fn == '')
		{
			die();
		}
		echo new Image($fn);
	}
	
	if (ARCHIVE && isset($_GET['archive']))
	{
		$log -> add_entry('Directory archived');
		$outfile = Item::get_basename($subdir);
		if ($outfile == '' || $outfile == '.')
		{
			$outfile = 'base_dir';
		}
		$mime = new MimeType('.tar'); 
		header('Content-Type: ' . $mime -> __toString());
		header('Content-Disposition: attachment; filename="' . $outfile . '.tar"');
		set_time_limit(0);
		$list = new DirectoryList($dir);
		$tar = new Tar($list, $outfile, strlen($dir));
		die();
	}
	
	if (THUMBNAIL_HEIGHT && isset($_GET['thm']))
	{
		$fn = Url::clean_input($_GET['thm']);
		if ($fn == '')
		{
			die();
		}
		echo new Stream($fn);
	}
	
	//set the sorting mode:
	if (!empty($_GET['sort']) && $_GET['sort'] != '')
	{
		$_SESSION['sort'] = $_GET['sort'];
	}
	else if (!!empty($_SESSION['sort']))
	{
		$_SESSION['sort'] = 'filename'; //default sort mode
	}
	
	//set the sorting order:
	if (!empty($_GET['sort_mode']) && ($_GET['sort_mode'] == 'a' || $_GET['sort_mode'] == 'd'))
	{
		$_SESSION['sort_mode'] = $_GET['sort_mode'];
	}
	else if (!!empty($_SESSION['sort_mode']))
	{
		$_SESSION['sort_mode'] = 'a'; //default sort order
	}
	
	if (count($_FILES) > 0)
	//deal with any request to upload files:
	{
		$upload = new Upload($you); //the constructor checks if you have permission to upload
		$upload -> do_upload();
	}
	
	if (USE_LOGIN_SYSTEM)
	{
		if (!empty($_GET['logout']) && $_GET['logout'] == 'true')
		{
			$you -> logout();
		}
		else if (!empty($_GET['action']) && $_GET['action'] != '')
		{
			$admin = new Admin($you); //the constructor checks if you really are an admin
			$admin -> action($_GET['action']);
		}
	}
	
	if (ANTI_LEECH && !!empty($_SESSION['ref']))
	{
		$_SESSION['ref'] = true;
	}
	
	$search_log = '';
	if (SEARCH_ENABLED && !empty($_GET['search']) && ($_GET['search'] != '') && ($_GET['search_mode'] != ''))
	{
		$s = Url::clean_input($_GET['search']);
		$dir_list = new Search($s, $dir, $_GET['search_mode']);
		$search_log = "Search: $s";
	}
	else if (ENABLE_CACHE)
	{
		$cache = CACHE_STORAGE_DIR . strtr($dir, '\/:', '---'); //path to cache file
		if (is_file($cache))
		{
			$contents = file_get_contents($cache);
			if ($contents === false)
			{
				throw new ExceptionDisplay('Cannot open cache file for reading. Make sure PHP has read permission for these files.');
			}
			$dir_list = unserialize($contents);
		}
		else
		{
			$dir_list = new DirectoryListDetailed($dir);
			if (!is_dir(CACHE_STORAGE_DIR))
			{
				if (!Admin::mkdir_recursive(CACHE_STORAGE_DIR))
				//Attempt to create the directory. If it fails, tell the user to manually make the folder.
				{
					throw new ExceptionDisplay('Please create the directory <em>' . Url::html_output(CACHE_STORAGE_DIR) . '</em> so cache files can be written.');
				}
			}
			$h = fopen($cache, 'wb');
			if ($h === false)
			{
				throw new ExceptionDisplay('Cannot write to cache file. Make sure PHP has write permission in the cache directory.');
			}
			fwrite($h, serialize($dir_list));
			fclose($h);
		}
	}
	else
	{
		$page = ((ENTRIES_PER_PAGE && !empty($_GET['page'])) ? (int)$_GET['page'] : 1);
		$dir_list = new DirectoryListDetailed($dir, $page);
		$max_page = (ENTRIES_PER_PAGE ? (ceil($dir_list -> total_items() / $config -> __get('entries_per_page'))) : 1);
	}
	$log -> add_entry($search_log);
	$str = $dir_list -> __toString();
	echo new Display($str);
	echo $mobile_device_detect->detect()->getInfo();
}
catch (ExceptionDisplay $e)
{
	echo $e;
}
catch (Exception $e)
{
	echo simple_display($e -> getMessage());
}
?>
