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
 * Generates a thumbnail of an image file.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.0 (May 22, 2004)
 * @package AutoIndex
 */
class Image
{
	/**
	 * @var string Name of the image file
	 */
	private $filename;
	
	/**
	 * @var int The height of the thumbnail to create (width is automatically determined)
	 */
	private $height;
	
	/**
	 * Outputs the jpeg image along with the correct headers so the
	 * browser will display it. The script is then exited.
	 */
	public function __toString()
	{
		$thumbnail_height = $this -> height;
		$file = $this -> filename;
		if (!@is_file($file))
		{
			header('HTTP/1.0 404 Not Found');
			throw new ExceptionDisplay('Image file not found: <em>'
			. Url::html_output($file) . '</em>');
		}
		switch (FileItem::ext($file))
		{
			case 'gif':
			{
				$src = @imagecreatefromgif($file);
				break;
			}
			case 'jpeg':
			case 'jpg':
			case 'jpe':
			{
				$src = @imagecreatefromjpeg($file);
				break;
			}
			case 'png':
			{
				$src = @imagecreatefrompng($file);
				break;
			}
			case 'php':
			{
				$src = str_replace('php', 'png', $file);
				//JN (GPL)
				$file_header = 'Content-type: image/png';

				srand ((float) microtime() * 10000000);
				$quote = rand(1, 6);

				switch($quote)
				{
					case "1":
						$rand_quote = "MXP-CMS Team, mxp.sf.net";
					break;

					case "2":
						$rand_quote = "in between milestones edition ;)";
					break;

					case "3":
						$rand_quote = "MX-Publisher, Fully Modular Portal & CMS for phpBB";
					break;

					case "4":
						$rand_quote = "Portal & CMS Site Creation Tool";
					break;

					case "5":
						$rand_quote = "...pafileDB, FAP, MX-Publisher, Translator";
					break;

					case "6":
						$rand_quote = "...Calendar, Links & News...modules";
					break;
				}

				$pic_title = $rand_quote;
				$pic_title_reg = preg_replace("/[^A-Za-z0-9]/", "_", $pic_title);

				$current_release = "3.0.0";

				$im = @ImageCreateFromPNG($src);
				$pic_size = @getimagesize($src);

				$pic_width = $pic_size[0];
				$pic_height = $pic_size[1];

				$dimension_font = 1;
				$dimension_filesize = filesize($src);
				$dimension_string = intval($pic_width) . 'x' . intval($pic_height) . '(' . intval($dimension_filesize / 1024) . 'KB)';

				$blue = ImageColorAllocate($im, 6, 108, 159);

				$dimension_height = imagefontheight($dimension_font);
				$dimension_width = imagefontwidth($dimension_font) * strlen($current_release);
				$dimension_x = ($thumbnail_width - $dimension_width) / 2;
				$dimension_y = $thumbnail_height + ((16 - $dimension_height) / 2);
				//ImageString($im, 2, $dimension_x, $dimension_y, $current_release, $blue);
				@ImageString($im, 2, 125, 2, $current_release, $blue);
				@ImageString($im, 2, 20, 17, $rand_quote, $blue);

				@Header($file_header);
				Header("Expires: Mon, 1, 1999 05:00:00 GMT");
				Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
				Header("Cache-Control: no-store, no-cache, must-revalidate");
				Header("Cache-Control: post-check=0, pre-check=0", false);
				Header("Pragma: no-cache");
				@ImagePNG($im);
				exit;
				break;
			}
			default:
			{
				throw new ExceptionDisplay('Unsupported file extension.');
			}
		}
		if ($src === false)
		{
			throw new ExceptionDisplay('Unsupported image type.');
		}
		
		header('Content-Type: image/jpeg');
		header('Cache-Control: public, max-age=3600, must-revalidate');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600)
		. ' GMT');
		$src_height = imagesy($src);
		if ($src_height <= $thumbnail_height)
		{
			imagejpeg($src, '', 95);
		}
		else
		{
			$src_width = imagesx($src);
			$thumb_width = $thumbnail_height * ($src_width / $src_height);
			$thumb = imagecreatetruecolor($thumb_width, $thumbnail_height);
			imagecopyresampled($thumb, $src, 0, 0, 0, 0, $thumb_width,
				$thumbnail_height, $src_width, $src_height);
			imagejpeg($thumb);
			imagedestroy($thumb);
		}
		imagedestroy($src);
		die();
	}
	
	/**
	 * @param string $file The image file
	 */
	public function __construct($file)
	{
		if (!THUMBNAIL_HEIGHT)
		{
			throw new ExceptionDisplay('Image thumbnailing is turned off.');
		}
		global $config;
		$this -> height = (int)$config -> __get('thumbnail_height');
		$this -> filename = $file;
	}
}

?>