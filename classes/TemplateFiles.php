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
 * In addition to everything TemplateInfo and template parse, this adds
 * information about files/folders through the item class.
 *
 * Third and final step for parsing templates. Only used for:
 * - each_file
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.1 (July 09, 2004)
 * @package AutoIndex
 */
class TemplateFiles extends TemplateInfo
{
	/**
	 * @var Item The file or folder we're currently processing
	 */
	private $temp_item;
	
	/**
	 * @var bool Is the current user an admin
	 */
	private $is_admin;
	
	/**
	 * @var bool Is the current user a moderator
	 */
	private $is_mod;
	
	/**
	 * @var int The number of the file we're currently processing
	 */
	private $i;
	
	/**
	 * @var int The total number of files to process
	 */
	private $length;
	
	/**
	 * @param array $m The array given by preg_replace_callback()
	 * @return string Property is gotten from temp_item
	 */
	private function callback_file($m)
	{
		global $words, $subdir;
		switch (strtolower($m[1]))
		{
			case 'tr_class':
			{
				return (($this -> i % 2) ? 'dark_row' : 'light_row');
			}
			case 'filename':
			{
				return Url::html_output($this -> temp_item -> __get('filename'));
			}
			case 'file_ext':
			{
				return $this -> temp_item -> file_ext();
			}
			case 'size':
			{
				return $this -> temp_item -> __get('size') -> formatted();
			}
			case 'bytes':
			{
				return $this -> temp_item -> __get('size') -> __get('bytes');
			}
			case 'date':
			case 'time':
			case 'm_time':
			{
				return $this -> temp_item -> format_m_time();
			}
			case 'a_time':
			{
				return $this -> temp_item -> format_a_time();
			}
			case 'thumbnail':
			{
				return $this -> temp_item -> __get('thumb_link');
			}
			case 'num_subfiles':
			{
				return (($this -> temp_item instanceof DirItem 
				&& !$this -> temp_item -> __get('is_parent_dir')) ? $this -> temp_item -> num_subfiles() : '');
			}
			case 'delete_link':
			{
				return (($this -> is_admin && !$this -> temp_item -> __get('is_parent_dir')) ?
				' [<a href="' . Url::html_output($_SERVER['PHP_SELF']) . '?action=delete&amp;dir=' . rawurlencode($subdir)
				. '&amp;filename=' . rawurlencode($this -> temp_item -> __get('filename'))
				. '" class="autoindex_small autoindex_a">' . $words -> __get('delete') . '</a>]' : '');
			}
			case 'rename_link':
			{
				return (($this -> is_admin && !$this -> temp_item -> __get('is_parent_dir')) ?
				' [<a href="' . Url::html_output($_SERVER['PHP_SELF']) . '?action=rename&amp;dir=' . rawurlencode($subdir)
				. '&amp;filename=' . rawurlencode($this -> temp_item -> __get('filename'))
				. '" class="autoindex_small autoindex_a">' . $words -> __get('rename') . '</a>]' : '');
			}
			case 'edit_description_link':
			{
				$slash = (($this -> temp_item instanceof DirItem) ? '/' : '');
				return (($this -> is_mod && DESCRIPTION_FILE && !$this -> temp_item -> __get('is_parent_dir')) ?
				' [<a href="' . Url::html_output($_SERVER['PHP_SELF']) . '?action=edit_description&amp;dir='
				. rawurlencode($subdir) . '&amp;filename='
				. rawurlencode($this -> temp_item -> __get('filename')) . $slash
				. '" class="autoindex_small autoindex_a">'
				. $words -> __get('edit description') . '</a>]' : '');
			}
			case 'ftp_upload_link':
			{
				if (!$this -> is_mod || !$this -> temp_item instanceof FileItem || !isset($_SESSION['ftp']))
				{
					return '';
				}
				return ' [<a href="' . Url::html_output($_SERVER['PHP_SELF']) . '?action=ftp&amp;dir='
				. rawurlencode($subdir) . '&amp;filename=' . rawurlencode($this -> temp_item -> __get('filename'))
				. '" class="autoindex_small autoindex_a">' . $words->__get('upload to ftp') . '</a>]';
			}
			default:
			{
				return $this -> temp_item -> __get($m[1]);
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
	private function callback_type($m)
	{
		switch (strtolower($m[1]))
		{
			case 'is_file': //file
			{
				return (($this -> temp_item instanceof FileItem) ? $m[2] : '');
			}
			case 'is_dir': //folder or link to parent directory
			{
				return (($this -> temp_item instanceof DirItem) ? $m[2] : '');
			}
			case 'is_real_dir': //folder
			{
				return (($this -> temp_item instanceof DirItem
				&& !$this -> temp_item -> __get('is_parent_dir')) ? $m[2] : '');
			}
			case 'is_parent_dir': //link to parent directory
			{
				return (($this -> temp_item instanceof DirItem
				&& $this -> temp_item -> __get('is_parent_dir')) ? $m[2] : '');
			}
			default:
			{
				throw new ExceptionDisplay('Invalid file:if statement in <em>'
				. Url::html_output(EACH_FILE) . '</em>');
			}
		}
	}
	
	/**
	 * Either the HTML text is returned or an empty string is returned,
	 * depending on if temp_item is the ith file parsed.
	 *
	 * @param array $m The array given by preg_replace_callback()
	 * @return string The result to insert into the HTML output
	 */
	private function callback_do_every($m)
	{
		$num = $this -> i + 1;
		return (($num % (int)$m[1] === 0 && $this -> length !== $num) ? $m[2] : '');
	}
	
	
	/**
	 * Parses info for each file in the directory. Order of elements to
	 * replace is:
	 * - file:if
	 * - do_every
	 * - file
	 *
	 * @param string $filename The name of the file to parse
	 * @param DirectoryListDetailed $list
	 */
	public function __construct($filename, DirectoryListDetailed $list)
	{
		parent::__construct($filename, $list);
		global $you;
		$this -> is_admin = ($you -> level >= ADMIN);
		$this -> is_mod = ($you -> level >= MODERATOR);
		$final_file_line = '';
		$this -> length = (int)$list -> __get('list_count');
		foreach ($list as $i => $item)
		{
			$this -> i = (int)$i;
			$this -> temp_item = $item;
			$temp_line = preg_replace_callback('/\{\s*file\s*:\s*if\s*:\s*(\w+)\s*\}(.*)\{\s*end\s*if\s*\}/Uis',
				array($this, 'callback_type'), $this -> out);
			$temp_line = preg_replace_callback('/\{\s*do_every\s*:\s*(\d+)\s*\}(.*)\{\s*end\s*do_every\s*\}/Uis',
				array($this, 'callback_do_every'), $temp_line);
			$final_file_line .= preg_replace_callback('/\{\s*file\s*:\s*(\w+)\s*\}/Ui',
				array($this, 'callback_file'), $temp_line);
		}
		$this -> out = $final_file_line;
	}
}

?>