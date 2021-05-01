<?php
/**
*
* @package AutoIndex
* @version $Id: stream.php,v 1.4 2008/03/21 20:18:42 orynider Exp $
* @copyright (c) 2003 [orynider@rdslink.ro, OryNider] github.com/Mx-Publisher Development Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
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

/**********************************************************************
 *                            MODIFICATIONS
 *                           ---------------
 *   started            : Saturday, February 28, 2007
 *   copyright          : © OryNider
 *   web              	: http://pubory.uv.ro/
 *   version            : 2.0.4
 *
 *   Credits:
 *	-Getting ip and port in settings by lsn (http://botland.org/)
 *
 ***********************************************************************/

// AX
if (!defined('IN_AUTOINDEX') || !IN_AUTOINDEX)
{
	die("Hacking attempt");
}

/**
 * Generates a video steam of an video file.
 *
 * @author FlorinCB <orynider@gmail.com>
 * @version 2.0.4 (Jan 27, 2007)
 * @package AutoIndex
 */
class Stream
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
	 * @param string $fn The filename
	 * @return string Everything after the list dot in the filename, not including the dot
	 */
	public static function ext($fn)
	{
		$fn = Item::get_basename($fn);
		return (strpos($fn, '.') ? strtolower(substr(strrchr($fn, '.'), 1)) : '');
	}
	
	/**
	 * @return string Returns the extension of the filename
	 * @see FileItem::ext()
	 */
	public function file_ext()
	{
		return self::ext($this -> filename);
	}
	
	/**
	 * Outputs the video stream along with the correct headers so the
	 * browser will display it. The script is then exited.
	 */
	public function __toString()
	{
		$thumbnail_height = $this -> height;
		$filepath = $this -> filename;

		if (!@is_file($filepath))
		{
			header('HTTP/1.0 404 Not Found');
			throw new ExceptionDisplay('Video file not found: <em>' . Url::html_output($filepath) . '</em>');
		}
		$file = Item::get_basename($filepath);

		// ------------------------------------
		// Check the request
		// ------------------------------------

		// ------------------------------------
		// Check the permissions
		// ------------------------------------

		// ------------------------------------
		// Check hotlink
		// ------------------------------------

		/*
		+----------------------------------------------------------
		| Main work here...
		+----------------------------------------------------------
		*/
	
		$ip = '127.0.0.0'; //localhost
		$port = '80';
		
		$mount = "/"; // Used for alternate path to "Streaming URL" -- leave as "/" for the default setup.

		$wmpmode = ($protocol_type == 'icyx:') ? 'icyx://' : 'http://';	// AAC VS MPEG
		$mimetype = ($protocol_type == 'icyx:') ? 'audio/aacp' : 'audio/x-mpeg';	// AAC VS MPEG

		//Other
		$artist = "Video Steam -via- AutoIndex";
		$title = "Video Steam !";
		$album = "Live";

		// Make socket connection
		$errno = "errno";
		$errstr = "errstr";

		//$station_url = str_replace("/listen.pls", "", htmlspecialchars(trim($thissong['station_url']))); 

		$size = filesize($filepath);
		static $u = array('B', 'K', 'M', 'G');
		for ($i = 0; $size >= 1024 && $i < 4; $i++)
		{
			$size /= 1024;
		}
		$filesize = number_format($size, 1) . ' ' . $u[$i];
		
		// Establish response headers
		//header("HTTP/1.0 200 OK");
		//Get media file content type
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		//Display correct headers for media file
		$mimetype = finfo_file($finfo, $filepath);
		header("Content-Type: $mimetype, application/octet-stream");
		header("Content-Transfer-Encoding: binary");

		// Content-Length is required for Internet Explorer:
		// - Set to a rediculous number
		// = I think the limit is somewhere around 420 MB
		// 
		ini_set('memory_limit', '512M');

		// Create send headers
		//echo "here".finfo_file($finfo, $filepath); 
		finfo_close($finfo);
		header('Content-length: ' . filesize($filepath));
		//header("Content-Disposition: attachment; filename=$title")."\n";
		header('Content-Disposition: inline; filename="'.$file.'"');
		//header('X-Sendfile: ' . $filepath); 
		header('Cache-Control: public, max-age=3600, must-revalidate');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
		readfile($filepath);
		//die();
	}

	/**
	 * @param string $file The video file
	*/
	public function __construct($file)
	{
		if (!THUMBNAIL_HEIGHT)
		{
			throw new ExceptionDisplay('Video streaming is turned off.');
		}
		global $config;
		$this -> height = (int)$config -> __get('thumbnail_height');
		$this -> filename = $file;
	}
}
// +------------------------------------------------------+
// |    Powered by Mx Music Center 2.0.1 (c) 2007 OryNider|
// +------------------------------------------------------+

?>