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
 * Uses the template system to format HTML output, which is then echoed.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.2 (July 22, 2004)
 * @package AutoIndex
 */
class Display
{
	/**
	 * @var string HTML text to output
	 */
	private $contents;
	
	/**
	 * @return string The HTML text of the list of function calls
	 * @see debug_backtrace()
	 */
	public static function get_trace()
	{
		$list = '<p><strong>Debug trace</strong>:';
		foreach (debug_backtrace() as $arr)
		{
			$line = (isset($arr['line']) ? $arr['line'] : 'unknown');
			$file = (isset($arr['file']) ? Item::get_basename($arr['file']) : 'unknown');
			$type = (isset($arr['type']) ? $arr['type'] : '');
			$class = (isset($arr['class']) ? $arr['class'] : '');
			$function = (isset($arr['function']) ? $arr['function'] : 'unknown');
			$list .= "\n<br /><em>$file</em> line $line <span class=\"autoindex_small\">($class$type$function)</span>";
		}
		return $list . '</p>';
	}
	
	/**
	 * @param string $contents Sets the HTML contents
	 */
	public function __construct(&$contents)
	{
		$this -> contents = $contents;
	}
	
	/**
	 * @return string The HTML output, using the template system
	 */
	public function __toString()
	{
		$header = new TemplateIndexer(GLOBAL_HEADER);
		$footer = new TemplateIndexer(GLOBAL_FOOTER);
		$output = $header -> __toString() . $this -> contents;
		if (DEBUG)
		{
			$output .= self::get_trace();
		}
		return $output . $footer -> __toString();
	}
}

?>