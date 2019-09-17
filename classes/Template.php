<?php

/**
 * @package AutoIndex
 *
 * @copyright Copyright (C) 2002-2005 Justin Hagstrom
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
 * Reads the file contents, then parses comments and translated words.
 *
 * First step in parsing a template file. Used on all templates:
 * - global header
 * - global footer
 * - table header
 * - table footer
 * - each_file
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.3 (February 02, 2005)
 * @package AutoIndex
 */
class Template
{
	/**
	 * @var string The final output
	 */
	protected $out;
	
	/**
	 * @param array $m The array given by preg_replace_callback()
	 * @return string Looks up $m[1] in word list and returns match
	 */
	private static function callback_words($m)
	{
		global $words;
		return $words -> __get(strtolower($m[1]));
	}
	
	/**
	 * @param array $m The array given by preg_replace_callback()
	 * @return string The parsed template of filename $m[1]
	 */
	private static function callback_include($m)
	{
		$temp = new Template($m[1]);
		return $temp -> __toString();
	}
	
	/**
	 * @param array $m The array given by preg_replace_callback()
	 * @return string The setting for the config value $m[1]
	 */
	private static function callback_config($m)
	{
		global $config;
		return $config -> __get(strtolower($m[1]));
	}
	
	/**
	 * Parses the text in $filename and sets the result to $out. We cannot
	 * use ExceptionDisplay here if there is an error, since it uses the
	 * template system.
	 *
	 * Steps to parse the template:
	 * - remove comments
	 * - replace {info} variables
	 * - replace {words} strings
	 * - replace {config} variables
	 * - include other files when we see the {include} statement
	 *
	 * @param string $filename The name of the file to parse
	 */
	public function __construct($filename)
	{
		global $config, $dir, $subdir;
		$full_filename = $config -> __get('template') . $filename;
		if (!@is_file($full_filename))
		{
			throw new ExceptionFatal('Template file <em>'
			. Url::html_output($full_filename) . '</em> cannot be found.');
		}
		
		//read raw file contents
		$contents = @file_get_contents($full_filename);
		if ($contents === false)
		{
			throw new ExceptionFatal('Template file <em>'
			. Url::html_output($full_filename) . '</em> could not be opened for reading.');
		}
		
		//remove comments
		$contents = preg_replace('#/\*.*?\*/#s', '', $contents);
		
		//replace info variables and word strings from language file
		$tr = array(
			'{info:dir}' => (isset($dir) ? Url::html_output($dir) : ''),
			'{info:subdir}' => (isset($subdir) ? Url::html_output($subdir) : ''),
			'{info:version}' => VERSION,
			'{info:page_time}' => round((microtime(true) - START_TIME) * 1000, 1));
		$contents = preg_replace_callback('/\{\s*words?\s*:\s*(.+)\s*\}/Ui',
			array('self', 'callback_words'), strtr($contents, $tr));
		
		//replace {config} variables
		$contents = preg_replace_callback('/\{\s*config\s*:\s*(.+)\s*\}/Ui',
			array('self', 'callback_config'), $contents);

		//parse includes
		$this -> out = preg_replace_callback('/\{\s*include\s*:\s*(.+)\s*\}/Ui',
			array('self', 'callback_include'), $contents);
	}
	
	/**
	 * @return string The HTML text of the parsed template
	 */
	public function __toString()
	{
		return $this -> out;
	}
}

?>
