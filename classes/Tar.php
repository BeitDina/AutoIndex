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
 * Creates and outputs a tar archive given an array of filenames.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.1 (July 03, 2004)
 * @package AutoIndex
 */
class Tar
{
	/**
	 * @var int Length of directory path to cut off from start
	 */
	private $base_dir_length;
	
	/**
	 * @var string Added in the filepath inside the tar archive
	 */
	private $prepend_path;
	
	/**
	 * @param string $data
	 * @return int The checksum of $data
	 */
	private static function checksum(&$data)
	{
		$unsigned_chksum = 0;
		for ($i = 0; $i < 512; $i++)
		{
			$unsigned_chksum += ord($data{$i});
		}
		for ($i = 148; $i < 156; $i++)
		{
			$unsigned_chksum -= ord($data{$i});
		}
		return $unsigned_chksum + 256;
	}
	
	/**
	 * @param string $name The file or folder name
	 * @param int $size The size of the file (0 for directories)
	 * @param bool $is_dir True if folder, false if file
	 */
	private function create_header($name, $size = 0, $is_dir = true)
	{
		$header = str_pad($this -> prepend_path . substr($name, $this -> base_dir_length), 100, "\0") //filename
		. str_pad('755', 7, '0', STR_PAD_LEFT) . "\0" //permissions
		. '0000000' . "\0" //uid
		. '0000000' . "\0" //gid
		. str_pad(decoct($size), 11, '0', STR_PAD_LEFT) . "\0" //size
		. str_pad(decoct(filemtime($name)), 11, '0', STR_PAD_LEFT) . "\0" //time
		. '        ' //checksum (8 spaces)
		. ($is_dir ? '5' : '0') //typeflag
		. str_repeat("\0", 100) //linkname
		. 'ustar  ' //magic
		/*
		 * version (1) + username (32) + groupname (32) + devmajor (8) +
		 * devminor (8) + prefix (155) + end (12) = 248
		 */
		. str_repeat("\0", 248);
		
		$checksum = str_pad(decoct(self::checksum($header)), 6, '0', STR_PAD_LEFT) . "\0 ";
		return substr_replace($header, $checksum, 148, strlen($checksum));
	}
	
	/**
	 * @param DirectoryList $filenames List of files to add to the archive
	 * @param string $prepend_path Added in the filepath inside the tar archive
	 * @param int $base_dir_length Length of directory path to cut off from start
	 */
	public function __construct(DirectoryList $filenames, $prepend_path = '', $base_dir_length = 0)
	{
		$this -> base_dir_length = (int)$base_dir_length;
		$this -> prepend_path = Item::make_sure_slash($prepend_path);
		foreach ($filenames as $base)
		{
			$name = $filenames -> __get('dir_name') . $base;
			if (@is_dir($name))
			{
				if ($base != '.' && $base != '..')
				{
					echo $this -> create_header($name);
					$list = new DirectoryList($name);
					new Tar($list, $this -> prepend_path, $this -> base_dir_length);
				}
			}
			else if (@is_file($name) && @is_readable($name) && ($size = @filesize($name)))
			{
				echo $this -> create_header($name, $size, false);
				Url::force_download($name, false);
				echo str_repeat("\0", (ceil($size / 512) * 512) - $size);
			}
		}
	}
}

?>