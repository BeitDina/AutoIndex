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
 * Abstract class to represent either a file or a directory.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.1 (July 03, 2004)
 * @package AutoIndex
 * @see DirItem, FileItem
 */
abstract class Item
{
	/**
	 * @var string
	 */
	protected $filename;
	
	/**
	 * @var Size
	 */
	protected $size;
	
	/**
	 * @var int Last modified time
	 */
	protected $m_time;
	
	/**
	 * @var int Last accessed time
	 */
	protected $a_time;
	
	/**
	 * @var int
	 */
	protected $downloads;
	
	/**
	 * @var string
	 */
	protected $description;
	
	/**
	 * @var string The HTML text of the link to the type icon
	 */
	protected $icon;
	
	/**
	 * @var string The HTML text of the "[New]" icon
	 */
	protected $new_icon;
	
	/**
	 * @var string The HTML text of the link to this file or folder
	 */
	protected $link;
	
	/**
	 * @var string The HTML text of the link to the thumbnail picture
	 */
	protected $thumb_link;
	
	/**
	 * @var string The HTML text of the link to find the md5sum
	 */
	protected $md5_link;
	
	/**
	 * @var string The name and path of the parent directory
	 */
	protected $parent_dir;
	
	/**
	 * @var bool True if this is a link to '../'
	 */
	protected $is_parent_dir;
	
	/**
	 * @param int $timestamp Time in UNIX timestamp format
	 * @return string Formatted version of $timestamp
	 */
	private static function format_date($timestamp)
	{
		if ($timestamp === false)
		{
			return '&nbsp;';
		}
		return date(DATE_FORMAT, $timestamp);
	}
	
	/**
	 * @return string Date modified (m_time) formatted as a string
	 * @see Item::format_date()
	 */
	public function format_m_time()
	{
		return self::format_date($this -> m_time);
	}
	
	/**
	 * @return string Date last accessed (a_time) formatted as a string
	 * @see Item::format_date()
	 */
	public function format_a_time()
	{
		return self::format_date($this -> a_time);
	}
	
	/**
	 * Returns everything after the slash, or the original string if there is
	 * no slash. A slash at the last character of the string is ignored.
	 *
	 * @param string $fn The file or folder name
	 * @return string The basename of $fn
	 * @see basename()
	 */
	public static function get_basename($fn)
	{
		return basename(str_replace('\\', '/', $fn));
	}
	
	/**
	 * @param string $path The directory name
	 * @return string If there is no slash at the end of $path, one will be added
	 */
	public static function make_sure_slash($path)
	{
		$path = str_replace('\\', '/', $path);
		if (!preg_match('#/$#', $path))
		{
			$path .= '/';
		}
		return $path;
	}
	
	/**
	 * @param string $parent_dir
	 * @param string $filename
	 */
	public function __construct($parent_dir, $filename)
	{
		$parent_dir = self::make_sure_slash($parent_dir);
		$full_name = $parent_dir . $filename;
		$this -> is_parent_dir = false;
		$this -> m_time = filemtime($full_name);
		$this -> a_time = fileatime($full_name);
		$this -> icon = $this -> new_icon = $this -> md5_link = $this -> thumb_link = '';
		global $descriptions;
		$this -> description = ((DESCRIPTION_FILE && $descriptions -> is_set($full_name)) ? $descriptions -> __get($full_name) : '&nbsp;');
		$this -> parent_dir = $parent_dir;
		if (DAYS_NEW)
		{
			global $config;
			$days_new = $config -> __get('days_new');
			$age = (time() - $this -> m_time) / 86400;
			$age_r = round($age, 1);
			$s = (($age_r == 1) ? '' : 's');
			
			$this -> new_icon = (($days_new > 0 && $age <= $days_new) ?
			(ICON_PATH ? ' <img src="' . $config -> __get('icon_path')
			. 'new.png" alt="' . "$age_r day$s" . ' old" height="14" width="28" />' : ' <span class="autoindex_small" style="color: #FF0000;">[New]</span>') : '');
		}
	}
	
	/**
	 * @param string $var The key to look for
	 * @return bool True if $var is set
	 */
	public function is_set($var)
	{
		return isset($this -> $var);
	}
	
	/**
	 * @return string The file or folder name
	 */
	public function __toString()
	{
		return $this -> filename;
	}
	
	/**
	 * @return string The file extension of the file or folder name
	 */
	abstract public function file_ext();
}

?>