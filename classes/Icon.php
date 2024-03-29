<?php
/**
 * @package AutoIndex
 *
 * @copyright Copyright (C) 2002-2004 Justin Hagstrom, 2019-2023 Florin C Bodin aka orynider at github.com
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
	die('bad class init...');
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
	private $icon_name;
	
	/**
	 * Given a file extension, this will come up with the filename of the
	 * icon to represent the filetype.
	 *ş
	 * @param string $ext The file extension to find the icon for
	 * @return string The appropriate icon depending on the extension
	 */
	private static function find_icon($ext, $name)
	{			
		if (($ext == '') || ($ext == 'md'))
		{
			switch($name)
			{
				case 'README':
				case 'ReadMe':
					return 'readme';
				break;
				case 'CODE_OF_CONDUCT':
					return 'conduct';
				break;
				case 'LICENSE':
					return 'license';
				break;
				case 'SECURITY':
					return 'security';
				break;
				default:
					return 'generic';
			}					
		}		
		static $icon_types = array(
		'binary' => array('patch', 'bin', 'dmg', 'dms', 'exe', 'msi', 'msp', 'pyd', 'scr', 'so'),
		'binhex' => array('hqx'),
		'conduct' => array('cnd'),
		'readme' => array('wri', 'md'),
		'license' => array('tql'),
		'security' => array('sec', 'cer', 'der', 'crt', 'spc', 'p7b', 'p12', 'pfx'),
		'key' => array('key', 'pem', 'pub', 'fin'),		
		'cd' => array('bwi', 'bws', 'bwt', 'ccd', 'cdi', 'cue', 'img', 'iso', 'mdf', 'mds', 'nrg', 'nri', 'sub', 'vcd'),
		'command' => array('bat', 'cmd', 'com', 'lnk', 'pif'),
		'comp' => array('cfg', 'conf', 'inf', 'ini', 'log', 'nfo', 'sys'),
		'registry' => array('reg', 'hiv'),		
		'compressed' => array('7z', 'a', 'ace', 'ain', 'alz', 'amg', 'arc',
			'ari', 'arj', 'bh', 'bz', 'bz2', 'cab', 'deb', 'dz', 'gz',
			'io', 'ish', 'lha', 'lzh', 'lzs', 'lzw', 'lzx', 'msx', 'pak',
			'rar', 'rpm', 'sar', 'sea', 'sit', 'taz', 'tbz', 'tbz2',
			'tgz', 'tz', 'tzb', 'uc2', 'xxe', 'yz', 'z', 'zip', 'zoo'),
		'dll' => array('386', 'db', 'dll', 'ocx', 'sdb', 'vxd', 'drv'),		
		'doc' => array('abw', 'ans', 'chm', 'cwk', 'dif', 'doc', 'dot',
			'mcw', 'msw', 'pdb', 'psw', 'rtf', 'rtx', 'sdw', 'stw', 'sxw',
			'vor', 'wk4', 'wkb', 'wpd', 'wps', 'wpw', 'wsd'),
		'image' => array('adc', 'art', 'bmp', 'cgm', 'dib', 'gif', 'ico',
			'ief', 'jfif', 'jif', 'jp2', 'jpc', 'jpe', 'jpeg', 'jpg', 'jpx',
			'mng', 'pcx', 'png', 'psd', 'psp', 'swc', 'sxd', 'svg', 'tga',
			'tif', 'tiff', 'wmf', 'wpg', 'xcf', 'xif', 'yuv'),
		'bible' => array('bbl', 'bblx', 'ot', 'nt', 'toc'),
		'java' => array('class', 'jar', 'jav', 'java', 'jtk'),
		'js' => array('ebs', 'js', 'jse', 'vbe', 'vbs', 'wsc', 'wsf', 'wsh'),
		'key' => array('aex', 'asc', 'gpg', 'key', 'pgp', 'ppk'),
		'mov' => array('amc', 'dv', 'm4v', 'mac', 'mov', 'pct', 'pic', 'pict', 'pnt', 'pntg', 'qpx', 'qt', 'qti', 'qtif', 'qtl', 'qtp', 'qts', 'qtx'),
		'movie' => array('asf', 'asx', 'avi', 'div', 'divx', 'dvi', 'm1v', 'm2v', 'mkv', 'movie', 'mp2v', 'mpa', 'mpe', 'mpeg', 'mpg', 'mp4v', 'mp4', 'mpg4', 'mps', 'mpv', 'mpv2', 'ogm', 'ram', 'rmvb', 'rnx', 'rp', 'rv', 'vivo', 'vob', 'wmv', 'xvid'),
		'fnt' => array('fnt', 'bdf'),
		'fon' => array('fon'),
		'ttf' => array('ttf'),
		'otf' => array('otf'),
		'sfd' => array('sfd'),
		'afm' => array('afm'),
		'eot' => array('eot'),
		'woff' => array('woff'),
		'woff2' => array('woff2'),
		'pdf' => array('edn', 'fdf', 'pdf', 'pdp', 'pdx'),
		'php' => array('inc', 'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'php9', 'phps', 'phtml'),
		'ppt' => array('emf', 'pot', 'ppa', 'pps', 'ppt', 'sda', 'sdd', 'shw', 'sti', 'sxi'),
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
		'csharp' => array('csproj', 'cs'),
		'prog' => array('asm', 'c', 'cc', 'cp', 'cpp', 'cxx', 'diff', 'h', 'hpp', 'hxx', 'md5', 'patch', 'py', 'sfv', 'sh'),
		'play' => array('m3u', 'pls'),
		'text' => array('md5', 'txt'),
		'uu' => array('uu', 'uud', 'uue'),
		'web' => array('asa', 'asp', 'aspx', 'cfm', 'cgi', 'css', 'dhtml',
			'dtd', 'grxml', 'htc', 'htm', 'html', 'htt', 'htx', 'jsp',
			'mathml', 'mht', 'mhtml', 'perl', 'pl', 'plg', 'rss', 'shtm',
			'shtml', 'stm', 'swf', 'tpl', 'wbxml', 'xht', 'xhtml', 'xml', 'xsl', 'xslt', 'xul'),
		'xls' => array('csv', 'dbf', 'prn', 'pxl', 'sdc', 'slk', 'stc', 'sxc', 'xla', 'xlb', 'xlc', 'xld', 'xlr', 'xls', 'xlt', 'xlw'));
		
		foreach ($icon_types as $png_name => $exts)
		{
			if (in_array($ext, $exts))
			{
				return $png_name;
			}
		}
		
		switch($name)
		{
			case 'README':
			case 'ReadMe':
				return 'readme';
			break;
			case 'CODE_OF_CONDUCT':
				return 'conduct';
			break;
			case 'LICENSE':
				return 'license';
			break;
			case 'SECURITY':
				return 'security';
			break;
			default:
				return 'unknown';
		}	
	}
	
	/**
	 * @param string $filename The filename to find the icon for
	 */
	public function __construct($filename)
	{
		$this->icon_name = self::find_icon(FileItem::ext($filename), FileItem::ext($filename, false));
	}
	
	/**
	 * @return string The full path to the icon file
	 */
	public function __toString()
	{
		global $config;
		
		return $config->__get('icon_path')
		. $this->icon_name . '.png';
	}
}

?>
