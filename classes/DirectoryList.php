<?php

/**
 * @package AutoIndex
 *
 * @copyright Copyright (C) 2002-2004 Justin Hagstrom
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
 * Maintains an array of all files and folders in a directory. Each entry is
 * stored as a string (the filename).
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.1 (June 30, 2004)
 * @package AutoIndex
 */
class DirectoryList implements Iterator
{
	/**
	 * @var string The directory this object represents
	 */
	protected $dir_name;
	
	/**
	 * @var array The list of filesname in this directory (strings)
	 */
	protected $contents;
	
	/**
	 * @var int The size of the $contents array
	 */
	private $list_count;
	
	//begin implementation of Iterator
	/**
	 * @var int $i is used to keep track of the current pointer inside the array when implementing Iterator
	 */
	private $i;
	
	/**
	 * @return string The element $i currently points to in the array
	 */
	public function current()
	{
		if ($this -> i < count($this -> contents))
		{
			return $this -> contents[$this -> i];
		}
		return false;
	}
	
	/**
	 * Increments the internal array pointer, then returns the value
	 * at that new position.
	 *
	 * @return string The current position of the pointer in the array
	 */
	public function next()
	{
		$this -> i++;
		return $this -> current();
	}
	
	/**
	 * Sets the internal array pointer to 0
	 */
	public function rewind()
	{
		$this -> i = 0;
	}
	
	/**
	 * @return bool True if $i is a valid array index
	 */
	public function valid()
	{
		return ($this -> i < count($this -> contents));
	}
	
	/**
	 * @return int Returns $i, the key of the array
	 */
	public function key()
	{
		return $this -> i;
	}
	//end implementation of Iterator
	
	/**
	 * @return int The total size in bytes of the folder (recursive)
	 */
	public function size_recursive()
	{
		$total_size = 0;
		foreach ($this as $current)
		{
			$t = $this -> dir_name . $current;
			if (@is_dir($t))
			{
				if ($current != '..')
				{
					$temp = new DirectoryList($t);
					$total_size += $temp -> size_recursive();
				}
			}
			else
			{
				$total_size += @filesize($t);
			}
		}
		return $total_size;
	}
	
	/**
	 * @return int The total number of files in this directory (recursive)
	 */
	public function num_files()
	{
		$count = 0;
		foreach ($this as $current)
		{
			$t = $this -> dir_name . $current;
			if (@is_dir($t))
			{
				if ($current != '..')
				{
					$temp = new DirectoryList($t);
					$count += $temp -> num_files();
				}
			}
			else
			{
				$count++;
			}
		}
		return $count;
	}
	
	/**
	 * @param string $string The string to search for
	 * @param array $array The array to search
	 * @return bool True if $string matches any elements in $array
	 */
	public static function match_in_array($string, &$array)
	{
		$string = Item::get_basename($string);
		static $replace = array(
			'\*' => '[^\/]*',
			'\+' => '[^\/]+',
			'\?' => '[^\/]?');
		foreach ($array as $m)
		{
			if (preg_match('/^' . strtr(preg_quote(Item::get_basename($m), '/'), $replace) . '$/i', $string))
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @param string $t The file or folder name
	 * @param bool $is_file
	 * @return bool True if $t is listed as a hidden file
	 */
	public static function is_hidden($t, $is_file = true)
	{
		$t = Item::get_basename($t);
		if ($t == '.' || $t == '')
		{
			return true;
		}
		global $you;
		if ($you -> level >= ADMIN)
		//allow admins to view hidden files
		{
			return false;
		}
		global $hidden_files, $show_only_these_files;
		if ($is_file && count($show_only_these_files))
		{
			return (!self::match_in_array($t, $show_only_these_files));
		}
		return self::match_in_array($t, $hidden_files);
	}
	
	/**
	 * @param string $var The key to look for
	 * @return mixed The data stored at the key
	 */
	public function __get($var)
	{
		if (isset($this -> $var))
		{
			return $this -> $var;
		}
		throw new ExceptionDisplay('Variable <em>' . Url::html_output($var)
		. '</em> not set in DirectoryList class.');
	}
	
	/**
	 * @param string $path
	 */
	public function __construct($path)
	{
		$path = Item::make_sure_slash($path);
		if (!@is_dir($path))
		{
			throw new ExceptionDisplay('Directory <em>' . Url::html_output($path)
			. '</em> does not exist.');
		}
		$temp_list = @scandir($path);
		if ($temp_list === false)
		{
			throw new ExceptionDisplay('Error reading from directory <em>'
			. Url::html_output($path) . '</em>.');
		}
		$this -> dir_name = $path;
		$this -> contents = array();
		foreach ($temp_list as $t)
		{
			if (!self::is_hidden($t, !@is_dir($path . $t)))
			{
				$this -> contents[] = $t;
			}
		}
		$this -> list_count = count($this -> contents);
		$this -> i = 0;
	}
}

?>