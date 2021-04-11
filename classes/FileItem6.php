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
 * Subclass of item that specifically represents a file.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.1 (July 10, 2004)
 * @package AutoIndex
 */
class FileItem extends Item
{
	/**
	 * @param string $fn The filename
	 * @return string Everything after the list dot in the filename, not including the dot
	 */
	public static function ext($fn)
	{
		$fn = Item::get_basename($fn);
		return (strpos($fn, '.') ? strtolower(substr(strrchr($fn, '.'), 1)) : '');
	}
	
	/**
	 * @return string Returns the extension of the filename
	 * @see FileItem::ext()
	 */
	public function file_ext()
	{
		return self::ext($this -> filename);
	}
	
	/**
	 * @param string $parent_dir
	 * @param string $filename
	 */
	public function __construct($parent_dir, $filename)
	{
		parent::__construct($parent_dir, $filename);
		if (!is_file($this -> parent_dir . $filename))
		{
			throw new ExceptionDisplay('File <em>' . Url::html_output($this -> parent_dir . $filename) . '</em> does not exist.');
		}
		global $config, $words, $downloads;
		$this -> filename = $filename;
		$this -> size = new Size(filesize($this -> parent_dir . $filename));
		if (ICON_PATH)
		{
			$file_icon = new Icon($filename);
			$this -> icon = $file_icon -> __toString();
		}
		$this -> downloads = (DOWNLOAD_COUNT && $downloads -> is_set($parent_dir . $filename) ? (int)($downloads -> __get($parent_dir . $filename)) : 0);
		$this -> link = Url::html_output($_SERVER['PHP_SELF']) . '?dir=' . Url::translate_uri(substr($this -> parent_dir, strlen($config -> __get('base_dir')))) . '&amp;file=' . Url::translate_uri($filename);
		
		if (THUMBNAIL_HEIGHT && in_array(self::ext($filename), array('png', 'jpg', 'jpeg', 'jfif', 'gif', 'bmp')))
		{
			$this -> thumb_link = ' <img src="' . Url::html_output($_SERVER['PHP_SELF'])
			. '?thumbnail='. Url::translate_uri($this -> parent_dir . $filename) . '"' 
			. ' alt="' . $words -> __get('thumbnail of') . ' ' . $filename . '"' 
			. ' />';
			$this -> thumb_link .= ' <a href="' . Url::html_output($_SERVER['PHP_SELF'])
			. '?thm='. Url::translate_uri($this -> parent_dir . $filename) . '"' 
			. ' alt="' . $words -> __get('thumbnail of') . ' ' . $filename . '"' 
			. ' >' . $words -> __get('view') . ' ' . $words -> __get('file') . '</a>';
		}
		if (THUMBNAIL_HEIGHT && in_array(self::ext($filename), array('avi', 'thm', 'mkv', 'asf', 'mov', 'wmv', '3gp')))
		{
			$mime = new MimeType($filename);
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			//Display correct headers for media file
			$mimetype = finfo_file($finfo, $this -> parent_dir . $filename);
			$file_size = function_exists('getvideosize') ? getvideosize($this -> parent_dir . $filename) : array();
			$file_mime = function_exists('getvideosize') ? $file_size['mime'] : $mime -> __toString();
			$this -> thumb_link = ' <video controls="play" src="' . Url::translate_uri($this -> parent_dir . $filename) . '"'  
			. ' poster="' . Url::html_output($_SERVER['PHP_SELF'])  . '"' 
			. ' type="' . $file_mime . '"' 
			. ' />Your browser does not support the <code>video</code> element.</video> ';
			
			if (function_exists('imagecreatefromavi') && in_array(self::ext($filename), array('avi', 'wmv', '3gp')))
			{
				$this -> thumb_link .= '</br><img src="' . Url::html_output($_SERVER['PHP_SELF'])
				. '?thumbnail='. Url::translate_uri($this -> parent_dir . $filename) . '"' 
				. ' alt="' . $words -> __get('thumbnail of') . ' ' . $filename . '"' 
				. ' />';
			}
			else
			{
				$this -> thumb_link = ' <video controls="play" src="' . Url::html_output($_SERVER['PHP_SELF'])
				. '?thm='. Url::translate_uri($this -> parent_dir . $filename) . '"' 
				. ' poster="' . Url::html_output($_SERVER['PHP_SELF'])  . '"' 
				. ' type="' . $file_mime . ', ' . $mimetype . ', application/octet-stream"' 
				. ' />Your browser does not support the <code>video</code> element.</video> ';
			}
		}
		if (THUMBNAIL_HEIGHT && in_array(self::ext($filename), array('svg', 'xml')))
		{
			$icon_svg = ICON_PATH ? Url::translate_uri($config -> __get('icon_path') . 'svg.png') : Url::translate_uri($this -> parent_dir . $filename);
			$heightwidth = in_array(self::ext($filename), array('svg', 'xml')) ?  ' height="' . '150'  . '" width="' . '150'  . '" ' : ' '; 
			$this -> thumb_link = ' <img src="' . Url::html_output($_SERVER['PHP_SELF'])
			. '?thumbnail='. Url::translate_uri($icon_svg) . '"' 
			. ' alt="' . $words -> __get('thumbnail of') . ' ' . $filename . '"'
			. ' />';
			//. ' <img src="' . Url::html_output($_SERVER['PHP_SELF'])
			//. '?thumbnail='. Url::translate_uri($this -> parent_dir . $filename) . '" srcset="' . Url::html_output($_SERVER['PHP_SELF'])
			//. '?thumbnail='. Url::translate_uri($this -> parent_dir . $filename) . '"'  
			//. ' alt="' . $words -> __get('thumbnail of') . ' ' . $filename . '"'
			//. $heightwidth . ' />';
		}
		
		$size = $this -> size -> __get('bytes');
		if (MD5_SHOW && $size > 0 && $size / 1048576 <= $config -> __get('md5_show'))
		{
			$this -> md5_link = '<span class="autoindex_small">[<a class="autoindex_a" href="'
			. Url::html_output($_SERVER['PHP_SELF']) . '?dir='
			. Url::translate_uri(substr($this -> parent_dir, strlen($config -> __get('base_dir'))))
			. '&amp;md5=' . Url::translate_uri($filename) . '">'
			. $words -> __get('calculate md5sum') . '</a>]</span>';
		}
	}
	
	/**
	 * @param string $var The key to look for
	 * @return mixed The data stored at the key
	 */
	public function __get($var = '')
	{
		if (isset($this -> $var))
		{
			return $this -> $var;
		}
		throw new ExceptionDisplay('Variable <em>' . Url::html_output($var) . '</em> not set in FileItem class.');
	}
}

?>