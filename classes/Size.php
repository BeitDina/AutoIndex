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
 * Represens a filesize. Stored in bytes, and can be formatted as a string.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.1 (July 15, 2004)
 * @package AutoIndex
 */
class Size
{
	/**
	 * @var int Size in bytes
	 */
	private $bytes;
	
	/**
	 * @return string Returns $bytes formatted as a string
	 */
	public function formatted()
	{
		$size = $this -> bytes;
		if ($size === true)
		//used for the parent directory
		{
			return '&nbsp;';
		}
		if ($size === false)
		//used for regular directories (if SHOW_DIR_SIZE is false)
		{
			return '[dir]';
		}
		static $u = array('&nbsp;B', 'KB', 'MB', 'GB');
		for ($i = 0; $size >= 1024 && $i < 4; $i++)
		{
			$size /= 1024;
		}
		return number_format($size, 1) . ' ' . $u[$i];
	}
	
	/**
	 * Adds the size of $s into $this
	 *
	 * @param Size $s
	 */
	public function add_size(Size $s)
	{
		$temp = $s -> __get('bytes');
		if (is_int($temp))
		{
			$this -> bytes += $temp;
		}
	}
	
	/**
	 * True if parent directory,
	 * False if directory,
	 * Integer for an actual size.
	 *
	 * @param mixed $bytes
	 */
	public function __construct($bytes)
	{
		$this -> bytes = ((is_bool($bytes)) ? $bytes : max((int)$bytes, 0));
	}
	
	/**
	 * @param string $var The key to look for
	 * @return string The value $name points to
	 */
	public function __get($var)
	{
		if (isset($this -> $var))
		{
			return $this -> $var;
		}
		throw new ExceptionDisplay('Variable <em>' . Url::html_output($var)
		. '</em> not set in Size class.');
	}
}

?>