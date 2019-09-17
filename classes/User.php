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
 * Stores info about an individual user account, such as username and password.
 *
 * This class is basically just used for storing data (hence all variables are
 * public). Currently, each user has four properties: username, password,
 * level, and home directory.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.1 (July 21, 2004)
 * @package AutoIndex
 */
class User
{
	/**
	 * @var string Username
	 */
	public $username;
	
	/**
	 * @var string The password, stored as a sha-1 hash of the actual password
	 */
	public $sha1_pass;
	
	/**
	 * @var int The user's level (use the GUEST USER ADMIN constants)
	 */
	public $level;
	
	/**
	 * @var string The user's home directory, or an empty string to use the default base_dir
	 */
	public $home_dir;
	
	/**
	 * @param User $user The user to compare to $this
	 * @return bool True if this user is equal to $user, based on username and password
	 */
	public function equals(User $user)
	{
		return ((strcasecmp($this -> username, $user -> username) === 0)
		&& (strcasecmp($this -> sha1_pass, $user -> sha1_pass) === 0));
	}
	
	/**
	 * Since this is not an instance of UserLoggedIn, we know he is not
	 * logged in.
	 */
	public function logout()
	{
		throw new ExceptionDisplay('You are not logged in.');
	}
	
	/**
	 * Here we display a login box rather than account options, since this is
	 * not an instance of UserLoggedIn.
	 *
	 * @return string The HTML text of the login box
	 */
	public function login_box()
	{
		$str = '';
		if (USE_LOGIN_SYSTEM)
		{
			global $words, $subdir;
			$str .= '<form action="' . Url::html_output($_SERVER['PHP_SELF']) . '?dir='
			. (isset($subdir) ? rawurlencode($subdir) : '')
			. '" method="post"><table><tr class="paragraph"><td>'
			. $words -> __get('username') . ':</td><td><input type="text" name="username" />'
			. '</td></tr><tr class="paragraph"><td>' . $words -> __get('password')
			. ':</td><td><input type="password" name="password" /></td></tr></table>'
			. '<p><input class="button" type="submit" value="'
			. $words -> __get('login') . '" /></p></form>';
		}
		if (LEVEL_TO_UPLOAD === GUEST)
		{
			global $you;
			$upload_panel = new Upload($you);
			$str .= $upload_panel -> __toString();
		}
		return $str;
	}
	
	/**
	 * @param string $username Username
	 * @param string $sha1_pass Password as a sha-1 hash
	 * @param int $level User's level (use the GUEST, USER, MODERATOR, ADMIN constants)
	 * @param string $home_dir The home directory of the user, or blank for the default
	 */
	public function __construct($username = '', $sha1_pass = '', $level = GUEST, $home_dir = '')
	{
		$level = (int)$level;
		if ($level < BANNED || $level > ADMIN)
		{
			throw new ExceptionDisplay('Error in user accounts file:
			Invalid user level (for username "'
			. Url::html_output($username) . '").');
		}
		if ($sha1_pass != '' && strlen($sha1_pass) !== 40)
		{
			throw new ExceptionDisplay('Error in user accounts file:
			Invalid password hash (for username "'
			. Url::html_output($username) . '").');
		}
		$this -> sha1_pass = $sha1_pass;
		$this -> username = $username;
		$this -> level = $level;
		$this -> home_dir = $home_dir;
	}
	
	/**
	 * @return string This string format is how it is stored in the user_list file
	 */
	public function __toString()
	{
		return $this -> username . "\t" . $this -> sha1_pass . "\t"
		. $this -> level . "\t" . $this -> home_dir . "\n";
	}
}

?>