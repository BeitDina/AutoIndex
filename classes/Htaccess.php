<?php

/**
 * @package AutoIndex
 *
 * @copyright Copyright (C) 2002-2007 Justin Hagstrom
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
 * Parses .htaccess files and imports their settings to AutoIndex.
 *
 * These Apache directives are supported:
 * - <Directory>
 * - <Limit>
 * - <IfDefine>
 * - AddDescription
 * - IndexIgnore
 * - Include
 * - Order
 * - Deny from
 * - Allow from
 * - AuthUserFile
 * - AuthName
 * - Require user
 *
 * These password formats are supported for .htpasswd file:
 * - MD5
 * - SHA-1
 * - Crypt
 * - Apache's Custom MD5 Crypt
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.1 (January 6, 2007)
 * @package AutoIndex
 */
class Htaccess
{
	/**
	 * @var string "AuthName" setting
	 */
	private $auth_name;
	
	/**
	 * @var string "AuthUserFile" setting
	 */
	private $auth_user_file;
	
	/**
	 * @var array "Require user" setting
	 */
	private $auth_required_users;
	
	/**
	 * @var string "Order" setting
	 */
	private $order;
	
	/**
	 * @var array "Allow from" setting
	 */
	private $allow_list;
	
	/**
	 * @var array "Deny from" setting
	 */
	private $deny_list;
	
	/**
	 * Converts hexadecimal to binary.
	 *
	 * @param string $hex
	 * @return string
	 */
	private static function hex2bin($hex)
	{
		$bin = '';
		$ln = strlen($hex);
		for($i = 0; $i < $ln; $i += 2)
		{
			$bin .= chr(hexdec($hex{$i} . $hex{$i+1}));
		}
		return $bin;
	}
	
	/**
	 * Return the number of count from the value using base conversion.
	 *
	 * @param int $value
	 * @param int $count
	 * @return int
	 */
	private static function to64($value, $count)
	{
		static $root = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$result = '';
		while(--$count)
		{
			$result .= $root[$value & 0x3f];
			$value >>= 6;
		}
		return $result;
	}
	
	/**
	 * Implementation of Apache's Custom MD5 Crypt.
	 *
	 * @param string $plain The plaintext password
	 * @param string $salt The salt
	 * @return string The hashed password
	 */
	private static function md5_crypt($plain, $salt)
	{
		$length = strlen($plain);
		$context = $plain . '$apr1$' . $salt;
		$binary = self::hex2bin(md5($plain . $salt . $plain));
		for ($i = $length; $i > 0; $i -= 16)
		{
			$context .= substr($binary, 0, min(16, $i));
		}
		for ( $i = $length; $i > 0; $i >>= 1)
		{
			$context .= ($i & 1) ? chr(0) : $plain[0];
		}
		$binary = self::hex2bin(md5($context));
		for ($i = 0; $i < 1000; $i++) 
		{
			$new = ($i & 1) ? $plain : substr($binary, 0, 16);
			if ($i % 3)
			{
				$new .= $salt;
			}
			if ($i % 7)
			{
				$new .= $plain;
			}
			$new .= (($i & 1) ? substr($binary, 0, 16) : $plain);
			$binary = self::hex2bin(md5($new));
		}
		$p = array();
		for ($i = 0; $i < 5; $i++)
		{
			$k = $i + 6;
			$j = $i + 12;
			if ($j == 16)
			{
				$j = 5;
			}
			$p[] = self::to64(
				(ord($binary[$i]) << 16) |
				(ord($binary[$k]) << 8) |
				(ord($binary[$j])), 5
				);
		}
		return '$apr1$' . $salt . '$' . implode($p) . self::to64(ord($binary[11]), 3);
	}
	
	/**
	 * Tests if $test matches $target.
	 *
	 * @param string $test
	 * @param string $target
	 * @return bool True if $test matches $target
	 */
	private static function matches($test, $target)
	{
		static $replace = array(
			'\*' => '.*',
			'\+' => '.+',
			'\?' => '.?');
		return (bool)preg_match('/^' . strtr(preg_quote($test, '/'), $replace) . '$/i', $target);
	}
	
	/**
	 * Checks if AuthName and AuthUserFile are set, and then prompts for a
	 * username and password.
	 */
	private function check_auth()
	{
		if ($this -> auth_user_file == '')
		{
			return;
		}
		if ($this -> auth_name == '')
		{
			$this -> auth_name = '"Directory access restricted by AutoIndex"';
		}
		$validated = false;
		if (isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']))
		{
			$file = @file($this -> auth_user_file);
			if ($file === false)
			{
				$_GET['dir'] = '';
				throw new ExceptionDisplay('Cannot open .htpasswd file.
				<br /><em>' . htmlentities($this -> auth_user_file) . '</em>');
			}
			if ($this -> auth_required_users === array() || DirectoryList::match_in_array($_SERVER['PHP_AUTH_USER'], $this -> auth_required_users))
			{
				foreach ($file as $account)
				{
					$parts = explode(':', trim($account));
					if (count($parts) < 2 || $_SERVER['PHP_AUTH_USER'] != $parts[0])
					{
						continue;
					}
					if (isset($parts[2]))
					//MD5 hash format with realm
					{
						$parts[1] = $parts[2];
					}
					switch (strlen($parts[1]))
					{
						case 13:
						//Crypt hash format
						{
							$validated = (crypt($_SERVER['PHP_AUTH_PW'], substr($parts[1], 0, 2)) == $parts[1]);
							break 2;
						}
						case 32:
						//MD5 hash format
						{
							$validated = (md5($_SERVER['PHP_AUTH_PW']) == $parts[1]);
							break 2;
						}
						case 37:
						//Apache's MD5 Crypt hash format
						{
							$salt = explode('$', $parts[1]);
							$validated = (self::md5_crypt($_SERVER['PHP_AUTH_PW'], $salt[2]) == $parts[1]);
							break 2;
						}
						case 40:
						//SHA-1 hash format
						{
							$validated = (sha1($_SERVER['PHP_AUTH_PW']) == $parts[1]);
							break 2;
						}
					}
				}
			}
			sleep(1);
		}
		if (!$validated)
		{
			header('WWW-Authenticate: Basic realm=' . $this -> auth_name);
			header('HTTP/1.0 401 Authorization Required');
			$_GET['dir'] = '';
			throw new ExceptionDisplay('A username and password are required to access this directory.');
		}
	}
	
	/**
	 * Checks if the user's IP or hostname is either allowed or denied.
	 */
	private function check_deny()
	{
		global $ip, $host, $words;
		if ($this -> order === 'allow,deny')
		{
			if (!DirectoryList::match_in_array($host, $this -> allow_list)
				&& !DirectoryList::match_in_array($ip, $this -> allow_list))
			{
				$_GET['dir'] = '';
				throw new ExceptionDisplay($words -> __get('the administrator has blocked your ip address or hostname') . '.');
			}
		}
		else if (DirectoryList::match_in_array($ip, $this -> deny_list)
			|| DirectoryList::match_in_array($host, $this -> deny_list))
		{
			$_GET['dir'] = '';
			throw new ExceptionDisplay($words -> __get('the administrator has blocked your ip address or hostname') . '.');
		}
	}
	
	/**
	 * @param string $file The .htaccess file (name and path) to parse
	 */
	private function parse($file)
	{
		$data = @file($file);
		if ($data === false)
		{
			return;
		}
		$conditional_directory = '';
		$other_conditional = false;
		foreach ($data as $line)
		{
			$line = trim($line);
			if ($line == '')
			{
				continue;
			}
			if ($line{0} == '<')
			{
				if (preg_match('#^</\s*directory.*?>#i', $line))
				{
					$conditional_directory = '';
				}
				else if (preg_match('#^<\s*directory\s+\"?(.+?)\"?\s*>#i', $line, $matches))
				{
					$conditional_directory = $matches[1];
				}
				else if (preg_match('#^</?\s*limit.*?>#i', $line))
				{
					//ignore <Limit> tags
					continue;
				}
				else if (preg_match('#^</\s*ifdefine.*?>#i', $line))
				{
					$conditional_defined = '';
				}
				else if (preg_match('#^<\s*ifdefine\s+(.+?)\s*>#i', $line, $matches))
				{
					$conditional_defined = $matches[1];
				}
				else if (isset($line{1}))
				{
					$other_conditional = ($line{1} != '/');
				}
				continue;
			}
			global $dir;
			if ($other_conditional || $conditional_directory != '' && !self::matches($conditional_directory, $dir))
			//deal with <Directory> or an unknown < > tag
			{
				continue;
			}
			if ($conditional_defined != '')
			//deal with <IfDefine>
			{
				$conditional_defined = strtoupper($conditional_defined);
				if ($conditional_defined{0} === '!')
				{
					$conditional_defined = substr($conditional_defined, 1);
					if (defined($conditional_defined) && constant($conditional_defined))
					{
						continue;
					}
				}
				else if (!defined($conditional_defined) || !constant($conditional_defined))
				{
					continue;
				}
			}
			$parts = preg_split('#\s#', $line, -1, PREG_SPLIT_NO_EMPTY);
			switch (strtolower($parts[0]))
			{
				case 'indexignore':
				{
					global $hidden_files;
					for ($i = 1; $i < count($parts); $i++)
					{
						$hidden_files[] = $parts[$i];
					}
					break;
				}
				case 'include':
				{
					if (isset($parts[1]) && @is_file($parts[1]) && @is_readable($parts[1]))
					{
						self::parse($parts[1]);
					}
					break;
				}
				case 'allow':
				{
					if (isset($parts[1]) && strtolower($parts[1]) === 'from')
					{
						for ($i = 2; $i < count($parts); $i++)
						{
							foreach (explode(',', $parts[$i]) as $ip)
							{
								if (strtolower($ip) === 'all')
								{
									$this -> allow_list = array('*');
								}
								else
								{
									$this -> allow_list[] = $ip;
								}
							}
						}
					}
					break;
				}
				case 'deny':
				{
					if (isset($parts[1]) && strtolower($parts[1]) === 'from')
					{
						for ($i = 2; $i < count($parts); $i++)
						{
							foreach (explode(',', $parts[$i]) as $ip)
							{
								if (strtolower($ip) === 'all')
								{
									$this -> deny_list = array('*');
								}
								else
								{
									$this -> deny_list[] = $ip;
								}
							}
						}
					}
					break;
				}
				case 'adddescription':
				{
					global $descriptions;
					if (!isset($descriptions))
					{
						$descriptions = new ConfigData(false);
					}
					for ($i = 1; isset($parts[$i], $parts[$i+1]); $i += 2)
					{
						$descriptions -> set($parts[$i], $parts[$i+1]);
					}
					break;
				}
				case 'authuserfile':
				{
					if (isset($parts[1]))
					{
						$this -> auth_user_file = str_replace('"', '', implode(' ', array_slice($parts, 1)));
					}
					break;
				}
				case 'authname':
				{
					if (isset($parts[1]))
					{
						$this -> auth_name = implode(' ', array_slice($parts, 1));
					}
					break;
				}
				case 'order':
				{
					if (isset($parts[1]) && (strtolower($parts[1]) === 'allow,deny' || strtolower($parts[1]) === 'mutual-failure'))
					{
						$this -> order = 'allow,deny';
					}
				}
				case 'require':
				{
					if (isset($parts[1]) && strtolower($parts[1]) === 'user')
					{
						for ($i = 2; $i < count($parts); $i++)
						{
							$this -> auth_required_users[] = $parts[$i];
						}
					}
					break;
				}
			}
		}
	}
	
	/**
	 * @param string $dir The deepest folder to parse for .htaccess files
	 * @param string $filename The name of the files to look for
	 */
	public function __construct($dir, $filename = '.htaccess')
	{
		$this -> auth_name = $this -> auth_user_file = '';
		$this -> auth_required_users = $this -> allow_list = $this -> deny_list = array();
		$this -> order = 'deny,allow';
		if (DirItem::get_parent_dir($dir) != '')
		//recurse into parent directories
		{
			new Htaccess(DirItem::get_parent_dir($dir));
		}
		$dir = Item::make_sure_slash($dir);
		$file = $dir . $filename;
		if (@is_file($file) && @is_readable($file))
		{
			$this -> parse($dir . $filename);
			$this -> check_deny();
			$this -> check_auth();
		}
	}
}

?>