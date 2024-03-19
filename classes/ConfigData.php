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
 * Reads information stored in files, where the key and data are separated by a
 * tab.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.2 (January 13, 2005)
 * @package AutoIndex
 */
class ConfigData implements Iterator
{
	/**
	 * @var array A list of all the settings
	*/
	private $config;
	
	/**
	 * @var string The name of the file to read the settings from
	*/
	private $filename;
	
	//begin implementation of Iterator
	/**
	 * @var bool
	*/
	private $valid;
	
	/**
	 * @return string
	*/
    #[\ReturnTypeWillChange] 
	public function current()
	{
		return current($this->config);
	}
	
	/**
	 * Increments the internal array pointer, and returns the new value.
	 *
	 * @return string
	 */
	 #[\ReturnTypeWillChange] 
	public function next()
	{
		$t = next($this->config);
		if ($t === false)
		{
			$this -> valid = false;
		}
		return $t;
	}
	
	/**
	 * Sets the internal array pointer to the beginning.
	*/
	#[\ReturnTypeWillChange] 
	public function rewind()
	{
		reset($this->config);
	}
	
	/**
	 * @return bool
	*/
	#[\ReturnTypeWillChange] 
	public function valid()
	{
		return $this->valid;
	}
	
	/**
	 * @return string
	*/
	#[\ReturnTypeWillChange]
	public function key()
	{
		return key($this->config);
	}
	//end implementation of Iterator
	
	/**
	 * @param string $line The line to test
	 * @return bool True if $line starts with characters that mean it is a comment
	 */
	public static function line_is_comment($line)
	{
		$line = trim($line);
		return (($line == '') || preg_match('@^(//|<\?|\?>|/\*|\*/|#)@', $line));
	}
	
	/**
	 * @param string $file The filename to read the data from
	 */
	public function __construct($file)
	{
		if ($file === false)
		{
			return;
		}
		$this -> valid = true;
		$this -> filename = $file;
		$contents = file($file);
		if ($contents === false)
		{
			throw new ExceptionFatal('Error reading file <em>' . Url::html_output($file) . '</em>');
		}
		foreach ($contents as $i => $line)
		{
			$line = rtrim($line, "\r\n");
			if (self::line_is_comment($line))
			{
				continue;
			}
			$parts = explode("\t", $line, 2);
			if (count($parts) !== 2 || $parts[0] == '' || $parts[1] == '')
			{
				throw new ExceptionFatal('Incorrect format for file <em>' . Url::html_output($file) . '</em> on line ' . ($i + 1) . '.<br />Format is "variable name[tab]value"');
			}
			if (isset($this -> config[$parts[0]]))
			{
				throw new ExceptionFatal('Error in <em>' . Url::html_output($file) . '</em> on line ' . ($i + 1) . '.<br />' . Url::html_output($parts[0]) . ' is already defined.');
			}
			$this->config[$parts[0]] = $parts[1];
		}
	}
	
	/**
	 * @param string $file we do not use explode() in PHP7+ 
	 * The filename to read the data from
	 */
	public function dos_description($full_name, $file = './descript.ion')
	{
		if ($file === false)
		{
			return;
		}
		$this -> valid = true;
		//trim path
		$file_dir = trim(dirname($file));
		//trim file name
		$file_name = trim(basename($full_name));
		if (strpos($full_name, '.') !== false)
		{
			// Nested file
			$filename_ext = substr(strrchr($full_name, '.'), 1);
		}
		//rebuild path
		$file_path = $file_dir . "/{$file_name}";
		
		$contents = file($file);
		if ($contents === false)
		{
			throw new ExceptionFatal('Error reading file <em>' . Url::html_output($file) . '</em>');
		}
		foreach ($contents as $i => $line)
		{
			$line = rtrim($line, "\r\n");
			if (self::line_is_comment($line))
			{
				continue;
			}
			$parts = explode($file_name, $line);
			if (count($parts) > 0)
			{
				//throw new ExceptionFatal('Incorrect format for file <em>explode on ' . $full_name . ' line: ' . print_r($line, true) . ' ' . Url::html_output($file) . '</em> on line ' . ($i + 1) . '.<br />Format is "file name[space]value"');
				return empty($parts[1]) ? $parts[0] : $parts[1];
			}
			return false;
		}
	}
	/**
	 * Returns a list of all files in $path that match the filename format
	 * of themes files.
	 *
	 * There are two valid formats for the filename of a template folder..
	 *
	 * @param string $path The directory to read from
	 * @return array The list of valid theme names (based on directory name)
	 */
	public static function get_all_styles($path = PATH_TO_TEMPLATES)
	{
		if (($hndl = @opendir($path)) === false)
		{
			echo 'Did try to open dir: ' . $path;
			return false;
		}
		
		$themes_array = $installable_themes = array();
		
		$style_id = 0;	
		while (($sub_dir = readdir($hndl)) !== false)
		{
			// get the sub-template path
			if( !is_file(@realpath($path . $sub_dir)) && !is_link(@realpath($path . $sub_dir)) && $sub_dir != "." && $sub_dir != ".." && $sub_dir != "CVS" )
			{
				if(@file_exists(realpath($path . $sub_dir . "/$sub_dir.css")) || @file_exists(realpath($path . $sub_dir . "/default.css")) )
				{
					$themes[] = array('template' => $path . $sub_dir . '/', 'template_name' => $sub_dir, 'style_id' => $style_id++);	
				}
			}
		}
		closedir($hndl);		
		
		return $themes;
	}	
	/**
	 * $config[$key] will be set to $info.
	 *
	 * @param string $key
	 * @param string $info
	 */
	public function set($key, $info)
	{
		$this->config[$key] = $info;
	}
	
	/**
	 * This will look for the key $item, and add one to the $info (assuming
	 * it is an integer).
	 *
	 * @param string $item The key to look for
	 */
	public function add_one($item)
	{
		if ($this->is_set($item))
		{
			$h = fopen($this->filename, 'wb');
			if ($h === false)
			{
				throw new ExceptionFatal('Could not open file <em>' . Url::html_output($this->filename) . '</em> for writing. Make sure PHP has write permission to this file.');
			}
			foreach ($this as $current_item => $count)
			{
				fwrite($h, "$current_item\t" . (($current_item == $item) ? ((int)$count + 1) : $count) . "\n");
			}
		}
		else
		{
			$h = fopen($this->filename, 'ab');
			if ($h === false)
			{
				throw new ExceptionFatal('Could not open file <em>' . $this->filename . '</em> for writing.' . ' Make sure PHP has write permission to this file.');
			}
			fwrite($h, "$item\t1\n");
		}
		fclose($h);
	}
	
	/**
	 * @param string $name The key to look for
	 * @return bool True if $name is set
	*/
	public function is_set($name)
	{
		return isset($this->config[$name]);
	}
	
	/**
	 * @param string $name The key to look for
	 * @return string The value $name points to
	 */
	public function __get($name)
	{
		global $request;
		
		if ($request->is_set_get('style') && $name == 'template')
		{
			$style = $request->is_set('style') ? $request->variable('style', '') : 0;				
			$themes = $this->get_all_styles($this->config['template_path']);	
			$template_path = $request->is_set('style') ? $themes[$style]['template'] : $this->config['template'];			
			return $template_path;	
		}	
		
		if (isset($this->config[$name]))
		{
			return $this->config[$name];
		}
		
		throw new ExceptionFatal('Setting <em>' . Url::html_output($name) . '</em> is missing in file <em>' . Url::html_output($this -> filename) . '</em>.');
	}
}

?>
