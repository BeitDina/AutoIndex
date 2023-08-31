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
	 * Massage the SVG image data for converters which don't understand some path data syntax.
	 *
	 * This is necessary for rsvg and ImageMagick when compiled with rsvg support.
	 * Upstream bug is https://bugzilla.gnome.org/show_bug.cgi?id=620923, fixed 2014-11-10, so
	 * this will be needed for a while. (T76852)
	 *
	 * @param string $svg SVG image data
	 * @return string Massaged SVG image data
	 */
	protected function massageSvgPathdata($svg) 
	{
		// load XML into simplexml
		$xml = simplexml_load_file($svg);

		// if the XML is valid
		if ( $xml instanceof SimpleXMLElement ) 
		{
			$dom = new DOMDocument( '1.0', 'utf-8' );
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true;

			// use it as a source
			$dom->loadXML( $xml->asXML() );
			
			foreach ($dom->getElementsByTagName('path') as $node)
			{
				$pathData = $node->getAttribute('d');
				// Make sure there is at least one space between numbers, and that leading zero is not omitted.
				// rsvg has issues with syntax like "M-1-2" and "M.445.483" and especially "M-.445-.483".
				$pathData = preg_replace('/(-?)(\d*\.\d+|\d+)/', ' ${1}0$2 ', $pathData);
				// Strip unnecessary leading zeroes for prettiness, not strictly necessary
				$pathData = preg_replace('/([ -])0(\d)/', '$1$2', $pathData);
				$node->setAttribute('d', $pathData);
			}
			return $dom->saveXML();
		}
	}
	
	/**
	 * Convert passed image data, which is assumed to be SVG, to PNG.
	 *
	 * @param string $file SVG image data
	 * @return string|bool PNG image data, or false on failure
	 */
	protected function imagecreatefromsvg($file) 
	{
		/**
		 * This code should be factored out to a separate method on SvgHandler, or perhaps a separate
		 * class, with a separate set of configuration settings.
		 *
		 * This is a distinct use case from regular SVG rasterization:
		 * * We can skip many sanity and security checks (as the images come from a trusted source,
		 *   rather than from the user).
		 * * We need to provide extra options to some converters to achieve acceptable quality for very
		 *   small images, which might cause performance issues in the general case.
		 * * We want to directly pass image data to the converter, rather than a file path.
		 *
		 * See https://phabricator.wikimedia.org/T76473#801446 for examples of what happens with the
		 * default settings.
		 *
		 * For now, we special-case rsvg (used in WMF production) and do a messy workaround for other
		 * converters.
		 */
		 
		$src = file_get_contents($file);
		$svg = $this->massageSvgPathdata($file);
		
		// Sometimes this might be 'rsvg-secure'. Long as it's rsvg.
		if ( strpos( CACHE_STORAGE_DIR, 'rsvg' ) === 0 ) 
		{
			$command = 'rsvg-convert';
			if ( CACHE_STORAGE_DIR )
			{
				$command = Shell::escape(CACHE_STORAGE_DIR) . $command;
			}

			$process = proc_open(
				$command,
				[ 0 => [ 'pipe', 'r' ], 1 => [ 'pipe', 'w' ] ],
				$pipes
			);

			if ( is_resource( $process ) ) 
			{
				fwrite( $pipes[0], $svg );
				fclose( $pipes[0] );
				$png = stream_get_contents( $pipes[1] );
				fclose( $pipes[1] );
				proc_close( $process );

				return $png ?: false;
			}
			return false;

		} 
		else 
		{
			
			// Write input to and read output from a temporary file  
			$tempFilenameSvg = CACHE_STORAGE_DIR . 'ResourceLoaderImage.svg';
			$tempFilenamePng = CACHE_STORAGE_DIR . 'ResourceLoaderImage.png';
			
			@copy($file, $tempFilenameSvg);
			@file_put_contents( $tempFilenameSvg, $src );
			
			$typeString = "image/png";
			$command =  'cd ~' . CACHE_STORAGE_DIR . ' && java -jar batik-rasterizer.jar ' . $tempFilenameSvg . ' -m ' .$typeString;
			//$command = "java -jar ". CACHE_STORAGE_DIR . "batik-rasterizer.jar -m " . $typeString ." -d ". $tempFilenamePng . " -q " . THUMBNAIL_HEIGHT . " " . $tempFilenameSvg . " 2>&1"; 
			
			$process = proc_open(
				$command,
				[ 0 => [ 'pipe', 'r' ], 1 => [ 'pipe', 'w' ] ],
				$pipes
			);
			
			if ( is_resource( $process ) ) 
			{
				proc_close( $process );
			}
			else
			{
				$output = shell_exec($command);
				echo "Command: $command <br>";
				echo "Output: $output";
			}
			//$svgReader = new SVGReader($file);
			//$metadata = $svgReader->getMetadata();
			//if ( !isset( $metadata['width'] ) || !isset( $metadata['height'] ) ) 
			//{
				$metadata['width'] = $metadata['height'] = THUMBNAIL_HEIGHT;
			//}
			
			//loop to color each state as needed, something like
			$idColorArray = array(
				  "AL" => "339966",
				  "AK" => "0099FF",
				 "WI" => "FF4B00",
				 "WY" => "A3609B"
			);

			foreach($idColorArray as $state => $color)
			{
				//Where $color is a RRGGBB hex value
				$svg = preg_replace('/id="'.$state.'" style="fill: #([0-9a-f]{6})/', 
					   'id="'.$state.'" style="fill: #'.$color, $svg
				);
			}
			
				$im = @ImageCreateFromPNG($svg);
			
				//$im->readImageBlob($svg);

				// png settings
				//$im->setImageFormat(function_exists('imagecreatefrompng') ? 'png24' : 'jpeg');
				//$im->resizeImage($metadata['width'], $metadata['height'], (function_exists('imagecreatefrompng') ? imagick::FILTER_LANCZOS : ''), 1);  // Optional, if you need to resize

				// jpeg
				//$im->adaptiveResizeImage($metadata['width'], $metadata['height']); //Optional, if you need to resize

				//$im->writeImage($tempFilenamePng); // (or .jpg)
				
				//unlink( $tempFilenameSvg );
			
			//$png = null;
			//if ( $res === true ) 
			//{
			//	$png = file_get_contents( $tempFilenamePng );
			//	unlink( $tempFilenamePng );
			//}
			//return $png ?: false;
		}
		die($svg);
	}
	
	/**
	 * Outputs the jpeg image along with the correct headers so the
	 * browser will display it. The script is then exited.
	 */
	public function __toString()
	{
		$thumbnail_height = $this -> height;
		$file = $this -> filename;
		$file_icon = new Icon($file);
		$this -> icon = $file_icon -> __toString();
		if (!@is_file($file))
		{
			header('HTTP/1.0 404 Not Found');
			throw new ExceptionDisplay('Image file not found: <em>' . Url::html_output($file) . '</em>');
		}
		switch (FileItem::ext($file))
		{
			case 'gif':
			{
				$src = @imagecreatefromgif($file);
				break;
			}
			/*
			case 'thm':
			{
				$src = @exif_thumbnail($file, THUMBNAIL_HEIGHT, THUMBNAIL_HEIGHT, 'image/jpg');
				break;
			}
			*/
			case 'jpeg':
			case 'jpg':
			case 'jpe':
			case 'jfif' :
			{
				$src = @imagecreatefromjpeg($file);
				break;
			}
			case 'svg' :
			{
				$src = $this->imagecreatefromsvg($file);
				break;
			}
			case 'png':
			{
				$src = @imagecreatefrompng($file);
				break;
			}
			case 'bmp' :
			{
				$src = imagecreatefrombmp($file);
				break;
			}
			case 'xbm' :
			{
				$src= imagecreatefromxbm($file);
				break;
			}
			case 'xpm' :
			{
				$src = imagecreatefromxpm($file);
				break;
			}
			case 'wmv' :
			{
				ini_set('memory_limit', '512M');
				$src = function_exists('imagecreatefromwmv') ? imagecreatefromwmv($file) : imagecreatefromjpeg(str_replace('wmv', 'jpg', $file));
				break;
			}
			case 'avi' :
			case 'mp4' :
			case 'mpg' :
			case 'mp3' :
			case 'ogv' :
			{
				ini_set('memory_limit', '512M');
				$function = 'imagecreatefrom'.FileItem::ext($file);
				$src = function_exists($function) ? $$function($file) : imagecreatefromjpeg(str_replace('avi', 'jpg', $file));
				break;
			}
			case '3gp' :
			{
				ini_set('memory_limit', '512M');
				$src = function_exists('imagecreatefrom3gp') ? imagecreatefrom3gp($file) : imagecreatefromjpeg(str_replace('3gp', 'jpg', $file));
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
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
		
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
			imagecopyresampled($thumb, $src, 0, 0, 0, 0, $thumb_width, $thumbnail_height, $src_width, $src_height);
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
		//$this -> tn_path = $config -> __get('thumbnail_path');
		//$this -> tn_quality = $config -> __get('thumbnail_quality');
	}
}

?>
