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
 * Allows admins to connect to FTP servers and perform various actions.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.0 (February 16, 2005)
 * @package AutoIndex
 */
class Ftp extends DirectoryList
{
	/**
	 * @var resource The FTP connection handle
	 */
	private $handle;
	
	/**
	 * @var array Array of bools, for each entry
	 */
	private $is_directory;
	
	/**
	 * Returns if the $i'th entry is a directory or not.
	 *
	 * @param int $i The file/folder entry to check
	 * @return bool True if directory, false if file
	 */
	public function is_directory($i)
	{
		return $this -> is_directory[$i];
	}
	
	/**
	 * Reads the contents of the directory $path from the FTP server.
	 *
	 * @param string $path
	 */
	private function update_list($path)
	{
		$path = Item::make_sure_slash($path);
		$is_dir = $this -> contents =  array();
		$this -> dir_name = $path;
		$raw_list = @ftp_rawlist($this -> handle, $path);
		if ($raw_list === false)
		{
			throw new ExceptionDisplay('Unable to read directory contents of FTP server.');
		}
		foreach ($raw_list as $file)
		{
			if ($file == '')
			{
				continue;
			}
			$name = strrchr($file, ' ');
			if ($name === false)
			{
				continue;
			}
			$this -> is_directory[] = (strtolower($file{0}) === 'd');
			$this -> contents[] = $path . substr($name, 1);
		}
		$this -> list_count = count($this -> contents);
		$this -> i = 0;
	}
	
	/**
	 * @param string $local
	 * @param string $remote
	 */
	public function get_file($local, $remote)
	{
		if (!@ftp_get($this -> handle, $local, $remote, FTP_BINARY))
		{
			throw new ExceptionDisplay('Unable to transfer file from FTP server.');
		}
	}
	
	/**
	 * @param string $local
	 * @param string $remote
	 */
	public function put_file($local, $remote)
	{
		if (!@ftp_put($this -> handle, $remote, $local, FTP_BINARY))
		{
			throw new ExceptionDisplay('Unable to transfer file to FTP server.');
		}
	}
	
	/**
	 * @param string $host
	 * @param int $port
	 * @param bool $passive
	 * @param string $directory Directory to view
	 * @param string $username To login with
	 * @param string $password To login with
	 */
	public function __construct($host, $port, $passive, $directory, $username, $password)
	{
		$this -> handle = @ftp_connect(trim($host), (int)$port);
		if ($this -> handle === false)
		{
			throw new ExceptionDisplay('Could not connect to FTP server.');
		}
		if (!@ftp_login($this -> handle, $username, $password))
		{
			throw new ExceptionDisplay('Incorrect login for FTP server.');
		}
		if ($passive && !@ftp_pasv($this -> handle, true))
		{
			throw new ExceptionDisplay('Could not set passive mode for FTP server.');
		}
		$this -> update_list($directory);
	}
	
	/**
	 * Closes the open FTP connection when the object is destroyed.
	 */
	public function __destruct()
	{
		ftp_close($this -> handle);
	}
}

?>