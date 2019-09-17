<?php

/**
 * @package AutoIndex
 *
 * @copyright Copyright (C) 2002-2005 Justin Hagstrom
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
 * Lets admins move/rename/delete files and implements the other actions in the
 * admin panel.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.1.1 (August 10, 2005)
 * @package AutoIndex
 */
class Admin
{
	/**
	 * @var int The level of the logged in user
	 */
	private $level;
	
	/**
	 * @var string The name of the logged in user
	 */
	private $username;
	
	/**
	 * @param string $path The path of the directory to create
	 * @return bool True on success, false on failure
	 */
	public static function mkdir_recursive($path)
	{
		$path = Item::make_sure_slash($path);
		if (@is_dir($path))
		{
			return true;
		}
		if (!self::mkdir_recursive(dirname($path)))
		{
			return false;
		}
		return @mkdir($path, 0755);
	}
	
	/**
	 * Deletes a directory and all its contents.
	 *
	 * @param string $path The path of the directory to delete
	 * @return bool True on success, false on failure
	 */
	private static function rmdir_recursive($path)
	{
		$path = Item::make_sure_slash($path);
		$list = @scandir($path);
		if ($list === false)
		{
			return false;
		}
		foreach ($list as $file)
		{
			if ($file == '' || $file == '.' || $file == '..')
			{
				continue;
			}
			$dir = "$path$file/";
			@is_dir($dir) ? self::rmdir_recursive($dir) : @unlink($dir);
		}
		return @rmdir($path);
	}
	
	/**
	 * Copies a remote file to the local server.
	 *
	 * @param string $protocol Either ftp:// or http://
	 * @param string $url The rest of the URL after the protocol
	 */
	private static function copy_remote_file($protocol, $url)
	{
		if ($protocol == '' || $url == '')
		{
			throw new ExceptionDisplay('Please go back and enter a file to copy.');
		}
		global $dir;
		$local_file = $dir . Item::get_basename($url);
		if (@file_exists($local_file))
		{
			throw new ExceptionDisplay('The file already exists in this directory.');
		}
		$remote = $protocol . $url;
		$r = @fopen($remote, 'rb');
		if ($r === false)
		{
			throw new ExceptionDisplay('Cannot open remote file for reading: <em>'
			. Url::html_output($remote) . '</em>');
		}
		$l = @fopen($local_file, 'wb');
		if ($l === false)
		{
			throw new ExceptionDisplay('Cannot open local file for writing.');
		}
		while (true)
		{
			$temp = fread($r, 8192);
			if ($temp === '')
			{
				break;
			}
			fwrite($l, $temp);
		}
		fclose($l);
		fclose($r);
	}
	
	/**
	 * @param string $filename The path to the file that stores the info
	 * @param string $old_name The old name of the file or folder to update inside of $filename
	 * @param string $new_name The new name of the file or folder
	 */
	private static function update_file_info($filename, $old_name, $new_name)
	{
		if (!@is_file($filename))
		{
			throw new ExceptionDisplay('The file <em>'
			. Url::html_output($filename) . '</em> does not exist.');
		}
		$text = @file_get_contents($filename);
		if ($text === false)
		{
			throw new ExceptionDisplay('Cannot open file <em>'
			. Url::html_output($filename) . '</em> for reading.');
		}
		$h = @fopen($filename, 'wb');
		if ($h === false)
		{
			throw new ExceptionDisplay('Cannot open file <em>'
			. Url::html_output($filename) . '</em> for writing.');
		}
		fwrite($h, preg_replace('/^' . preg_quote($old_name, '/')
		. '/m', $new_name, $text));
		fclose($h);
	}
	
	/**
	 * Validates a potential new password.
	 *
	 * @param string $pass1 The new password
	 * @param string $pass2 The new password typed again
	 */
	private static function validate_new_password($pass1, $pass2)
	{
		if ($pass1 != $pass2)
		{
			throw new ExceptionDisplay('Passwords do not match.');
		}
		if (strlen($pass1) < 6)
		{
			throw new ExceptionDisplay('Password must be at least 6 characters long.');
		}
	}
	
	/**
	 * Changes a user's password.
	 *
	 * @param string $username The username
	 * @param string $old_pass The user's old password
	 * @param string $new_pass1 The new password
	 * @param string $new_pass2 The new password typed again
	 */
	private static function change_password($username, $old_pass, $new_pass1, $new_pass2)
	{
		self::validate_new_password($new_pass1, $new_pass2);
		$accounts = new Accounts();
		if (!$accounts -> user_exists($username))
		{
			throw new ExceptionDisplay('Cannot change password: username does not exist.');
		}
		if (!$accounts -> is_valid_user(new User($username, sha1($old_pass))))
		{
			throw new ExceptionDisplay('Incorrect old password.');
		}
		global $config;
		$h = @fopen($config -> __get('user_list'), 'wb');
		if ($h === false)
		{
			throw new ExceptionDisplay("Could not open file <em>$user_list</em> for writing."
			. ' Make sure PHP has write permission to this file.');
		}
		foreach ($accounts as $this_user)
		{
			if (strcasecmp($this_user -> username, $username) === 0)
			{
				$this_user = new User($username, sha1($new_pass1), $this_user -> level, $this_user -> home_dir);
			}
			fwrite($h, $this_user -> __toString());
		}
		fclose($h);
		$_SESSION['password'] = sha1($new_pass1);
		throw new ExceptionDisplay('Password successfully changed.');
	}
	
	/**
	 * Changes a user's level.
	 *
	 * @param string $username The username
	 * @param int $new_level The user's new level
	 */
	private static function change_user_level($username, $new_level)
	{
		if ($new_level < BANNED || $new_level > ADMIN)
		{
			throw new ExceptionDisplay('Invalid user level.');
		}
		$accounts = new Accounts();
		if (!$accounts -> user_exists($username))
		{
			throw new ExceptionDisplay('Cannot change level: username does not exist.');
		}
		global $config;
		$h = @fopen($config -> __get('user_list'), 'wb');
		if ($h === false)
		{
			throw new ExceptionDisplay("Could not open file <em>$user_list</em> for writing."
			. ' Make sure PHP has write permission to this file.');
		}
		foreach ($accounts as $this_user)
		{
			if (strcasecmp($this_user -> username, $username) === 0)
			{
				$this_user = new User($username, $this_user -> sha1_pass, $new_level, $this_user -> home_dir);
			}
			fwrite($h, $this_user -> __toString());
		}
		fclose($h);
		throw new ExceptionDisplay('User level successfully changed.');
	}
	
	/**
	 * @param string $username The name of the new user to create
	 * @param string $pass1 The raw password
	 * @param string $pass2 The raw password repeated again for verification
	 * @param int $level The level of the user (use GUEST USER ADMIN constants)
	 * @param string $home_dir The home directory of the user, or blank for the default
	 */
	private static function add_user($username, $pass1, $pass2, $level, $home_dir = '')
	{
		self::validate_new_password($pass1, $pass2);
		$username_reg_exp = '/^[A-Za-z0-9_-]+$/';
		if (!preg_match($username_reg_exp, $username))
		{
			throw new ExceptionDisplay('The username must only contain alpha-numeric characters, underscores, or dashes.'
			. '<br /><span class="autoindex_small">It must match the regular expression: <strong>'
			. Url::html_output($username_reg_exp) . '</strong></span>');
		}
		if ($home_dir != '')
		{
			$home_dir = Item::make_sure_slash($home_dir);
			if (!@is_dir($home_dir))
			{
				throw new ExceptionDisplay('The user\'s home directory is not valid directory.');
			}
		}
		$list = new Accounts();
		if ($list -> user_exists($username))
		{
			throw new ExceptionDisplay('This username already exists.');
		}
		global $config;
		$h = @fopen($config -> __get('user_list'), 'ab');
		if ($h === false)
		{
			throw new ExceptionDisplay('User list file could not be opened for writing.');
		}
		$new_user = new User($username, sha1($pass1), $level, $home_dir);
		fwrite($h, $new_user -> __toString());
		fclose($h);
		throw new ExceptionDisplay('User successfully added.');
	}
	
	/**
	 * @param string $username Deletes user with the name $username
	 */
	private static function del_user($username)
	{
		$accounts = new Accounts();
		if (!$accounts -> user_exists($username))
		{
			throw new ExceptionDisplay('Cannot delete user: username does not exist.');
		}
		global $config;
		$h = @fopen($config -> __get('user_list'), 'wb');
		if ($h === false)
		{
			throw new ExceptionDisplay("Could not open file <em>$user_list</em> for writing."
			. ' Make sure PHP has write permission to this file.');
		}
		foreach ($accounts as $this_user)
		{
			if (strcasecmp($this_user -> username, $username) !== 0)
			{
				fwrite($h, $this_user -> __toString());
			}
		}
		fclose($h);
		throw new ExceptionDisplay('User successfully removed.');
	}
	
	/**
	 * @param User $current_user This user is checked to make sure it really is an admin
	 */
	public function __construct(User $current_user)
	{
		if (!($current_user instanceof UserLoggedIn))
		{
			throw new ExceptionDisplay('You must be logged in to access this section.');
		}
		$this -> level = $current_user -> level;
		$this -> username = $current_user -> username;
	}
	
	/**
	 * @param string $action
	 */
	public function action($action)
	{
		//This is a list of the actions moderators can do (otherwise, the user must be an admin)
		$mod_actions = array('edit_description', 'change_password', 'ftp');
		
		if (in_array(strtolower($action), $mod_actions))
		{
			if ($this -> level < MODERATOR)
			{
				throw new ExceptionDisplay('You must be a moderator to access this section.');
			}
		}
		else if ($this -> level < ADMIN)
		{
			throw new ExceptionDisplay('You must be an administrator to access this section.');
		}
		switch (strtolower($action))
		{
			case 'config':
			{
				/** Include the config generator file. */
				if (!@include_once(CONFIG_GENERATOR))
				{
					throw new ExceptionDisplay('Error including file <em>'
					. CONFIG_GENERATOR . '</em>');
				}
				die();
			}
			case 'rename':
			{
				if (!isset($_GET['filename']))
				{
					throw new ExceptionDisplay('No filenames specified.');
				}
				global $dir;
				$old = $dir . Url::clean_input($_GET['filename']);
				if (!@file_exists($old))
				{
					header('HTTP/1.0 404 Not Found');
					throw new ExceptionDisplay('Specified file could not be found.');
				}
				if (isset($_GET['new_name']))
				{
					$new = $dir . Url::clean_input($_GET['new_name']);
					if ($old == $new)
					{
						throw new ExceptionDisplay('Filename unchanged.');
					}
					if (@file_exists($new))
					{
						throw new ExceptionDisplay('Cannot overwrite existing file.');
					}
					if (@rename($old, $new))
					{
						global $config;
						if (DOWNLOAD_COUNT)
						{
							self::update_file_info($config -> __get('download_count'), $old, $new);
						}
						if (DESCRIPTION_FILE)
						{
							self::update_file_info($config -> __get('description_file'), $old, $new);
						}
						throw new ExceptionDisplay('File renamed successfully.');
					}
					throw new ExceptionDisplay('Error renaming file.');
				}
				global $words, $subdir;
				throw new ExceptionDisplay('<p>' . $words -> __get('renaming')
				. ' <em>' . Url::html_output($_GET['filename'])
				. '</em></p><p>' . $words -> __get('new filename')
				. ':<br /><span class="autoindex_small">('
				. $words -> __get('you can also move the file by specifying a path')
				. ')</span></p><form method="get" action="' . Url::html_output($_SERVER['PHP_SELF'])
				. '"><p><input type="hidden" name="filename" value="'
				. $_GET['filename'] . '" />'
				. '<input type="hidden" name="dir" value="' . $subdir
				. '" /><input type="hidden" name="action" value="rename" />'
				. '<input type="text" name="new_name" size="40" value="'
				. $_GET['filename'] . '" />'
				. '<input type="submit" value="' . $words -> __get('rename')
				. '" /></p></form>');
			}
			case 'delete':
			{
				if (!isset($_GET['filename']))
				{
					throw new ExceptionDisplay('No filename specified.');
				}
				if (isset($_GET['sure']))
				{
					global $dir;
					$to_delete = $dir . Url::clean_input($_GET['filename']);
					if (!@file_exists($to_delete))
					{
						header('HTTP/1.0 404 Not Found');
						throw new ExceptionDisplay('Specified file could not be found.');
					}
					if (@is_dir($to_delete))
					{
						if (self::rmdir_recursive($to_delete))
						{
							throw new ExceptionDisplay('Folder successfully deleted.');
						}
						throw new ExceptionDisplay('Error deleting folder.');
					}
					if (@unlink($to_delete))
					{
						throw new ExceptionDisplay('File successfully deleted.');
					}
					throw new ExceptionDisplay('Error deleting file.');
				}
				global $words, $subdir;
				throw new ExceptionDisplay('<p>'
				. $words -> __get('are you sure you want to delete the file')
				. ' <em>' . Url::html_output($_GET['filename']) . '</em>?</p>'
				. '<form method="get" action="' . Url::html_output($_SERVER['PHP_SELF'])
				. '"><p><input type="hidden" name="action" value="delete" />'
				. '<input type="hidden" name="dir" value="' . $subdir
				. '" /><input type="hidden" name="sure" value="true" />'
				. '<input type="hidden" name="filename" value="'
				. $_GET['filename'] . '" /><input type="submit" value="'
				. $words -> __get('yes, delete') . '" /></p></form>');
			}
			case 'add_user':
			{
				if (isset($_POST['username'], $_POST['pass1'], $_POST['pass2'], $_POST['level'], $_POST['home_dir']))
				{
					self::add_user($_POST['username'], $_POST['pass1'],
					$_POST['pass2'], (int)$_POST['level'], $_POST['home_dir']);
				}
				global $words;
				throw new ExceptionDisplay($words -> __get('add user')
				. ':<form method="post" action="'
				. Url::html_output($_SERVER['PHP_SELF']) . '?action=add_user"><p>'
				. $words -> __get('username') . ': <input type="text" name="username" /><br />'
				. $words -> __get('password') . ': <input type="password" name="pass1" /><br />'
				. $words -> __get('password') . ': <input type="password" name="pass2" /><br />'
				. $words -> __get('level') . ': <select name="level"><option value="' . GUEST . '">'
				. $words -> __get('guest') . '</option><option selected="selected" value="' . USER . '">'
				. $words -> __get('user') . '</option><option value="' . MODERATOR . '">'
				. $words -> __get('mod') . '</option><option value="' . ADMIN . '">'
				. $words -> __get('admin') . '</option></select></p><p>Home Directory: '
				. '<input type="text" name="home_dir" /><br /><span class="autoindex_small">(leave blank to use the default base directory)</span></p><p><input type="submit" value="'
				. $words -> __get('add user') . '" /></p></form>');
			}
			case 'change_password':
			{
				if (isset($_POST['pass1'], $_POST['pass2'], $_POST['old_pass']))
				{
					self::change_password($this -> username, $_POST['old_pass'],
						$_POST['pass1'], $_POST['pass2']);
				}
				throw new ExceptionDisplay('<form method="post" action="'
				. Url::html_output($_SERVER['PHP_SELF']) . '?action=change_password">
				<p>Old password: <input type="password" name="old_pass" />
				<br />New password: <input type="password" name="pass1" />
				<br />New password: <input type="password" name="pass2" /></p>
				<p><input type="submit" value="Change" /></p></form>');
			}
			case 'change_user_level':
			{
				if (isset($_POST['username'], $_POST['level']))
				{
					self::change_user_level($_POST['username'], (int)$_POST['level']);
				}
				$accounts = new Accounts();
				$out = '<form method="post" action="'
				. Url::html_output($_SERVER['PHP_SELF']) . '?action=change_user_level">
				<p>Select user: <select name="username">';
				foreach ($accounts as $this_user)
				{
					$out .= '<option>' . $this_user -> username . '</option>';
				}
				global $words;
				throw new ExceptionDisplay($out
				. '</select></p><p>Select new level: <select name="level"><option value="' . BANNED . '">
				Banned</option><option value="' . GUEST . '">'
				. $words -> __get('guest') . '</option><option selected="selected" value="' . USER . '">'
				. $words -> __get('user') . '</option><option value="' . MODERATOR . '">'
				. $words -> __get('mod') . '</option><option value="' . ADMIN . '">'
				. $words -> __get('admin') . '</option></select></p>
				<p><input type="submit" value="Change user\'s level" /></p></form>');
			}
			case 'del_user':
			{
				if (isset($_POST['username']))
				{
					if (isset($_POST['sure']))
					{
						self::del_user($_POST['username']);
					}
					global $words;
					throw new ExceptionDisplay('<p>'
					. $words -> __get('are you sure you want to remove the user')
					. ' <em>'.$_POST['username'] . '</em>?</p>'
					. '<form method="post" action="' . Url::html_output($_SERVER['PHP_SELF']) . '?action=del_user">'
					. '<p><input type="hidden" name="sure" value="true" /><input type="hidden" name="username" value="'
					. $_POST['username'] . '" /><input type="submit" value="'
					. $words -> __get('yes, delete') . '" /></p></form>');
				}
				global $words;
				$accounts = new Accounts();
				$out = '<p>' . $words -> __get('select user to remove')
				. ':</p><form method="post" action="' . Url::html_output($_SERVER['PHP_SELF'])
				. '?action=del_user"><p><select name="username">';
				foreach ($accounts as $this_user)
				{
					$out .= '<option>' . $this_user -> username . '</option>';
				}
				throw new ExceptionDisplay($out
				. '</select></p><p><input type="submit" value="'
				. $words -> __get('delete this user') . '" /></p></form>');
			}
			case 'edit_description':
			{
				if (isset($_GET['filename']))
				{
					global $dir;
					$filename = $dir . $_GET['filename'];
					if (isset($_GET['description']))
					{
						global $descriptions, $config;
						if (DESCRIPTION_FILE && $descriptions -> is_set($filename))
						//if it's already set, update the old description
						{
							//update the new description on disk
							$h = @fopen($config -> __get('description_file'), 'wb');
							if ($h === false)
							{
								throw new ExceptionDisplay('Could not open description file for writing.'
								. ' Make sure PHP has write permission to this file.');
							}
							foreach ($descriptions as $file => $info)
							{
								fwrite($h, "$file\t" . (($file == $filename) ? $_GET['description'] : $info) . "\n");
							}
							fclose($h);
							
							//update the new description in memory
							$descriptions -> set($filename, $_GET['description']);
						}
						else if ($_GET['description'] != '')
						//if it's not set, add it to the end
						{
							$h = @fopen($config -> __get('description_file'), 'ab');
							if ($h === false)
							{
								throw new ExceptionDisplay('Could not open description file for writing.'
								. ' Make sure PHP has write permission to this file.');
							}
							fwrite($h, "$filename\t" . $_GET['description'] . "\n");
							fclose($h);
							
							//read the description file with the updated data
							$descriptions = new ConfigData($config -> __get('description_file'));
						}
					}
					else
					{
						global $words, $subdir, $descriptions;
						$current_desc = (DESCRIPTION_FILE && $descriptions -> is_set($filename) ? $descriptions -> __get($filename) : '');
						throw new ExceptionDisplay('<p>'
						. $words -> __get('enter the new description for the file')
						. ' <em>' . Url::html_output($_GET['filename'])
						. '</em>:</p><form method="get" action="' . Url::html_output($_SERVER['PHP_SELF'])
						. '"><p><input type="hidden" name="dir" value="'
						. $subdir . '" /><input type="hidden" name="filename" value="'
						. $_GET['filename'] . '" />'
						. '<input type="hidden" name="action" value="edit_description" /></p><p><input type="text" name="description" size="50" value="'
						. Url::html_output($current_desc)
						. '" /></p><p><input class="button" type="submit" value="'
						. $words -> __get('change') . '" /></p></form>');
					}
				}
				else
				{
					throw new ExceptionDisplay('No filename specified.');
				}
				break;
			}
			case 'edit_hidden':
			{
				if (!HIDDEN_FILES)
				{
					throw new ExceptionDisplay('The file hiding system is not in use. To enable it, reconfigure the script.');
				}
				global $hidden_list;
				if (isset($_GET['add']) && $_GET['add'] != '')
				{
					global $config;
					$h = @fopen($config -> __get('hidden_files'), 'ab');
					if ($h === false)
					{
						throw new ExceptionDisplay('Unable to open hidden files list for writing.');
					}
					fwrite($h, $_GET['add'] . "\n");
					fclose($h);
					throw new ExceptionDisplay('Hidden file added.');
				}
				if (isset($_GET['remove']))
				{
					global $config;
					$h = @fopen($config -> __get('hidden_files'), 'wb');
					if ($h === false)
					{
						throw new ExceptionDisplay('Unable to open hidden files list for writing.');
					}
					foreach ($hidden_list as $hid)
					{
						if ($hid != $_GET['remove'])
						{
							fwrite($h, $hid . "\n");
						}
					}
					fclose($h);
					throw new ExceptionDisplay('Hidden file removed.');
				}
				global $words;
				$str = '<h4>' . $words -> __get('add a new hidden file') . ':</h4>'
				. '<p class="autoindex_small">You can also use wildcards (?, *, +) for each entry.<br />'
				. 'If you want to do the opposite of "hidden files" - show only certain files - '
				. 'put a colon in front of those entries.</p><form method="get" action="'
				. Url::html_output($_SERVER['PHP_SELF']) . '"><p><input type="hidden" name="action" value="edit_hidden" />'
				. '<input type="text" name="add" size="40" /> <input type="submit" value="'
				. $words -> __get('add') . '" /></p></form>';
				
				$str .= '<hr class="autoindex_hr" /><h4>' . $words -> __get('remove a hidden file')
				. ':</h4><form method="get" action="'
				. Url::html_output($_SERVER['PHP_SELF']) . '"><p><select name="remove">';
				foreach ($hidden_list as $hid)
				{
					$str .= '<option>' . Url::html_output($hid) . '</option>';
				}
				$str .= '</select><input type="hidden" name="action" value="edit_hidden" /> <input type="submit" value="'
				. $words -> __get('remove') . '" /></p></form>';
				throw new ExceptionDisplay($str);
			}
			case 'edit_banned':
			{
				if (!BANNED_LIST)
				{
					throw new ExceptionDisplay('The banning system is not in use. To enable it, reconfigure the script.');
				}
				if (isset($_GET['add']) && $_GET['add'] != '')
				{
					global $config;
					$h = @fopen($config -> __get('banned_list'), 'ab');
					if ($h === false)
					{
						throw new ExceptionDisplay('Unable to open banned_list for writing.');
					}
					fwrite($h, $_GET['add'] . "\n");
					fclose($h);
					throw new ExceptionDisplay('Ban added.');
				}
				if (isset($_GET['remove']))
				{
					global $b_list, $config;
					$h = @fopen($config -> __get('banned_list'), 'wb');
					if ($h === false)
					{
						throw new ExceptionDisplay('Unable to open banned_list for writing.');
					}
					foreach ($b_list as $ban)
					{
						if ($ban != $_GET['remove'])
						{
							fwrite($h, $ban . "\n");
						}
					}
					fclose($h);
					throw new ExceptionDisplay('Ban removed.');
				}
				global $b_list, $words;
				$str = '<h4>' . $words -> __get('add a new ban') . ':</h4><form method="get" action="'
				. Url::html_output($_SERVER['PHP_SELF']) . '"><p><input type="hidden" name="action" value="edit_banned" />'
				. '<input type="text" name="add" size="40" /> <input type="submit" value="'
				. $words -> __get('add') . '" /></p></form>';
				
				$str .= '<hr class="autoindex_hr" /><h4>'
				. $words -> __get('remove a ban') . ':</h4><form method="get" action="'
				. Url::html_output($_SERVER['PHP_SELF']) . '"><p><select name="remove">';
				foreach ($b_list as $ban)
				{
					$str .= '<option>' . $ban . '</option>';
				}
				$str .= '</select><input type="hidden" name="action" value="edit_banned" /> <input type="submit" value="'
				. $words -> __get('remove') . '" /></p></form>';
				throw new ExceptionDisplay($str);
			}
			case 'stats':
			{
				if (!LOG_FILE)
				{
					throw new ExceptionDisplay('The logging system has not been enabled.');
				}
				$stats = new Stats();
				$stats -> display();
				break;
			}
			case 'view_log':
			{
				if (!LOG_FILE)
				{
					throw new ExceptionDisplay('The logging system has not been enabled.');
				}
				global $log;
				if (isset($_GET['num']))
				{
					$log -> display((int)$_GET['num']);
				}
				global $words;
				throw new ExceptionDisplay($words -> __get('how many entries would you like to view')
				. '?<form method="get" action="' . Url::html_output($_SERVER['PHP_SELF'])
				. '"><input type="hidden" name="action" value="view_log" />'
				. '<input name="num" size="3" type="text" /> <input type="submit" value="'
				. $words -> __get('view') . '" /></form>');
			}
			case 'create_dir':
			{
				if (isset($_GET['name']))
				{
					global $dir;
					if (!self::mkdir_recursive($dir . $_GET['name']))
					{
						throw new ExceptionDisplay('Error creating new folder.');
					}
				}
				else
				{
					global $words, $subdir;
					throw new ExceptionDisplay('<p>' . $words -> __get('enter the new name')
					. ':</p><form method="get" action="'
					. Url::html_output($_SERVER['PHP_SELF']) . '"><p><input type="hidden" name="action" value="create_dir" />'
					. '<input name="name" size="25" type="text" /> <input type="submit" value="'
					. $words -> __get('create') . '" /><input type="hidden" name="dir" value="'
					. $subdir . '" /></p></form>');
				}
				break;
			}
			case 'copy_url':
			{
				if (isset($_GET['protocol'], $_GET['copy_file']))
				{
					self::copy_remote_file(rawurldecode($_GET['protocol']), rawurldecode($_GET['copy_file']));
					throw new ExceptionDisplay('Copy was successful.');
				}
				global $dir;
				$text = '
				<table border="0" cellpadding="8" cellspacing="0">
				<tr class="paragraph"><td class="autoindex_td" style="padding: 8px;">
				<p>Enter the name of the remote file you would like to copy:</p>
				<form method="get" action="' . Url::html_output($_SERVER['PHP_SELF']) . '">
				<p><input type="hidden" name="action" value="copy_url" />
				<input type="hidden" name="dir" value="' . $dir . '" />
				<input type="radio" name="protocol" value="http://" checked="checked" />http://
				<br /><input type="radio" name="protocol" value="ftp://" />ftp://
				<input type="text" name="copy_file" /></p>
				<p><input class="button" type="submit" value="Copy" />
				</p></form></td></tr></table>';
				echo new Display($text);
				die();
			}
			case 'ftp':
			{
				if (isset($_POST['host'], $_POST['port'], $_POST['directory'],
					$_POST['ftp_username'], $_POST['ftp_password']))
				{
					if ($_POST['host'] == '')
					{
						throw new ExceptionDisplay('Please go back and enter a hostname.');
					}
					if ($_POST['ftp_username'] == '' && $_POST['ftp_password'] == '')
					//anonymous login
					{
						$_POST['ftp_username'] = 'anonymous';
						$_POST['ftp_password'] = 'autoindex@sourceforge.net';
					}
					if ($_POST['directory'] == '')
					{
						$_POST['directory'] = './';
					}
					if ($_POST['port'] == '')
					{
						$_POST['port'] = 21;
					}
					$_SESSION['ftp'] = array(
						'host' => $_POST['host'],
						'port' => (int)$_POST['port'],
						'directory' => Item::make_sure_slash($_POST['directory']),
						'username' => $_POST['ftp_username'],
						'password' => $_POST['ftp_password'],
						'passive' => isset($_POST['passive'])
						);
				}
				if (isset($_GET['set_dir']))
				{
					$_SESSION['ftp']['directory'] = $_GET['set_dir'];
				}
				global $subdir;
				if (isset($_GET['ftp_logout']))
				{
					unset($_SESSION['ftp']);
					$text = '<p>Logout successful. <a class="autoindex_a" href="'
					. Url::html_output($_SERVER['PHP_SELF']) . '?dir='
					. rawurlencode($subdir) . '">Go back.</a></p>';
				}
				else if (isset($_SESSION['ftp']))
				{
					try
					{
						$ftp = new Ftp($_SESSION['ftp']['host'], $_SESSION['ftp']['port'],
						$_SESSION['ftp']['passive'], $_SESSION['ftp']['directory'],
						$_SESSION['ftp']['username'], $_SESSION['ftp']['password']);
					}
					catch (ExceptionFatal $e)
					{
						unset($_SESSION['ftp']);
						throw $e;
					}
					if (isset($_GET['filename']) && $_GET['filename'] != '')
					//transfer local to FTP
					{
						global $dir;
						$name = rawurldecode($_GET['filename']);
						$ftp -> put_file($dir . $name, Item::get_basename($name));
						throw new ExceptionDisplay('File successfully transferred to FTP server.');
					}
					if (isset($_GET['transfer']) && $_GET['transfer'] != '')
					//transfer FTP to local
					{
						global $dir;
						$name = rawurldecode($_GET['transfer']);
						$ftp -> get_file($dir . Item::get_basename($name), $name);
						throw new ExceptionDisplay('File successfully transferred from FTP server.');
					}
					global $words;
					$text = '<ul><li><a href="' . Url::html_output($_SERVER['PHP_SELF'])
					. '?action=ftp&amp;dir=' . rawurlencode($subdir) . '&amp;set_dir='
					. rawurlencode(DirItem::get_parent_dir($_SESSION['ftp']['directory']))
					. '">../ (' . $words -> __get('parent directory') . ')</a></li>';
					$i = 0;
					foreach ($ftp as $file)
					{
						$is_directory = $ftp -> is_directory($i++);
						$command = ($is_directory ? 'set_dir' : 'transfer');
						$slash = ($is_directory ? '/' : '');
						$text .= '<li><a class="autoindex_a" href="'
						. Url::html_output($_SERVER['PHP_SELF']) . '?action=ftp&amp;'
						. $command . '=' . rawurlencode($file)
						. '&amp;dir=' . rawurlencode($subdir) . '">'
						. $file . $slash . '</a></li>' . "\n";
					}
					$text .= '</ul><p><a class="autoindex_a" href="'
					. Url::html_output($_SERVER['PHP_SELF']) . '?action=ftp&amp;dir='
					. rawurlencode($subdir) . '&amp;ftp_logout=true">Logout of FTP server</a>
					<br /><a href="' . Url::html_output($_SERVER['PHP_SELF']) . '?dir='
					. rawurlencode($subdir) . '">Back to index.</a></p>';
				}
				else
				{
					$text = '<form method="post" action="'
					. Url::html_output($_SERVER['PHP_SELF']) . '?action=ftp&amp;dir='
					. rawurlencode($subdir) . '"><table border="0" cellpadding="8" cellspacing="0">
					<tr class="paragraph"><td class="autoindex_td" style="padding: 8px;">
					<p>FTP server: <input type="text" name="host" />
					port <input type="text" size="3" name="port" value="21" />
					<br /><input type="checkbox" name="passive" value="true" />Passive Mode</p>
					<p>Username: <input type="text" name="ftp_username" />
					<br />Password: <input type="password" name="ftp_password" />
					<span class="autoindex_small">(Leave these blank to login anonymously)</span>
					</p><p>Directory: <input type="text" name="directory" value="./" />
					</p><p><input type="submit" value="Connect" /></p></td></tr></table></form>
					<p><a class="autoindex_a" href="' . Url::html_output($_SERVER['PHP_SELF'])
					. '?dir=' . rawurlencode($subdir) . '">Back to index.</a></p>';
				}
				echo new Display($text);
				die();
			}
			default:
			{
				throw new ExceptionDisplay('Invalid admin action.');
			}
		}
	}
	
	/**
	 * @return string The HTML text that makes up the admin panel
	 */
	public function __toString()
	{
		global $words, $subdir;
		$str = '';
		
		//only ADMIN accounts
		if ($this -> level >= ADMIN) $str = '
<p>
	<a href="' . Url::html_output($_SERVER['PHP_SELF']) . '?action=config" class="autoindex_a">'
	. $words -> __get('reconfigure script') . '</a>
</p>
<p>
	<a href="' . Url::html_output($_SERVER['PHP_SELF']) . '?action=edit_hidden" class="autoindex_a">'
	. $words -> __get('edit list of hidden files') . '</a>
	<br /><a href="' . Url::html_output($_SERVER['PHP_SELF']) . '?action=edit_banned" class="autoindex_a">'
	. $words -> __get('edit ban list') . '</a>
</p>
<p>
	<a href="' . Url::html_output($_SERVER['PHP_SELF']) . '?action=create_dir&amp;dir=' . rawurlencode($subdir)
	. '" class="autoindex_a">' . $words -> __get('create new directory in this folder')
	. '</a><br /><a href="' . Url::html_output($_SERVER['PHP_SELF']) . '?action=copy_url&amp;dir='
	. $subdir . '" class="autoindex_a">' . $words -> __get('copy url') . '</a>
</p>
<p>
	<a href="' . Url::html_output($_SERVER['PHP_SELF']) . '?action=view_log" class="autoindex_a">'
	. $words -> __get('view entries from log file') . '</a>
	<br /><a href="' . Url::html_output($_SERVER['PHP_SELF']) . '?action=stats" class="autoindex_a">'
	. $words -> __get('view statistics from log file') . '</a>
</p>
<p>
	<a href="' . Url::html_output($_SERVER['PHP_SELF']) . '?action=add_user" class="autoindex_a">'
	. $words -> __get('add new user') . '</a>
	<br /><a href="' . Url::html_output($_SERVER['PHP_SELF']) . '?action=del_user" class="autoindex_a">'
	. $words -> __get('delete user') . '</a>
	<br /><a href="' . Url::html_output($_SERVER['PHP_SELF']) . '?action=change_user_level" class="autoindex_a">
	Change a user\'s level</a>
</p>';
		//MODERATOR and ADMIN accounts
		if ($this -> level >= MODERATOR) $str .= '
<p>
	<a href="' . Url::html_output($_SERVER['PHP_SELF']) . '?action=change_password" class="autoindex_a">
	Change your password</a>
</p>
<p>
	<a href="' . Url::html_output($_SERVER['PHP_SELF']) . '?action=ftp&amp;dir=' . rawurlencode($subdir)
	. '" class="autoindex_a">FTP browser</a>
</p>';
		return $str;
	}
}

?>
