<?php

/**
 * @package AutoIndex
 *
 * @copyright Copyright (C) 2002-2006 Justin Hagstrom
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

if (!defined('IN_AUTOINDEX') || !IN_AUTOINDEX)
{
	die();
}

/**
 * Represents a language file.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.0 (January 01, 2006)
 * @package AutoIndex
 */
class Language
{
	/**
	 * @var ConfigData Contains the translation data from the language file
	 */
	private $translation_data;
	
	/**
	 * Returns a list of all files in $path that match the filename format
	 * of language files.
	 *
	 * There are two valid formats for the filename of a language file. The
	 * standard way is the language code then the .txt extension. You can
	 * also use the language code followed by an underscore then the
	 * country code. The second format would be used for dialects of
	 * languages. For example pt.txt would be Portuguese, and pt_BR.txt
	 * would be Brazilian Portuguese. The filenames are case insensitive.
	 *
	 * @param string $path The directory to read from
	 * @return array The list of valid language files (based on filename)
	 */
	public static function get_all_langs($path)
	{
		if (($hndl = @opendir($path)) === false)
		{
			return false;
		}
		$list = array();
		while (($file = readdir($hndl)) !== false)
		{
			if (@is_file($path . $file) && preg_match('/^[a-z]{2}(_[a-z]{2})?' . preg_quote(LANGUAGE_FILE_EXT, '/') . '$/i', $file))
			{
				$list[] = $file;
			}
		}
		closedir($hndl);
		for ($i = 0; $i < count($list); $i++)
		{
			//remove the file extention from each language code
			$list[$i] = substr($list[$i], 0, -strlen(LANGUAGE_FILE_EXT));
		}
		return $list;
	}
	
	/**
	 * @return string The code for the language to load
	 *
	 * First tries to use the default of the user's browser, and then tries
	 * the default in the config file.
	 */
	private function get_current_lang()
	{
		//try to detect the default language of the user's browser
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		//e.g. "en-us,en;q=0.5"
		{
			$available_langs = self::get_all_langs(PATH_TO_LANGUAGES);
			$accept_lang_ary = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);	
			if ($available_langs !== false)
			{
				$pref = array(); //user's preferred languages
				foreach ($accept_lang_ary as $lang)
				{
					$lang_array = explode(';q=', trim($lang));
					$q = (isset($lang_array[1]) ? trim($lang_array[1]) : 1); //preference value
					$pref[trim($lang_array[0])] = (float) $q;
				}
				arsort($pref);
				//find the first match that is available:
				foreach ($pref as $lang => $q)
				{
					//replace line string to language file downscroll
					$lang = isset($_GET['lang']) ? str_replace('-', '_', $_GET['lang']) : str_replace('-', '_', $lang);
					if (file_exists(@realpath(PATH_TO_LANGUAGES . $lang . LANGUAGE_FILE_EXT)))
					{
						return $lang;
					}
					elseif (in_array($lang, $available_langs))
					{
						return $lang;
					}
				}
			}
		}
		//the browser has no preferences set, so use the config's default
		global $config;
		return $config -> __get('language');
	}
	
	/**
	 * Creates a new language object. First tries to use the default of
	 * the user's browser, and then tries the default in the config file.
	 */
	public function __construct()
	{
		$lang_file = PATH_TO_LANGUAGES . $this -> get_current_lang() . LANGUAGE_FILE_EXT;
		if (!@is_readable($lang_file))
		{
			throw new ExceptionFatal('Cannot read from language file: <em>'  . Url::html_output($lang_file) . '</em>');
		}
		//load the file as a tab-separated object
		$this -> translation_data = new ConfigData($lang_file);
	}
	
	/**
	 * @param string $name The word to look for
	 * @return bool True if $name is set in the translation file
	 */
	public function is_set($name)
	{
		return $this -> translation_data -> is_set($name);
	}
	
	/**
	 * @param string $var The key to look for (the keyword)
	 * @return string The value $name points to (its translation)
	 */
	public function __get($var)
	{
		if ($this -> translation_data -> is_set($var))
		{
			return $this -> translation_data -> __get($var);
		}
		throw new ExceptionDisplay('Variable <em>' . Url::html_output($var) . '</em> not set in Language file.');
	}
}

?>