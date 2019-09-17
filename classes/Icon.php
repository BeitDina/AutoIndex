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
 * Given a filename, this will come up with an icon to represent the filetype.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.2 (August 07, 2004)
 * @package AutoIndex
 */
class Icon
{
	/**
	 * @var string Filename of the image file
	 */
	private $image_name;
	
	/**
	 * Given a file extension, this will come up with the filename of the
	 * icon to represent the filetype.
	 *
	 * @param string $ext The file extension to find the icon for
	 * @return string The appropriate icon depending on the extension
	 */
	private static function find_icon($ext)
	{
		if ($ext == '')
		{
			return 'generic';
		}
		static $icon_types = array(
		'binary' => array('bat', 'bin', 'com', 'dmg', 'dms', 'exe', 'msi',
			'msp', 'pif', 'pyd', 'scr', 'so'),
		'binhex' => array('hqx'),
		'cd' => array('bwi', 'bws', 'bwt', 'ccd', 'cdi', 'cue', 'img',
			'iso', 'mdf', 'mds', 'nrg', 'nri', 'sub', 'vcd'),
		'comp' => array('cfg', 'conf', 'inf', 'ini', 'log', 'nfo', 'reg'),
		'compressed' => array('7z', 'a', 'ace', 'ain', 'alz', 'amg', 'arc',
			'ari', 'arj', 'bh', 'bz', 'bz2', 'cab', 'deb', 'dz', 'gz',
			'io', 'ish', 'lha', 'lzh', 'lzs', 'lzw', 'lzx', 'msx', 'pak',
			'rar', 'rpm', 'sar', 'sea', 'sit', 'taz', 'tbz', 'tbz2',
			'tgz', 'tz', 'tzb', 'uc2', 'xxe', 'yz', 'z', 'zip', 'zoo'),
		'dll' => array('386', 'db', 'dll', 'ocx', 'sdb', 'vxd'),
		'doc' => array('abw', 'ans', 'chm', 'cwk', 'dif', 'doc', 'dot',
			'mcw', 'msw', 'pdb', 'psw', 'rtf', 'rtx', 'sdw', 'stw', 'sxw',
			'vor', 'wk4', 'wkb', 'wpd', 'wps', 'wpw', 'wri', 'wsd'),
		'image' => array('adc', 'art', 'bmp', 'cgm', 'dib', 'gif', 'ico',
			'ief', 'jfif', 'jif', 'jp2', 'jpc', 'jpe', 'jpeg', 'jpg', 'jpx',
			'mng', 'pcx', 'png', 'psd', 'psp', 'swc', 'sxd', 'tga',
			'tif', 'tiff', 'wmf', 'wpg', 'xcf', 'xif', 'yuv'),
		'java' => array('class', 'jar', 'jav', 'java', 'jtk'),
		'js' => array('ebs', 'js', 'jse', 'vbe', 'vbs', 'wsc', 'wsf',
			'wsh'),
		'key' => array('aex', 'asc', 'gpg', 'key', 'pgp', 'ppk'),
		'mov' => array('amc', 'dv', 'm4v', 'mac', 'mov', 'mp4v', 'mpg4',
			'pct', 'pic', 'pict', 'pnt', 'pntg', 'qpx', 'qt', 'qti',
			'qtif', 'qtl', 'qtp', 'qts', 'qtx'),
		'movie' => array('asf', 'asx', 'avi', 'div', 'divx', 'dvi', 'm1v',
			'm2v', 'mkv', 'movie', 'mp2v', 'mpa', 'mpe', 'mpeg', 'mpg',
			'mps', 'mpv', 'mpv2', 'ogm', 'ram', 'rmvb', 'rnx', 'rp', 'rv',
			'vivo', 'vob', 'wmv', 'xvid'),
		'pdf' => array('edn', 'fdf', 'pdf', 'pdp', 'pdx'),
		'php' => array('inc', 'php', 'php3', 'php4', 'php5', 'phps',
			'phtml'),
		'ppt' => array('emf', 'pot', 'ppa', 'pps', 'ppt', 'sda', 'sdd',
			'shw', 'sti', 'sxi'),
		'ps' => array('ai', 'eps', 'ps'),
		'sound' => array('aac', 'ac3', 'aif', 'aifc', 'aiff', 'ape', 'apl',
			'au', 'ay', 'bonk', 'cda', 'cdda', 'cpc', 'fla', 'flac',
			'gbs', 'gym', 'hes', 'iff', 'it', 'itz', 'kar', 'kss', 'la',
			'lpac', 'lqt', 'm4a', 'm4p', 'mdz', 'mid', 'midi', 'mka',
			'mo3', 'mod', 'mp+', 'mp1', 'mp2', 'mp3', 'mp4', 'mpc',
			'mpga', 'mpm', 'mpp', 'nsf', 'oda', 'ofr', 'ogg', 'pac', 'pce',
			'pcm', 'psf', 'psf2', 'ra', 'rm', 'rmi', 'rmjb', 'rmm', 'sb',
			'shn', 'sid', 'snd', 'spc', 'spx', 'svx', 'tfm', 'tfmx',
			'voc', 'vox', 'vqf', 'wav', 'wave', 'wma', 'wv', 'wvx', 'xa',
			'xm', 'xmz'),
		'tar' => array('gtar', 'tar'),
		'text' => array('asm', 'c', 'cc', 'cp', 'cpp', 'cxx', 'diff', 'h',
			'hpp', 'hxx', 'm3u', 'md5', 'patch', 'pls', 'py', 'sfv', 'sh',
			'txt'),
		'uu' => array('uu', 'uud', 'uue'),
		'web' => array('asa', 'asp', 'aspx', 'cfm', 'cgi', 'css', 'dhtml',
			'dtd', 'grxml', 'htc', 'htm', 'html', 'htt', 'htx', 'jsp', 'lnk',
			'mathml', 'mht', 'mhtml', 'perl', 'pl', 'plg', 'rss', 'shtm',
			'shtml', 'stm', 'swf', 'tpl', 'wbxml', 'xht', 'xhtml', 'xml',
			'xsl', 'xslt', 'xul'),
		'xls' => array('csv', 'dbf', 'prn', 'pxl', 'sdc', 'slk', 'stc', 'sxc',
			'xla', 'xlb', 'xlc', 'xld', 'xlr', 'xls', 'xlt', 'xlw'));
		foreach ($icon_types as $png_name => $exts)
		{
			if (in_array($ext, $exts))
			{
				return $png_name;
			}
		}
		return 'unknown';
	}
	
	/**
	 * @param string $filename The filename to find the icon for
	 */
	public function __construct($filename)
	{
		$this -> image_name = self::find_icon(FileItem::ext($filename));
	}
	
	/**
	 * @return string The full path to the icon file
	 */
	public function __toString()
	{
		global $config;
		return $config -> __get('icon_path')
		. $this -> image_name . '.png';
	}
}

?>