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
 * Stores a list of valid user accounts which are read from the user_list file.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.2 (July 21, 2004)
 * @package AutoIndex
 */
class Accounts implements Iterator
{
	/**
	 * @var array The list of valid accounts taken from the stored file
	 */
	private $userlist;
	
	/**
	 * @var int The size of the $userlist array
	 */
	private $list_count;
	
	//begin implementation of Iterator
	/**
	 * @var int $i is used to keep track of the current pointer inside the array when implementing Iterator
	 */
	private $i;
	
	/**
	 * @return User The current element in the array
	 */
	public function current()
	{
		if ($this -> i < $this -> list_count)
		{
			return $this -> userlist[$this -> i];
		}
		return false;
	}
	
	/**
	 * Increments the internal array pointer, then returns the user at that
	 * new position.
	 *
	 * @return User The current position of the pointer in the array
	 */
	public function next()
	{
		$this -> i++;
		return $this -> current();
	}
	
	/**
	 * Sets the internal array pointer to 0.
	 */
	public function rewind()
	{
		$this -> i = 0;
	}
	
	/**
	 * @return bool True if $i is a valid array index
	 */
	public function valid()
	{
		return ($this -> i < $this -> list_count);
	}
	
	/**
	 * @return int Returns $i, the key of the array
	 */
	public function key()
	{
		return $this -> i;
	}
	//end implementation of Iterator
	
	/**
	 * Reads the user_list file, and fills the $contents array with the
	 * valid users.
	 */
	public function __construct()
	{
		global $config;
		$file = @file($config -> __get('user_list'));
		if ($file === false)
		{
			throw new ExceptionDisplay('Cannot open user account file.');
		}
		$this -> userlist = array();
		foreach ($file as $line_num => $line)
		{
			$line = rtrim($line, "\r\n");
			if (ConfigData::line_is_comment($line))
			{
				continue;
			}
			$parts = explode("\t", $line);
			if (count($parts) !== 4)
			{
				throw new ExceptionDisplay('Incorrect format for user accounts file on line '
				. ($line_num + 1));
			}
			$this -> userlist[] = new User($parts[0], $parts[1], $parts[2], $parts[3]);
		}
		$this -> list_count = count($this -> userlist);
		$this -> i = 0;
	}
	
	/**
	 * @param string $name Username to find the level of
	 * @return int The level of the user
	 */
	public function get_level($name)
	{
		foreach ($this as $look)
		{
			if (strcasecmp($look -> username, $name) !== 0)
			{
				continue;
			}
			$lev = (int)$look -> level;
			if ($lev < BANNED || $lev > ADMIN)
			{
				throw new ExceptionDisplay('Invalid level for user <em>'
				. Url::html_output($name) . '</em>.');
			}
			return $lev;
		}
		throw new ExceptionDisplay('User <em>' . Url::html_output($name)
		. '</em> does not exist.');
	}
	
	/**
	 * @param string $name Username to find the home directory for
	 * @return string The home directory of $name
	 */
	public function get_home_dir($name)
	{
		foreach ($this as $look)
		{
			if (strcasecmp($look -> username, $name) === 0)
			{
				return $look -> home_dir;
			}
		}
		throw new ExceptionDisplay('User <em>' . Url::html_output($name)
		. '</em> does not exist.');
	}
	
	/**
	 * Returns $name with the character case the same as it is in the accounts
	 * file.
	 *
	 * @param string $name Username to find the stored case of
	 * @return string
	 */
	public function get_stored_case($name)
	{
		foreach ($this as $look)
		{
			if (strcasecmp($look -> username, $name) === 0)
			{
				return $look -> username;
			}
		}
		throw new ExceptionDisplay('User <em>' . Url::html_output($name)
		. '</em> does not exist.');
	}
	
	/**
	 * @param User $user The user to determine if it is valid or not
	 * @return bool True if the username and password are correct
	 */
	public function is_valid_user(User $user)
	{
		foreach ($this as $look)
		{
			if ($look -> equals($user))
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @param string $name Username to find if it exists or not
	 * @return bool True if a user exists with the username $name
	 */
	public function user_exists($name)
	{
		foreach ($this as $look)
		{
			if (strcasecmp($look -> username, $name) === 0)
			{
				return true;
			}
		}
		return false;
	}
}

?>