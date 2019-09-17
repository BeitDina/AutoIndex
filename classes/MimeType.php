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
 * Given a filename extension, this will come up with the appropriate MIME-type.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.1 (February 09, 2005)
 * @package AutoIndex
 */
class MimeType
{
	/**
	 * @var string The filename's MIME-type
	 */
	private $mime;
	
	/**
	 * @var string The default MIME-type to return
	 */
	private $default_type;
	
	/**
	 * Given a file extension, this will come up with the file's appropriate
	 * MIME-type.
	 *
	 * @param string $ext The file extension to find the MIME-type for
	 * @return string The appropriate MIME-type depending on the extension
	 */
	private function find_mime_type($ext)
	{
		static $mime_types = array(
			'application/andrew-inset' => array('ez'),
			'application/mac-binhex40' => array('hqx'),
			'application/mac-compactpro' => array('cpt'),
			'application/mathml+xml' => array('mathml'),
			'application/msword' => array('doc'),
			'application/octet-stream' => array('bin', 'dms', 'lha',
				'lzh', 'exe', 'class', 'so', 'dll', 'dmg'),
			'application/oda' => array('oda'),
			'application/ogg' => array('ogg'),
			'application/pdf' => array('pdf'),
			'application/postscript' => array('ai', 'eps', 'ps'),
			'application/rdf+xml' => array('rdf'),
			'application/smil' => array('smi', 'smil'),
			'application/srgs' => array('gram'),
			'application/srgs+xml' => array('grxml'),
			'application/vnd.mif' => array('mif'),
			'application/vnd.mozilla.xul+xml' => array('xul'),
			'application/vnd.ms-excel' => array('xls'),
			'application/vnd.ms-powerpoint' => array('ppt'),
			'application/vnd.wap.wbxml' => array('wbxml'),
			'application/vnd.wap.wmlc' => array('wmlc'),
			'application/vnd.wap.wmlscriptc' => array('wmlsc'),
			'application/voicexml+xml' => array('vxml'),
			'application/x-bcpio' => array('bcpio'),
			'application/x-cdlink' => array('vcd'),
			'application/x-chess-pgn' => array('pgn'),
			'application/x-cpio' => array('cpio'),
			'application/x-csh' => array('csh'),
			'application/x-director' => array('dcr', 'dir', 'dxr'),
			'application/x-dvi' => array('dvi'),
			'application/x-futuresplash' => array('spl'),
			'application/x-gtar' => array('gtar'),
			'application/x-hdf' => array('hdf'),
			'application/x-javascript' => array('js'),
			'application/x-koan' => array('skp', 'skd', 'skt', 'skm'),
			'application/x-latex' => array('latex'),
			'application/x-netcdf' => array('nc', 'cdf'),
			'application/x-sh' => array('sh'),
			'application/x-shar' => array('shar'),
			'application/x-shockwave-flash' => array('swf'),
			'application/x-stuffit' => array('sit'),
			'application/x-sv4cpio' => array('sv4cpio'),
			'application/x-sv4crc' => array('sv4crc'),
			'application/x-tar' => array('tar'),
			'application/x-tcl' => array('tcl'),
			'application/x-tex' => array('tex'),
			'application/x-texinfo' => array('texinfo', 'texi'),
			'application/x-troff' => array('t', 'tr', 'roff'),
			'application/x-troff-man' => array('man'),
			'application/x-troff-me' => array('me'),
			'application/x-troff-ms' => array('ms'),
			'application/x-ustar' => array('ustar'),
			'application/x-wais-source' => array('src'),
			'application/xhtml+xml' => array('xhtml', 'xht'),
			'application/xslt+xml' => array('xslt'),
			'application/xml' => array('xml', 'xsl'),
			'application/xml-dtd' => array('dtd'),
			'application/zip' => array('zip'),
			'audio/basic' => array('au', 'snd'),
			'audio/midi' => array('mid', 'midi', 'kar'),
			'audio/mpeg' => array('mpga', 'mp2', 'mp3'),
			'audio/x-aiff' => array('aif', 'aiff', 'aifc'),
			'audio/x-mpegurl' => array('m3u'),
			'audio/x-pn-realaudio' => array('ram', 'ra'),
			'application/vnd.rn-realmedia' => array('rm'),
			'audio/x-wav' => array('wav'),
			'chemical/x-pdb' => array('pdb'),
			'chemical/x-xyz' => array('xyz'),
			'image/bmp' => array('bmp'),
			'image/cgm' => array('cgm'),
			'image/gif' => array('gif'),
			'image/ief' => array('ief'),
			'image/jpeg' => array('jpeg', 'jpg', 'jpe'),
			'image/png' => array('png'),
			'image/svg+xml' => array('svg'),
			'image/tiff' => array('tiff', 'tif'),
			'image/vnd.djvu' => array('djvu', 'djv'),
			'image/vnd.wap.wbmp' => array('wbmp'),
			'image/x-cmu-raster' => array('ras'),
			'image/x-icon' => array('ico'),
			'image/x-portable-anymap' => array('pnm'),
			'image/x-portable-bitmap' => array('pbm'),
			'image/x-portable-graymap' => array('pgm'),
			'image/x-portable-pixmap' => array('ppm'),
			'image/x-rgb' => array('rgb'),
			'image/x-xbitmap' => array('xbm'),
			'image/x-xpixmap' => array('xpm'),
			'image/x-xwindowdump' => array('xwd'),
			'model/iges' => array('igs', 'iges'),
			'model/mesh' => array('msh', 'mesh', 'silo'),
			'model/vrml' => array('wrl', 'vrml'),
			'text/calendar' => array('ics', 'ifb'),
			'text/css' => array('css'),
			'text/html' => array('html', 'htm'),
			'text/plain' => array('asc', 'txt'),
			'text/richtext' => array('rtx'),
			'text/rtf' => array('rtf'),
			'text/sgml' => array('sgml', 'sgm'),
			'text/tab-separated-values' => array('tsv'),
			'text/vnd.wap.wml' => array('wml'),
			'text/vnd.wap.wmlscript' => array('wmls'),
			'text/x-setext' => array('etx'),
			'video/mpeg' => array('mpeg', 'mpg', 'mpe'),
			'video/quicktime' => array('qt', 'mov'),
			'video/vnd.mpegurl' => array('mxu', 'm4u'),
			'video/x-msvideo' => array('avi'),
			'video/x-sgi-movie' => array('movie'),
			'x-conference/x-cooltalk' => array('ice')
		);
		foreach ($mime_types as $mime_type => $exts)
		{
			if (in_array($ext, $exts))
			{
				return $mime_type;
			}
		}
		return $this -> default_type;
	}
	
	/**
	 * @param string $filename The filename to find the MIME-type for
	 * @param string $default_type The default MIME-type to return
	 */
	public function __construct($filename, $default_type = 'text/plain')
	{
		$this -> default_type = $default_type;
		$this -> mime = $this -> find_mime_type(FileItem::ext($filename));
	}
	
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this -> mime;
	}
}

?>