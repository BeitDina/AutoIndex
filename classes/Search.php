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
 * Similar to DirectoryListDetailed, except this filters out certain
 * entries based on filename.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.3 (July 06, 2005)
 * @package AutoIndex
 */
class Search extends DirectoryListDetailed
{
	/**
	 * @var array List of matched filenames
	 */
	private $matches;
	
	/**
	 * @return string The HTML text that makes up the search box
	 */
	public static function search_box()
	{
		global $words, $subdir;
		$search = (isset($_GET['search']) ? Url::html_output($_GET['search']) : '');
		$mode = (isset($_GET['search_mode']) ? self::clean_mode($_GET['search_mode']) : 'f');
		$modes = array('files' => 'f', 'folders' => 'd', 'both' => 'fd');
		$out = '<form action="' . Url::html_output($_SERVER['PHP_SELF']) . '" method="get">'
		. '<p><input type="hidden" name="dir" value="' . $subdir . '" />'
		. '<input type="text" name="search" value="' . $search
		. '" /><br /><select name="search_mode">';
		foreach ($modes as $word => $m)
		{
			$sel = (($m == $mode) ? ' selected="selected"' : '');
			$out .= '<option value="' . $m . '"' . $sel . '>'
			. $words -> __get($word) . '</option>';
		}
		$out .= '</select><input type="submit" class="button" value="'
		. $words -> __get('search') . '" /></p></form>';
		return $out;
	}
	
	/**
	 * @param string $filename
	 * @param string $string
	 * @return bool True if string matches filename
	 */
	private static function match(&$filename, &$string)
	{
		if (preg_match_all('/(?<=")[^"]+(?=")|[^ "]+/', $string, $matches))
		{
			foreach ($matches[0] as $w)
			{
				if (stripos($filename, $w) !== false)
				{
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Merges $obj into $this.
	 *
	 * @param Search $obj
	 */
	private function merge(Search $obj)
	{
		$this -> total_folders += $obj -> __get('total_folders');
		$this -> total_files += $obj -> __get('total_files');
		$this -> total_downloads += $obj -> __get('total_downloads');
		$this -> total_size -> add_size($obj -> __get('total_size'));
		$this -> matches = array_merge($this -> matches, $obj -> __get('contents'));
	}
	
	/**
	 * Returns a string with all characters except 'd' and 'f' stripped.
	 * Either 'd' 'f' 'df' will be returned, defaults to 'f'
	 *
	 * @param string $mode
	 * @return string
	 */
	private static function clean_mode($mode)
	{
		$str = '';
		if (stripos($mode, 'f') !== false)
		{
			$str .= 'f';
		}
		if (stripos($mode, 'd') !== false)
		{
			$str .= 'd';
		}
		else if ($str == '')
		{
			$str = 'f';
		}
		return $str;
	}
	
	/**
	 * @param string $query String to search for
	 * @param string $dir The folder to search (recursive)
	 * @param string $mode Should be f (files), d (directories), or fd (both)
	 */
	public function __construct($query, $dir, $mode)
	{
		if (strlen($query) < 2 || strlen($query) > 20)
		{
			throw new ExceptionDisplay('Search query is either too long or too short.');
		}
		$mode = self::clean_mode($mode);
		$dir = Item::make_sure_slash($dir);
		DirectoryList::__construct($dir);
		$this -> matches = array();
		$this -> total_size = new Size(0);
		$this -> total_downloads = $this -> total_folders = $this -> total_files = 0;
		foreach ($this as $item)
		{
			if ($item == '..')
			{
				continue;
			}
			if (@is_dir($dir . $item))
			{
				if (stripos($mode, 'd') !== false && self::match($item, $query))
				{
					$temp = new DirItem($dir, $item);
					$this -> matches[] = $temp;
					if ($temp -> __get('size') -> __get('bytes') !== false)
					{
						$this -> total_size -> add_size($temp -> __get('size'));
					}
					$this -> total_folders++;
				}
				$sub_search = new Search($query, $dir . $item, $mode);
				$this -> merge($sub_search);
			}
			else if (stripos($mode, 'f') !== false && self::match($item, $query))
			{
				$temp = new FileItem($dir, $item);
				$this -> matches[] = $temp;
				$this -> total_size -> add_size($temp -> __get('size'));
				$this -> total_downloads += $temp -> __get('downloads');
				$this -> total_files++;
			}
		}
		global $words, $config, $subdir;
		$link = ' <a class="autoindex_a" href="' . Url::html_output($_SERVER['PHP_SELF'])
		. '?dir=' . Url::translate_uri($subdir) . '">'
		. Url::html_output($dir) . '</a> ';
		$this -> path_nav = $words -> __get('search results for')
		. $link . $words -> __get('and its subdirectories');
		$this -> contents = $this -> matches;
		unset($this -> matches);
	}
}

?>