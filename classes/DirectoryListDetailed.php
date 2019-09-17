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
 * Subclass of DirectoryList that uses the Item class to represent each
 * file/folder in the directory. Each entry in the list is an object of
 * the Item class.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.1.0 (January 01, 2006)
 * @package AutoIndex
 */
class DirectoryListDetailed extends DirectoryList
{
	/**
	 * @var string The HTML text that makes up the path navigation links
	 */
	protected $path_nav;
	
	/**
	 * @var int Total number of files in this directory
	 */
	protected $total_files;
	
	/**
	 * @var int Total number of folders in this directory
	 */
	protected $total_folders;
	
	/**
	 * @var int Total number of folders in this directory (including parent)
	 */
	protected $raw_total_folders;
	
	/**
	 * @var int Total number of downloads of files in this directory
	 */
	protected $total_downloads;
	
	/**
	 * @var Size Total size of this directory (recursive)
	 */
	protected $total_size;
	
	/**
	 * @return string The HTML text that makes up the path navigation
	 */
	private function set_path_nav()
	{
		global $config, $subdir;
		$exploded = explode('/', $subdir);
		$c = count($exploded) - 1;
		$temp = '<a class="autoindex_a" href="' . Url::html_output($_SERVER['PHP_SELF']) . '?dir=">'
		. Url::html_output(substr(str_replace('/', ' / ', $config -> __get('base_dir')), 0, -2)) . '</a>/ ';
		for ($i = 0; $i < $c; $i++)
		{
			$temp .= '<a class="autoindex_a" href="' . Url::html_output($_SERVER['PHP_SELF'])
			. '?dir=';
			for ($j = 0; $j <= $i; $j++)
			{
				$temp .= Url::translate_uri($exploded[$j]) . '/';
			}
			$temp .= '">' . Url::html_output($exploded[$i]) . '</a> / ';
		}
		return $temp;
	}
	
	/**
	 * Returns -1 if $a < $b or 1 if $a > $b
	 *
	 * @param Item $a
	 * @param Item $b
	 * @return int
	 */
	private static function callback_sort(Item $a, Item $b)
	{
		if ($a -> __get('is_parent_dir'))
		{
			return -1;
		}
		if ($b -> __get('is_parent_dir'))
		{
			return 1;
		}
		$sort = strtolower($_SESSION['sort']);
		if ($sort === 'size')
		{
			$val = (($a -> __get('size') -> __get('bytes') <
				$b -> __get('size') -> __get('bytes')) ? -1 : 1);
		}
		else
		{
			if (!$a -> is_set($sort))
			{
				$_SESSION['sort'] = 'filename'; //so the "continue" link will work
				throw new ExceptionDisplay('Invalid sort mode.');
			}
			if (is_string($a -> __get($sort)))
			{
				$val = strnatcasecmp($a -> __get($sort), $b -> __get($sort));
			}
			else
			{
				$val = (($a -> __get($sort) < $b -> __get($sort)) ? -1 : 1);
			}
		}
		return ((strtolower($_SESSION['sort_mode']) === 'd') ? -$val : $val);
	}
	
	/**
	 * @param array $list The array to be sorted with the callback_sort function
	 */
	protected static function sort_list(&$list)
	{
		usort($list, array('self', 'callback_sort'));
	}
	
	/**
	 * @return int The total number of files and folders (including the parent folder)
	 */
	public function total_items()
	{
		return $this -> raw_total_folders + $this -> total_files;
	}
	
	/**
	 * @param string $path The directory to read the files from
	 * @param int $page The number of files to skip (used for pagination)
	 */
	public function __construct($path, $page = 1)
	{
		$path = Item::make_sure_slash($path);
		parent::__construct($path);
		$subtract_parent = false;
		$this -> total_downloads = $total_size = 0;
		$dirs = $files = array();
		foreach ($this as $t)
		{
			if (@is_dir($path . $t))
			{
				$temp = new DirItem($path, $t);
				if ($temp -> __get('is_parent_dir'))
				{
					$dirs[] = $temp;
					$subtract_parent = true;
				}
				else if ($temp -> __get('filename') !== false)
				{
					$dirs[] = $temp;
					if ($temp -> __get('size') -> __get('bytes') !== false)
					{
						$total_size += $temp -> __get('size') -> __get('bytes');
					}
				}
			}
			else if (@is_file($path . $t))
			{
				$temp = new FileItem($path, $t);
				if ($temp -> __get('filename') !== false)
				{
					$files[] = $temp;
					$this -> total_downloads += $temp -> __get('downloads');
					$total_size += $temp -> __get('size') -> __get('bytes');
				}
			}
		}
		self::sort_list($dirs);
		self::sort_list($files);
		$this -> contents = array_merge($dirs, $files);
		$this -> total_size = new Size($total_size);
		$this -> total_files = count($files);
		$this -> raw_total_folders = $this -> total_folders = count($dirs);
		if ($subtract_parent)
		{
			$this -> total_folders--;
		}
		$this -> path_nav = $this -> set_path_nav();
		
		//Paginate the files
		if (ENTRIES_PER_PAGE)
		{
			if ($page < 1)
			{
				throw new ExceptionDisplay('Invalid page number.');
			}
			global $config;
			$num_per_page = $config -> __get('entries_per_page');
			if (($page - 1) * $num_per_page >= $this -> total_items())
			{
				throw new ExceptionDisplay('Invalid page number.');
			}
			$this -> contents = array_slice($this -> contents, ($page - 1) * $num_per_page, $num_per_page);
		}
	}
	
	/**
	 * @return string The HTML text of the directory list, using the template system
	 */
	public function __toString()
	{
		$head = new TemplateInfo(TABLE_HEADER, $this);
		$main = new TemplateFiles(EACH_FILE, $this);
		$foot = new TemplateInfo(TABLE_FOOTER, $this);
		return $head -> __toString() . $main -> __toString() . $foot -> __toString();
	}
}

?>