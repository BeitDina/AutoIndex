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
 * In addition to everything the template class parses, this parses if
 * statements and information about the current working directory.
 *
 * Second step in parsing templates. Used for:
 * - global header
 * - global footer
 * - table header
 * - table footer
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.1.0 (January 01, 2006)
 * @package AutoIndex
 */
class TemplateInfo extends TemplateIndexer
{
	/**
	 * @var DirectoryListDetailed
	 */
	private $dir_list;
	
	/**
	 * @param array $m The array given by preg_replace_callback()
	 * @return string Link to change the sort mode
	 */
	private static function callback_sort($m)
	{
		global $subdir;
		$m = Url::html_output(strtolower($m[1]));
		$temp = Url::html_output($_SERVER['PHP_SELF']) . '?dir=' . $subdir
		. '&amp;sort=' . $m . '&amp;sort_mode='
		. (($_SESSION['sort'] == $m && $_SESSION['sort_mode'] == 'a') ? 'd' : 'a');
		
		if (isset($_GET['search'], $_GET['search_mode'])
			&& $_GET['search'] != '' && $_GET['search_mode'] != '')
		{
			$temp .= '&amp;search=' . Url::html_output($_GET['search'])
			. '&amp;search_mode=' . Url::html_output($_GET['search_mode']);
		}
		return $temp;
	}
	
	/**
	 * @param array $m The array given by preg_replace_callback()
	 * @return string Property is gotten from dir_list
	 */
	private function callback_info($m)
	{
		switch (strtolower($m[1]))
		{
			case 'archive_link':
			{
				global $config;
				return Url::html_output($_SERVER['PHP_SELF']) . '?archive=true&amp;dir='
				. substr($this -> dir_list -> __get('dir_name'), strlen($config -> __get('base_dir')));
			}
			case 'total_size':
			{
				return $this -> dir_list -> __get('total_size') -> formatted();
			}
			case 'search_box':
			{
				return Search::search_box();
			}
			case 'login_box':
			{
				global $you;
				return $you -> login_box();
			}
			case 'current_page_number':
			{
				if (!ENTRIES_PER_PAGE)
				{
					return 1;
				}
				global $page;
				return $page;
			}
			case 'last_page_number':
			{
				if (!ENTRIES_PER_PAGE)
				{
					return 1;
				}
				global $max_page;
				return $max_page;
			}
			case 'previous_page_link':
			{
				if (!ENTRIES_PER_PAGE)
				{
					return '';
				}
				global $config, $page;
				if ($page <= 1)
				{
					return '&lt;&lt;';
				}
				return '<a class="autoindex_a" href="'
				. Url::html_output($_SERVER['PHP_SELF']) . '?page=' . ($page - 1)
				. '&amp;dir=' . substr($this -> dir_list -> __get('dir_name'),
					strlen($config -> __get('base_dir'))) . '">&lt;&lt;</a>';
			}
			case 'next_page_link':
			{
				if (!ENTRIES_PER_PAGE)
				{
					return '';
				}
				global $config, $page, $max_page;
				if ($page >= $max_page)
				{
					return '&gt;&gt;';
				}
				return '<a class="autoindex_a" href="'
				. Url::html_output($_SERVER['PHP_SELF']) . '?page=' . ($page + 1)
				. '&amp;dir=' . substr($this -> dir_list -> __get('dir_name'),
					strlen($config -> __get('base_dir'))) . '">&gt;&gt;</a>';
			}
			default:
			{
				return $this -> dir_list -> __get($m[1]);
			}
		}
	}
	
	/**
	 * Either the HTML text is returned, or an empty string is returned,
	 * depending on if the if-statement passed.
	 *
	 * @param array $m The array given by preg_replace_callback()
	 * @return string The result to insert into the HTML
	 */
	private static function callback_if($m)
	{
		$var = strtoupper($m[1]);
		if (!defined($var))
		{
			throw new ExceptionDisplay('<em>$' . Url::html_output($m[1])
			. '</em> is not a valid variable (check if-statement in template file).');
		}
		return (constant($var) ? $m[2] : '');
	}
	
	/**
	 * @param string $filename The name of the file to parse
	 * @param DirectoryListDetailed $dir_list
	 */
	public function __construct($filename, DirectoryListDetailed $dir_list)
	{
		parent::__construct($filename);
		$this -> dir_list = $dir_list;
		
		//parse if-statements
		$last_text = '';
		$regex = '/\{\s*if\s*:\s*(\w+)\s*\}(.*)\{\s*end\s*if\s*:\s*\1\s*\}/Uis'; //match {if:foo} ... {end if:foo}
		while ($last_text != ($this -> out = preg_replace_callback($regex, array('self', 'callback_if'), $this -> out)))
		{
			$last_text = $this -> out;
		} 
		$this -> out = $last_text;
		
		//parse sort modes
		$this -> out = preg_replace_callback('/\{\s*sort\s*:\s*(\w+)\s*\}/Ui',
			array('self', 'callback_sort'), $this -> out);
			
		//replace {info} variables
		$this -> out = preg_replace_callback('/\{\s*info\s*:\s*(\w+)\s*\}/Ui',
			array($this, 'callback_info'), $this -> out);
	}
}

?>