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
 * Reads information stored in files, where the key and data are separated by a
 * tab.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.2 (January 13, 2005)
 * @package AutoIndex
 */
class ConfigData implements Iterator
{
	/**
	 * @var array A list of all the settings
	 */
	private $config;
	
	/**
	 * @var string The name of the file to read the settings from
	 */
	private $filename;
	
	//begin implementation of Iterator
	/**
	 * @var bool
	 */
	private $valid;
	
	/**
	 * @return string
	 */
	public function current()
	{
		return current($this -> config);
	}
	
	/**
	 * Increments the internal array pointer, and returns the new value.
	 *
	 * @return string
	 */
	public function next()
	{
		$t = next($this -> config);
		if ($t === false)
		{
			$this -> valid = false;
		}
		return $t;
	}
	
	/**
	 * Sets the internal array pointer to the beginning.
	 */
	public function rewind()
	{
		reset($this -> config);
	}
	
	/**
	 * @return bool
	 */
	public function valid()
	{
		return $this -> valid;
	}
	
	/**
	 * @return string
	 */
	public function key()
	{
		return key($this -> config);
	}
	//end implementation of Iterator
	
	/**
	 * @param string $line The line to test
	 * @return bool True if $line starts with characters that mean it is a comment
	 */
	public static function line_is_comment($line)
	{
		$line = trim($line);
		return (($line == '') || preg_match('@^(//|<\?|\?>|/\*|\*/|#)@', $line));
	}
	
	/**
	 * @param string $file The filename to read the data from
	 */
	public function __construct($file)
	{
		if ($file === false)
		{
			return;
		}
		$this -> valid = true;
		$this -> filename = $file;
		$contents = @file($file);
		if ($contents === false)
		{
			throw new ExceptionFatal('Error reading file <em>'
			. Url::html_output($file) . '</em>');
		}
		foreach ($contents as $i => $line)
		{
			$line = rtrim($line, "\r\n");
			if (self::line_is_comment($line))
			{
				continue;
			}
			$parts = explode("\t", $line, 2);
			if (count($parts) !== 2 || $parts[0] == '' || $parts[1] == '')
			{
				throw new ExceptionFatal('Incorrect format for file <em>'
				. Url::html_output($file) . '</em> on line ' . ($i + 1)
				. '.<br />Format is "variable name[tab]value"');
			}
			if (isset($this -> config[$parts[0]]))
			{
				throw new ExceptionFatal('Error in <em>'
				. Url::html_output($file) . '</em> on line ' . ($i + 1)
				. '.<br />' . Url::html_output($parts[0])
				. ' is already defined.');
			}
			$this -> config[$parts[0]] = $parts[1];
		}
	}
	
	/**
	 * $config[$key] will be set to $info.
	 *
	 * @param string $key
	 * @param string $info
	 */
	public function set($key, $info)
	{
		$this -> config[$key] = $info;
	}
	
	/**
	 * This will look for the key $item, and add one to the $info (assuming
	 * it is an integer).
	 *
	 * @param string $item The key to look for
	 */
	public function add_one($item)
	{
		if ($this -> is_set($item))
		{
			$h = @fopen($this -> filename, 'wb');
			if ($h === false)
			{
				throw new ExceptionFatal('Could not open file <em>'
				. Url::html_output($this -> filename)
				. '</em> for writing. Make sure PHP has write permission to this file.');
			}
			foreach ($this as $current_item => $count)
			{
				fwrite($h, "$current_item\t"
				. (($current_item == $item) ? ((int)$count + 1) : $count)
				. "\n");
			}
		}
		else
		{
			$h = @fopen($this -> filename, 'ab');
			if ($h === false)
			{
				throw new ExceptionFatal('Could not open file <em>'
				. $this -> filename . '</em> for writing.'
				. ' Make sure PHP has write permission to this file.');
			}
			fwrite($h, "$item\t1\n");
		}
		fclose($h);
	}
	
	/**
	 * @param string $name The key to look for
	 * @return bool True if $name is set
	 */
	public function is_set($name)
	{
		return isset($this -> config[$name]);
	}
	
	/**
	 * @param string $name The key to look for
	 * @return string The value $name points to
	 */
	public function __get($name)
	{
		if (isset($this -> config[$name]))
		{
			return $this -> config[$name];
		}
		throw new ExceptionFatal('Setting <em>' . Url::html_output($name)
		. '</em> is missing in file <em>'
		. Url::html_output($this -> filename) . '</em>.');
	}
}

?>