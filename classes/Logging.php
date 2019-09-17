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
 * Allows information to be written to the log file.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.1 (July 21, 2004)
 * @package AutoIndex
 */
class Logging
{
	/**
	 * @var string Filename of the log to write to
	 */
	private $filename;
	
	/**
	 * @param string $filename The name of the log file
	 */
	public function __construct($filename)
	{
		$this -> filename = $filename;
	}
	
	/**
	 * Writes data to the log file.
	 *
	 * @param string $extra Any additional data to add in the last column of the entry
	 */
	public function add_entry($extra = '')
	{
		if (LOG_FILE)
		{
			$h = @fopen($this -> filename, 'ab');
			if ($h === false)
			{
				throw new ExceptionDisplay('Could not open log file for writing.'
				. ' Make sure PHP has write permission to this file.');
			}
			global $dir, $ip, $host;
			$referrer = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'N/A');
			fwrite($h, date(DATE_FORMAT) . "\t" . date('H:i:s')
			. "\t$ip\t$host\t$referrer\t$dir\t$extra\n");
			fclose($h);
		}
	}
	
	/**
	 * @param int $max_num_to_display
	 */
	public function display($max_num_to_display)
	{
		if (!@is_file($this -> filename))
		{
			throw new ExceptionDisplay('There are no entries in the log file.');
		}
		$file_array = @file($this -> filename);
		if ($file_array === false)
		{
			throw new ExceptionDisplay('Could not open log file for reading.');
		}
		$count_log = count($file_array);
		$num = (($max_num_to_display == 0) ? $count_log : min($max_num_to_display, $count_log));
		$out = "<p>Viewing $num (of $count_log) entries.</p>\n"
		. '<table cellpadding="4"><tr class="autoindex_th">'
		. '<th>#</th><th>Date</th><th>Time</th>'
		. '<th>IP address</th><th>Hostname</th>'
		. '<th>Referrer</th><th>Directory</th>'
		. '<th>File downloaded or other info</th></tr>';
		for ($i = 0; $i < $num; $i++)
		{
			$class = (($i % 2) ? 'dark_row' : 'light_row');
			$out .= '<tr><th style="border: 1px solid; border-color: #7F8FA9;" class="'
			. $class . '">' . ($i + 1) . '</th>';
			$parts = explode("\t", rtrim($file_array[$count_log-$i-1], "\r\n"), 7);
			if (count($parts) !== 7)
			{
				throw new ExceptionDisplay('Incorrect format for log file on line '
				. ($i + 1));
			}
			for ($j = 0; $j < 7; $j++)
			{
				$cell = Url::html_output($parts[$j]);
				if ($j === 4 && $cell != 'N/A')
				{
					$cell = "<a class=\"autoindex_a\" href=\"$cell\">$cell</a>";
				}
				$out .= '<td style="border: 1px solid; border-color: #7F8FA9;" class="'
				. $class . '">' . (($cell == '') ? '&nbsp;</td>' : "$cell</td>");
			}
			$out .= "</tr>\n";
		}
		global $words;
		$out .= '</table><p><a class="autoindex_a" href="'
		. Url::html_output($_SERVER['PHP_SELF']) . '">' . $words -> __get('continue')
		. '.</a></p>';
		echo new Display($out);
		die();
	}
}

?>