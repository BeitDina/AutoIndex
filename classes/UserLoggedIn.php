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
 * Represents a user that is currently logged in.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.1 (July 02, 2004)
 * @package AutoIndex
 */
class UserLoggedIn extends User
{
	/**
	 * Since the user is already logged in, the account options will be
	 * displayed rather than a login box.
	 *
	 * @return string The HTML text that makes up the account options box
	 */
	public function login_box()
	{
		global $words, $you, $subdir;
		$txt = '<p><a class="autoindex_a" href="' . Url::html_output($_SERVER['PHP_SELF'])
		. '?dir=' . (isset($subdir) ?  rawurlencode($subdir) : '')
		. '&amp;logout=true">' . $words -> __get('logout')
		. ' [ ' . Url::html_output($this -> username) . ' ]</a></p>';
		if ($you -> level >= MODERATOR)
		//show admin options if they are a moderator or higher
		{
			$admin_panel = new Admin($you);
			$txt = $admin_panel -> __toString() . $txt;
		}
		if ($you -> level >= LEVEL_TO_UPLOAD)
		//show upload options if they are a logged in user or higher
		{
			$upload_panel = new Upload($you);
			$txt .= $upload_panel -> __toString();
		}
		return $txt;
	}
	
	/**
	 * Logs out the user by destroying the session data and refreshing the
	 * page.
	 */
	public function logout()
	{
		global $subdir;
		$this -> level = GUEST;
		$this -> sha1_pass = $this -> username = '';
		session_unset();
		session_destroy();
		$home = new Url(Url::html_output($_SERVER['PHP_SELF']), true);
		$home -> redirect();
	}
	
	/**
	 * Validates username and password using the accounts stored in the
	 * user_list file.
	 *
	 * @param string $username The username to login
	 * @param string $sha1_pass The sha-1 hash of the password
	 */
	public function __construct($username, $sha1_pass)
	{
		parent::__construct($username, $sha1_pass);
		$accounts = new Accounts();
		if (!($accounts -> is_valid_user($this)))
		{
			global $log;
			$log -> add_entry("Invalid login (Username: $username)");
			session_unset();
			sleep(1);
			throw new ExceptionDisplay('Invalid username or password.');
		}
		$this -> level = $accounts -> get_level($username);
		if ($this -> level <= BANNED)
		{
			throw new ExceptionDisplay('Your account has been disabled by the site admin.');
		}
		$this -> username = $accounts -> get_stored_case($username);
		$this -> home_dir = $accounts -> get_home_dir($username);
	}
}

?>