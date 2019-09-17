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
 * Allows files to be uploaded to the server from people's computers. By
 * default, only users logged in with level USER or higher may upload files.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.1 (June 30, 2004)
 * @package AutoIndex
 */
class Upload
{
	/**
	 * Uploads all files in the $_FILES array, then echos the results.
	 */
	public function do_upload()
	{
		$uploaded_files = $errors = '';
		global $words, $log, $dir;
		foreach ($_FILES as $file_upload)
		{
			$filename = Item::get_basename($file_upload['name']);
			if ($filename == '')
			{
				continue;
			}
			if (DirectoryList::is_hidden($filename))
			{
				$errors .= "<li>$filename ["
				. $words -> __get('filename is listed as a hidden file')
				. ']</li>';
				continue;
			}
			$filename = Url::clean_input($filename);
			$fullpathname = realpath($dir) . '/' . $filename;
			if (@file_exists($fullpathname))
			{
				$errors .= "<li>$filename ["
				. $words -> __get('file already exists') . ']</li>';
			}
			else if (@move_uploaded_file($file_upload['tmp_name'], $fullpathname))
			{
				@chmod($fullpathname, 0644);
				$uploaded_files .= "<li>$filename</li>";
				$log -> add_entry("Uploaded file: $filename");
			}
			else
			{
				$errors .= "<li>$filename</li>";
			}
		}
		if ($errors == '')
		{
			$errors = '<br />[' . $words -> __get('none') . ']';
		}
		if ($uploaded_files == '')
		{
			$uploaded_files = '<br />[' . $words -> __get('none') . ']';
		}
		$str = '<table><tr class="paragraph"><td class="autoindex_td" style="padding: 8px;">'
		. '<strong>' . $words -> __get('uploaded files')
		. "</strong>: $uploaded_files</p><p><strong>"
		. $words -> __get('failed files') . "</strong>: $errors"
		. '<p><a class="autoindex_a" href="' . Url::html_output($_SERVER['PHP_SELF']);
		if (isset($_GET['dir']))
		{
			$str .= '?dir=' . Url::translate_uri($_GET['dir']);
		}
		$str .= '">' . $words -> __get('continue') . '.</a></p></td></tr></table>';
		echo new Display($str);
		die();
	}
	
	/**
	 * @param User $current_user Makes sure the user has permission to upload files
	 */
	public function __construct(User $current_user)
	{
		if ($current_user -> level < LEVEL_TO_UPLOAD)
		{
			throw new ExceptionDisplay('Your user account does not have permission to upload files.');
		}
	}
	
	/**
	 * @return string The HTML that makes up the upload form
	 */
	public function __toString()
	{
		global $words, $subdir;
		if (isset($_GET['num_uploads']) && (int)$_GET['num_uploads'] > 0)
		{
			$str = '<form enctype="multipart/form-data" action="'
			. Url::html_output($_SERVER['PHP_SELF']) . '?dir=' . $subdir . '" method="post"><p>';
			$num = min((int)$_GET['num_uploads'], 100);
			for ($i = 0; $i < $num; $i++)
			{
				$str .= "\n\t" . $words -> __get('file')
				. ' '. ($i + 1) . ' : <input name="' . $i
				. '" type="file" /><br />';
			}
			$str .= '</p><p><input type="submit" value="'
			. $words -> __get('upload') . '" /></p></form>';
			$str = '<table><tr class="paragraph"><td class="autoindex_td" style="padding: 8px;">'
			. $str . '<p><a class="autoindex_a" href="'
			. Url::html_output($_SERVER['PHP_SELF']);
			if (isset($_GET['dir']))
			{
				$str .= '?dir=' . Url::translate_uri($_GET['dir']);
			}
			$str .= '">' . $words -> __get('continue') . '.</a></p></td></tr></table>';
			echo new Display($str);
			die();
		}
		return '<form action="' . Url::html_output($_SERVER['PHP_SELF']) . '" method="get"><p>'
		. $words -> __get('upload') . ' <input type="text" size="3" value="1" name="num_uploads" /> '
		. $words -> __get('files to this folder') . '<input class="button" type="submit" value="'
		. $words -> __get('upload') . '" /><input type="hidden" name="dir" value="'
		. $subdir . '" /></p></form>';
	}
}

?>