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
 * This is a special Exception that we can display using the template system via
 * the Display class.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.0 (August 01, 2004)
 * @package AutoIndex
 * @see Display
 */
class ExceptionDisplay extends ExceptionFatal
{
	/**
	 * @return string The HTML text to display
	 */
	public function __toString()
	{
		global $words;
		$str = '<table><tr class="paragraph"><td class="autoindex_td" style="padding: 8px;">'
		. $this -> message . '<p><a class="autoindex_a" href="'
		. Url::html_output($_SERVER['PHP_SELF']);
		if (isset($_GET['dir']))
		{
			$str .= '?dir=' . Url::translate_uri($_GET['dir']);
		}
		$str .= '">' . (isset($words) ? $words -> __get('continue') : 'Continue')
		. '.</a></p></td></tr></table>';
		$temp = new Display($str);
		return $temp -> __toString();
	}
}

?>