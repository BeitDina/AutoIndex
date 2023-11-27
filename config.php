<?php
/**
 * When a file with the stored config data is not present, this file is
 * automatically included to create a new one.
 *
 * @package AutoIndex
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>, FlorinCB <orynider@users.sourceforge.net>
 * @version 2.2.6 (January 01, 2019 / 15, November, 2023)
 *
 * @copyright Copyright (C) 2002-2008 Justin Hagstrom
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

if (!defined('IN_AUTOINDEX') || !IN_AUTOINDEX || !defined('ROOT_PATH'))
{
	die('Please use index.php to acces this directory.');
}

//Main Install default value for hidden files 
//Do not change the values if You are not sure what to do
$CONFIG_PATH = ROOT_PATH;	
$default_template_name = 'default';	
$access_log_file_name = 'access.log';
$description_file = 'description_file';
$user_list_file = '.htpasswd.autoindex';
$download_count_file = 'download_count';
$hidden_files_file = 'hidden_files';
$banned_list_file = 'banned_users';



/**
* Checking Request Class
**/
$request = is_object($request) ? $request : new RequestVars('', false);

$strings = array('base_dir', 'icon_path', 'flag_path', 'language', 'template_path', 'assets_path', 'template', 'log_file',
	'description_file', 'user_list', 'download_count', 'hidden_files', 'banned_list');
$checkboxes = array('show_dir_size', 'use_login_system', 'force_download',
	'search_enabled', 'anti_leech', 'must_login_to_download', 'archive', 'parse_htaccess');
$numbers = array('days_new', 'thumbnail_height', 'bandwidth_limit', 'md5_show', 'entries_per_page');

//begin display of "configuration complete" page
$install_header_css = '
			<style type="text/css" title="AutoIndex Default">
				html, body
				{
					font-family: verdana, lucidia, sans-serif;
					font-size: 14px;
					background-color: #F0F0F0;
					color: #000000;
				}
				a
				{
					color: #000000;
					text-decoration: none;
				}
				hr
				{
					color: #000020;
					background-color: #000020;
					border: none;
					width: 75%;
					height: 1px;
				}
				h3
				{
					text-align: center;
					color: #000000;
				}
				td
				{
					font-family: verdana, lucidia, sans-serif;
					font-size: 14px;
					color: #000000;
					border: 1px solid #7F8FA9;
				}
				tr
				{
					background: #F2F6FC;
					color: #000020;
				}
				.small
				{
					font-size: 11px;
					color: #000000;
				}
			</style>';
			
//debug code here: print_r('post array: ' . $request->post_array() . ', strings: ' . count($strings) . ', numbers: ' . count($numbers));
if ($request->post_array() >= count($strings) + count($numbers))
{
	$directories = array('base_dir', 'icon_path', 'flag_path', 'assets_path', 'template_path', 'template');
	$output = "<?php\n\n/* AutoIndex PHP Script config file\n\n";
	foreach ($strings as $setting)
	{
		if ($request->is_not_set_post($setting))
		{
			die(simple_display('Required setting <em>' . htmlentities($setting) . '</em> not set.'));
		}
		
		if ($request->is_empty_post($setting))
		{
			$output .= "$setting\tfalse\n";
			continue;
		}
		
		$request->recursive_set_var($setting, str_replace('\\', '/', $request->post($setting)), false);
		if (in_array($setting, $directories) && !preg_match('#/$#', $request->post($setting)))
		//make sure there is a slash at the end of directories
		{
			$request->recursive_set_var($setting, $request->post($setting) . '/', false);		
		}
		$output .= "$setting\t{$request->post($setting)}\n";
	}
	
	$_POST[$setting] = $request->post($setting);	
	foreach ($checkboxes as $setting)
	{
		$output .= "$setting\t" . ($request->is_post($setting) ? 'true' : 'false') . "\n";
	}
	
	foreach ($numbers as $setting)
	{
		if ($request->is_not_set_post($setting))
		{
			die(simple_display('Required setting <em>' . htmlentities($setting) . '</em> not set.'));
		}
		
		if ($request->is_empty_post($setting))
		{
			$output .= "$setting\t0\n";
			continue;
		}
		
		if ($request->post($setting) < 0)
		{
			die(simple_display('The setting <em>' . htmlentities($setting) . '</em> should not be a negative number.'));
		}
		$request->recursive_set_var($setting, (string)((float)$request->post($setting)), false);
		$output .= "$setting\t{$request->post($setting)}\n";
	}
	$output .= "\n*/\n\n?>";
	
	if ($request->is_not_set_post('force_download'))
	{
		if (preg_match('#^(/|[a-z]\:)#i', $request->post('base_dir')))
		{
			die(simple_display('It seems you are using an absolute path for the Base Directory.' . '<br />This means you must check the "Pipe downloaded files though the PHP script" box.'));
		}
		
		if ((int)$request->post('bandwidth_limit') !== 0)
		{
			die(simple_display('For the Bandwidth Limit feature to work, the "force download" feature needs to be on.' . '<br />This means you must check the "Pipe downloaded files though the PHP script" box.'));
		}
	}
	
	if ($request->is_set_post('must_login_to_download') && $request->is_not_set_post('use_login_system'))
	{
		die(simple_display('To enable <em>must_login_to_download</em>, the ' . '<em>use_login_system</em> option must also be turned on.'));
	}
	
	foreach (array('base_dir', 'template') as $setting)
	{
		$valid = $request->post($setting);
		if (!opendir($valid))
		{
			die(simple_display(htmlentities($valid) . ' for ' . htmlentities($setting) . ' setting is not a valid directory.'));
		}
		else
		{
			closedir($valid);
		}			
	}
	
	if (@is_file(CONFIG_STORED))
	//if the file already exists, back it up
	{
		$temp_name = CONFIG_STORED . '.bak';
		for ($i = 1; @file_exists($temp_name); $i++)
		{
			$temp_name = CONFIG_STORED . '.bak' . (string) $i;
		}
		@copy(CONFIG_STORED, $temp_name);
	}
	
	$h = @fopen(CONFIG_STORED, 'wb');
	if ($h === false)
	//the file could not be written to, so now it must be downloaded through the browser
	{
		header('Content-Type: text/plain; name="' . CONFIG_STORED . '"');
		header('Content-Disposition: attachment; filename="' . CONFIG_STORED . '"');
		die($output);
	}
	else
	//the .php file was opened successfully, so we write to it
	{
		fwrite($h, $output);
		fclose($h);	
		
		print '<?xml version="1.0" encoding="utf-8"?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd" />
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
		<head>
			<meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
			<meta http-equiv="Content-Style-Type" content="text/css" />

			<meta name="title"       content="AutoIndex Configuration" />
			<meta name="author"      content="Beit Dina Bible Arheology and Translation Institute @ beitdina.net" />
			<meta name="copyright"   content="default template © Beit Dina 2019 based on subSilver style © 2005 phpBB Group." />
			<meta name="keywords"    content="Beit, Dina, Bible, Arheology" />
			<meta name="description" lang="en" content="Directory Index. This is the description search engines show when listing your site." />
			<meta name="category"    content="general" />
			<meta name="robots"      content="index,follow" />
			<meta name="revisit-after" content="7 days" >
			
			<meta http-equiv="X-UA-Compatible" content="IE=edge" />
			<meta name="viewport" content="width=device-width, initial-scale=1.0" />
			<meta name="apple-mobile-web-app-capable" content="yes" />
			<meta name="apple-mobile-web-app-status-bar-style" content="blue" />
			
			<title>AutoIndex Configuration: Complete!</title>
			'.$install_header_css.'
			<link rel="stylesheet" href="'.PATH_TO_TEMPLATES.'/'.$default_template_name.'/'.$default_template_name.'.css" type="text/css" />
		</head>;
		<body>
		<table border="0" cellpadding="5" cellspacing="0">
			<tr>
				<td>
					<p>Write successful!<br />AutoIndex configuration is finished.</p>
					<p><a href="' . $request->server('PHP_SELF') .'">Continue</a>.</p>
				</td>
			</tr>
		</table>
		</body>
		</html>';
		die();
	}
}

/** if we're reconfiguring the script, use the current settings:
*/
$settings = !isset($settings) ? array() : $settings;
global $config;

/**
* now we need to include either the stored settings, or the config generator:
**/
if (!isset($config))
{
	if (is_file(CONFIG_STORED)) 
	{
		if (!is_readable(CONFIG_STORED))
		{
			print("This is fresh install so the script will attempt to write a file named <em>" . CONFIG_STORED . "</em> to the <em>" . ROOT_PATH . "</em> directory.");
		}
		$config = new ConfigData(CONFIG_STORED);
	}
}

/**
* Go through each config setting, and set a constant with each setting's
 * name to either true or false depending on if the config setting is enabled.
 **/
if (isset($config))
{
	foreach ($config as $key => $item)
	{
		//For security poposes var $config is a private variable so this will not work:
		$settings[$key] = $config->__get($key);	
	}
	//So we list the default settings one by one
	$settings = array(
		'base_dir'			=> $config->__get('base_dir'),
		'assets_path'		=> $config->__get('assets_path'),
		'icon_path'			=> $config->__get('icon_path'),
		'flag_path'			=> $config->__get('flag_path'),
		'language'			=> $config->__get('language'),
		'template'			=> $config->__get('template'),
		'template_path'		=> $config->__get('template_path'),
		'log_file'			=> $config->__get('log_file'),
		'description_file'	=> $config->__get('user_list'),
		'user_list'			=> $config->__get('user_list'),
		'download_count'	=> $config->__get('download_count'),
		'hidden_files'		=> $config->__get('hidden_files'),
		'banned_list'		=> $config->__get('banned_list'),
		'show_dir_size'		=> $config->__get('show_dir_size'),
		'use_login_system'	=> $config->__get('use_login_system'),
		'force_download'	=> $config->__get('force_download'),
		'search_enabled'	=> $config->__get('search_enabled'),
		'anti_leech'		=> $config->__get('anti_leech'),
		'must_login_to_download' => $config->__get('must_login_to_download'),
		'archive'			=> $config->__get('archive'),
		'days_new'			=> $config->__get('days_new'),
		'entries_per_page'	=> $config->__get('entries_per_page'),
		'thumbnail_height'	=> $config->__get('thumbnail_height'),
		'bandwidth_limit'	=> $config->__get('bandwidth_limit'),
		'md5_show'			=> $config->__get('md5_show'),
		'parse_htaccess'	=> $config->__get('parse_htaccess')
	);
}

/** doble check the directories
**/
//overwrite the base dir path
if(!empty($settings['base_dir']) && !is_file(@realpath($settings['base_dir'])) && !is_link(@realpath($settings['base_dir'])) && $settings['base_dir'] != "." && $settings['base_dir'] != ".." && $settings['base_dir'] != "CVS" )
{
	$CONFIG_PATH = $settings['base_dir'];
}
//overwrite the template base dir path
if(!empty($settings['template_path']) && !is_file(@realpath($settings['template_path'])) && !is_link(@realpath($settings['template_path'])) && $settings['template_path'] != "." && $settings['template_path'] != ".." && $settings['template_path'] != "CVS" )
{
	@define('PATH_TO_TEMPLATES', $settings['template_path']);	
}
// overwrite the classes dir path
if( !is_file(@realpath($CONFIG_PATH . 'classes/')) && !is_link(@realpath($CONFIG_PATH . 'classes/')) && $CONFIG_PATH . 'classes/' != "." && $CONFIG_PATH . 'classes/' != ".." && $CONFIG_PATH . 'classes/' != "CVS" )
{
	@define('PATH_TO_CLASSES', $CONFIG_PATH . 'classes/');
}
// overwrite the languages dir path
if( !is_file(@realpath($CONFIG_PATH . 'languages/')) && !is_link(@realpath($CONFIG_PATH . 'languages/')) && $CONFIG_PATH . 'languages/' != "." && $CONFIG_PATH . 'languages/' != ".." && $CONFIG_PATH . 'languages/' != "CVS" )
{
	@define('PATH_TO_LANGUAGES', $CONFIG_PATH . 'languages/');
}
// overwrite the flags dir path
if( !is_file(@realpath($CONFIG_PATH . 'flags/')) && !is_link(@realpath($CONFIG_PATH . 'flags/')) && $CONFIG_PATH . 'flags/' != "." && $CONFIG_PATH . 'flags/' != ".." && $CONFIG_PATH . 'flags/' != "CVS" )
{
	@define('PATH_TO_FLAGS', $CONFIG_PATH . 'flags/');
}
// overwrite the index_icons dir path
if( !is_file(@realpath($CONFIG_PATH . 'index_icons/')) && !is_link(@realpath($CONFIG_PATH . 'index_icons/')) && $CONFIG_PATH . 'index_icons/' != "." && $CONFIG_PATH . 'index_icons/' != ".." && $CONFIG_PATH . 'index_icons/' != "CVS" )
{
	@define('PATH_TO_ICONS', $CONFIG_PATH . 'index_icons/');
}
// overwrite the assets dir path
if( !is_file(@realpath($CONFIG_PATH . 'assets/')) && !is_link(@realpath($CONFIG_PATH . 'assets/')) && $CONFIG_PATH . 'assets/' != "." && $CONFIG_PATH . 'assets/' != ".." && $CONFIG_PATH . 'assets/' != "CVS" )
{
	@define('PATH_TO_ASSETS', $CONFIG_PATH . 'assets/');
}

//List Templates GNU GPL v. 2.0 / Borrowed from github.com/Mx-Publisher/mxpcms
$installable_themes = array();
$current_template_name = isset($settings['template']) ? $settings['template'] : $default_template_name;

$lang_select = '	Default Language: <select name="language">';	
$l = Language::get_all_langs(PATH_TO_LANGUAGES);
if ($l === false)
{
	$l = array('en');
}
sort($l);
foreach ($l as $lang)
{
	$sel = (($lang == $settings['language']) ? ' selected="selected"' : '');
	$lang_select .= '\t\t<option ' . $sel . '>' . $lang . '</option>\n';
}			
$lang_select .= '	</select>';

// i.e. ./templates/SwiftBlueBeitDina/
$template = empty($settings['template']) ? PATH_TO_TEMPLATES . $current_template_name : $settings['template'];
$template_data = str_replace('/', '', explode(dirname($template), $template));
$template_name = !empty($template_data[1]) ? $template_data[1] : $default_template_name;

if ($dir = @opendir(PATH_TO_TEMPLATES))
{
	while($sub_dir = @readdir($dir))
	{
		// get the sub-template path
		if( !is_file(@realpath(PATH_TO_TEMPLATES .$sub_dir)) && !is_link(@realpath(PATH_TO_TEMPLATES .$sub_dir)) && $sub_dir != "." && $sub_dir != ".." && $sub_dir != "CVS" )
		{
			if( @file_exists(realpath(PATH_TO_TEMPLATES . $sub_dir . "/$sub_dir.css")) || @file_exists(realpath(PATH_TO_TEMPLATES . $sub_dir . "/default.css")) )
			{
				$installable_themes[] = array('template' => PATH_TO_TEMPLATES . $sub_dir . '/', 'template_name' => $sub_dir);				
			}
		}
	}
			
	$style_select = '	Template Directory: <input type="text" name="template_path" value="' . PATH_TO_TEMPLATES . '" /> Style:	<select name="template">';
	$selected1 = '';
	$style_select .= '\t\t<option value="-1"' . $selected1 . '>' . 'Select template style' . '</option>\n';
			
	for ($id = 0; $id < count($installable_themes); $id++)
	{			
		$selected = ($installable_themes[$id]['template'] == $current_template_name && !$selected1) ? ' selected="selected"' : '';
		$style_select .= '\t\t<option value="' . $installable_themes[$id]['template'] . '"' . $selected . '>' . $installable_themes[$id]['template_name'] . '</option>\n';
	}
	$style_select .= '	</select>';	
}
else	
{	
	$style_select = '<input type="text" name="template" value="' . $template . '" /><input type="text" name="template_path" value="' . PATH_TO_TEMPLATES . '" />';
}
closedir($dir);	

//list of default settings
$settings = array(
	'base_dir' => empty($settings['base_dir']) ? $CONFIG_PATH : $settings['base_dir'],
	'assets_path' => empty($settings['assets_path']) ? PATH_TO_ASSETS : $settings['assets_path'],
	'icon_path' => empty($settings['icon_path']) ? PATH_TO_ICONS . 'winvista/' : $settings['icon_path'], //To do: A list alike for languages
	'flag_path' => empty($settings['flag_path']) ? PATH_TO_FLAGS . 'language/' : $settings['flag_path'], //is 'language' or 'country' icons
	'language' => empty($settings['language']) ? 'en' : $settings['language'],
	'template_path' => empty($settings['template_path']) ? PATH_TO_TEMPLATES : $settings['template_path'],
	'template' => empty($settings['template']) ? $template : $settings['template'],
	'log_file' => empty($settings['log_file']) ? $access_log_file_name : $settings['log_file'],
	'description_file' => empty($settings['description_file']) ? $description_file : $settings['description_file'],
	'user_list' => empty($settings['user_list']) ? $user_list_file : $settings['user_list'],
	'download_count' => empty($settings['download_count']) ? $download_count_file : $settings['download_count'],
	'hidden_files' => empty($settings['hidden_files']) ? $hidden_files_file : $settings['hidden_files'],
	'banned_list' => empty($settings['banned_list']) ? $banned_list_file : $settings['banned_list'],
	'show_dir_size' => empty($settings['show_dir_size']) ? 'true' : $settings['show_dir_size'],
	'use_login_system' => empty($settings['use_login_system']) ? 'true' : $settings['use_login_system'],
	'force_download' => empty($settings['force_download']) ? 'false' : $settings['force_download'],
	'search_enabled' => empty($settings['search_enabled']) ? 'true' : $settings['search_enabled'],
	'anti_leech' => empty($settings['anti_leech']) ? 'false' : $settings['anti_leech'],
	'must_login_to_download' => empty($settings['must_login_to_download']) ? 'false' : $settings['must_login_to_download'],
	'archive' => empty($settings['archive']) ? 'false' : $settings['archive'],
	'days_new' => empty($settings['days_new']) ? '7' : $settings['days_new'],
	'entries_per_page' => empty($settings['entries_per_page']) ? '300' : $settings['entries_per_page'],
	'thumbnail_height' => empty($settings['thumbnail_height']) ? '100' : $settings['thumbnail_height'],
	'bandwidth_limit' => empty($settings['bandwidth_limit']) ? '0' : $settings['bandwidth_limit'],
	'md5_show' => empty($settings['md5_show']) ? '20' : $settings['md5_show'],
	'parse_htaccess' => empty($settings['parse_htaccess']) ? 'true' : $settings['parse_htaccess']
);

//begin display of main configuration page:
$page_header = '<?xml version="1.0" encoding="utf-8"?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd" />
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
			<meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
			<meta http-equiv="Content-Style-Type" content="text/css" />

			<meta name="title"       content="AutoIndex Configuration Generator" />
			<meta name="author"      content="Beit Dina Bible Arheology and Translation Institute @ beitdina.net" />
			<meta name="copyright"   content="default template © Beit Dina 2019 based on subSilver style © 2005 phpBB Group." />
			<meta name="keywords"    content="Beit, Dina, Bible, Arheology" />
			<meta name="description" lang="en" content="Directory Index. This is the description search engines show when listing your site." />
			<meta name="category"    content="general" />
			<meta name="robots"      content="index,follow" />
			<meta name="revisit-after" content="7 days" >
			
			<meta http-equiv="X-UA-Compatible" content="IE=edge" />
			<meta name="viewport" content="width=device-width, initial-scale=1.0" />
			<meta name="apple-mobile-web-app-capable" content="yes" />
			<meta name="apple-mobile-web-app-status-bar-style" content="blue" />
			
			<title>AutoIndex Configuration Generator</title>
			'.$install_header_css.'
			<link rel="stylesheet" href="'.$template.'/'.$template_name.'.css" type="text/css" />
	</head>
	<body>';
	$install_form = '
	<form method="post" action="'. $request->server('PHP_SELF') . '?action=config">
	<h3>
		The <a href="http://autoindex.sourceforge.net/">AutoIndex PHP Script</a> special edition by <a href="http://github.com/BeitDina/AutoIndex">Beit Dina Institute</a>
		<br />Configuration
	</h3>
	<p>
		The default options are currently selected, so just press the configure button at the bottom to use them.
	</p>
	<hr />
	<p />
	<p>Return to the <a href="'. $CONFIG_PATH .'">Main Index</a>.</p>';
	$install_form .= '
		<table class="table1" width="650" cellpadding="8">
		<tr>
			<td>
			Base Directory: <input type="text" name="base_dir" value="'; if ($settings['base_dir'] != 'false') { $install_form .= $settings['base_dir']; } $install_form .= '" />';
		
	$install_form .= '<p class="small">This is the folder that will be the root of the directory listing.
				<br />This will be the starting point for the script. Nothing above this directory can be viewed, but its subfolders can.
				<br />Make sure to use a path relative to this index.php file if you can.
			</p>
			</td>
		</tr>
		</table>
	<p />';
	$install_form .= '<table class="table2" width="650" cellpadding="8">
		<tr>
			<td>Assets Path: <input type="text" name="assets_path" value="'; if ($settings['assets_path'] != 'false') { $install_form .= $settings['assets_path']; } $install_form .= '" />';
	$install_form .= '		<p class="small">
				This is the path to the assets files (the path web browsers will access them from).
				<br />The included assets are <em>cookie consent</em>, <em>ion icons</em>, <em>font awesome</em>, and <em>jquery</em>.
			</p>
			</td>
		</tr>
	</table>
	<p />';
	$install_form .= '<table width="650" cellpadding="8">
		<tr>
		<td>
		Icon Path: <input type="text" name="icon_path" value="'; if ($settings['icon_path'] != 'false') { $install_form .= $settings['icon_path']; } $install_form .= '" />';
	$install_form .= '
		<p class="small">
			This is the path to the icon image files (the path web browsers will access them from).
			<br />The included icon sets are <em>apache</em>, <em>kde</em>, <em>osx</em>, and <em>winxp</em>.
			<br />You can leave it blank to not show icons.
		</p>
		</td>
		</tr>
	</table>
	<p />';
	$install_form .= '<table width="650" cellpadding="8">
		<tr>
			<td>
			Flag Path: <input type="text" name="flag_path" value="'; if ($settings['flag_path'] != 'false') { $install_form .= $settings['flag_path']; } $install_form .= '" />';
	$install_form .= '
			<p class="small">
				This is the path to the flag image files (the path web browsers will access them from).
				<br />The included icon sets are <em>country</em>, <em>language</em>.
				<br />You can leave it blank to not show icons.
			</p>
			</td>
		</tr>
	</table>
	<p />';
	$install_form .= '<table width="650" cellpadding="8">
		<tr>
			<td>
			<input type="checkbox" name="show_dir_size" value="true"'; if ($settings['show_dir_size'] != 'false') { $install_form .= ' checked="checked"'; } $install_form .= ' /> Show Directory Size';
	$install_form .= '		
			<p class="small">
				If this box is checked, the total size of directories will be shown under size (all the folder\'s contents will be added up).
				<br />Otherwise, it will display "[dir]" under size.
				<br />NOTE: If you are trying to index many files (meaning a few thousand), you will notice a speed improvement with this turned off.
			</p>
			</td>
		</tr>
	</table>
	<p />';
	$install_form .= '
	<table width="650" cellpadding="8">
	<tr>
	<td>
	<input type="checkbox" name="search_enabled" value="true"'; if ($settings['search_enabled'] != 'false') { $install_form .= ' checked="checked"'; } $install_form .= ' /> Enable Searching';
	$install_form .= '
	<p class="small">
		If this box is checked, people will be able to search for a file or folder by its filename.
		<br />It will search the folder you are currently in, and all subfolders.
		<br />Searching is not case sensitive.
	</p>
	</td>
	</tr>
	</table>
	<p />';
	$install_form .= '
	<table width="650" cellpadding="8">
		<tr>
			<td>
			'.$style_select.'
			<p class="small">
				This is the path where the *.tpl template files are located (relative to this index.php file).
			</p>
			</td>
		</tr>
	</table>
	<p />';
	$install_form .= '<table width="650" cellpadding="8">
		<tr>
			<td>
			<input type="checkbox" name="use_login_system" value="true"'; if ($settings['use_login_system'] != 'false') { $install_form .= ' checked="checked"'; } $install_form .= ' /> Enable Login System
			<br /><input type="checkbox" name="must_login_to_download" value="true"'; if ($settings['must_login_to_download'] != 'false') { $install_form .= ' checked="checked"'; } $install_form .= ' /> Users must login to view/download
			<br />User List: <input type="text" name="user_list" value="'; if ($settings['user_list'] != 'false') { $install_form .= $settings['user_list']; } $install_form .= '" />
			<p class="small">
				User List contains the path to the text file where the usernames and encrypted passwords are stored.
				<br />Make sure the file is chmod\'ed so PHP can read and write to it.
				<br />(User List is only needed if the login system is enabled.)
				<br />
				<br />The default accounts are:
				<br /><code>username: admin</code>
				<br /><code>password: admin</code>
				<br />
				<br /><code>username: user</code>
				<br /><code>password: user</code>
				<br />
				<br />Be sure to create new accounts, then delete these default ones if you enable the login system!
			</p>
			</td>
		</tr>
	</table>
	<p />';
	$install_form .= '<table width="650" cellpadding="8">
		<tr>
			<td>
			Age for "New" Icon: <input type="text" name="days_new" size="3" value="'; if ($settings['days_new'] != 'false') { $install_form .= $settings['days_new']; } $install_form .= '" /> days
			<p class="small">
				This contains the number of days old a file can be and still have [New] written next to it.
				<br />If it is set to 0, this feature will be disabled.
			</p>
			</td>
		</tr>
	</table>
	<p />';
	$install_form .= '<table width="650" cellpadding="8">
		<tr>
			<td>
			Number of file entires per page: <input type="text" name="entries_per_page" size="3" value="'; if ($settings['entries_per_page'] != 'false') { $install_form .= $settings['entries_per_page']; } $install_form .= '" />
			<p class="small">
				This contains the number of files or folders to display on a single page.
				If there are more files or folders, the display will be separated into different
				pages with <code>Previous</code> and <code>Next</code> buttons.
				<br />If it is set to 0, everything will be displayed on one page.
			</p>
			</td>
		</tr>
	</table>
	<p />';
	$install_form .= '<table width="650" cellpadding="8">
		<tr>
			<td>
			Image Thumbnail Height: <input type="text" name="thumbnail_height" size="3" value="'; if ($settings['thumbnail_height'] != 'false') { $install_form .= $settings['thumbnail_height']; } $install_form .= '" /> pixels
			<p class="small">
				This is a feature that will show thumbnails next to images. (NOTE: GDlib 2.0.1 or higher is required)
				<br />Setting it to 0 will disable this feature, and setting it to any other number will set the size of the thumbnail.
				<br />(100 is a good setting to start with.)
			</p>
			</td>
		</tr>
	</table>
	<p />';
	$install_form .= '<table width="650" cellpadding="8">
		<tr>
		<td>
		<input type="checkbox" name="force_download" value="true"'; if ($settings['force_download'] != 'false') { $install_form .= ' checked="checked"'; } $install_form .= '/> Pipe downloaded files though the PHP script
		<p>Bandwidth Limit: <input type="text" name="bandwidth_limit" size="3" value="'; if ($settings['bandwidth_limit'] != 'false') {  $install_form .= $settings['bandwidth_limit']; } $install_form .= '" /> KB/s</p>
		<p class="small">
			This contains the max download speed for files. The above checkbox needs to be checked for this to work.
			<br />If it is set to 0, the script will not limit download speed.
		</p>
		</td>
		</tr>
	</table>
	<p />';
	$install_form .= '<table width="650" cellpadding="8">
		<tr>
			<td>
			<p><input type="checkbox" name="anti_leech" value="true"'; if ($settings['anti_leech'] != 'false') { $install_form .= ' checked="checked"'; } $install_form .= '/> Anti-Leech</p>
			<p class="small">
				When downloading a file, this will check to make sure the referrer the browser sends matches the website\'s URL.
				<br />Since some people turn off referrer sending in their browser, this option is not recommended.
			</p>
			</td>
		</tr>
	</table>
	<p />';
	$install_form .= '<table width="650" cellpadding="8">
		<tr>
			<td>
			<p>
				The following items contain the path and filename to the file where the data for that feature will be stored.
				<br />Leave it blank to turn off that feature.
			</p>
			<p>Hidden Files List: <input type="text" name="hidden_files" value="'; if ($settings['hidden_files'] != 'false') { $install_form .= $settings['hidden_files']; } $install_form .= '" />
			<br /><span class="small">
				Any file or folder matched to an item in this list will be kept hidden.
				<br />The contents of the list are editable when you login as an admin.
			</span>
			</p>

			<p>Access Log File: <input type="text" name="log_file" value="'; if ($settings['log_file'] != 'false') { $install_form .= $settings['log_file']; } $install_form .= '" />
			<br /><span class="small">
				The file to write the access log.
				<br />If this is enabled, you will be able to view the contents of the logfile
				<br />and generate statistics when you login as an admin.
			</span></p>

			<p>File/Folder Description File: <input type="text" name="description_file" value="'; if ($settings['description_file'] != 'false') { $install_form .= $settings['description_file']; } $install_form .= '" />
			<br /><span class="small">
				The file to write the file descriptions to.
				<br />File/Folder descriptions are editable when you login as an admin.
			</span></p>

			<p>Download Count File: <input type="text" name="download_count" value="'; if (!empty($settings['download_count']) && ($settings['download_count'] != 'false')) { $install_form .= $settings['download_count']; } else { $install_form .= $download_count_file; } $install_form .= '" />
			<br /><span class="small">
				The file to write the file download counts to.
				<br />The count is automatically increased when a file is downloaded.
			</span></p>

			<p>Banned User List: <input type="text" name="banned_list" value="'; if (!empty($settings['banned_list']) && ($settings['banned_list'] != 'false')) { $install_form .= $settings['banned_list']; } else { $install_form .= $banned_list_file; } $install_form .= '" />
			<br /><span class="small">
				The file to write IP addresses and hostnames that are blocked from accessing this script.
				<br />The contents of the list are editable when you login as an admin.
			</span></p>
			</td>
		</tr>
	</table>
	<p />';
	$install_form .= '<table width="650" cellpadding="8">
		<tr>
			<td>
			<input type="checkbox" name="archive" value="true"'; if ($settings['archive'] != 'false') { $install_form .= ' checked="checked"'; } $install_form .= ' /> Allow folder archive downloading
			<p class="small">
				If this box is checked, users will be able to download the folder\'s contents as a tar archive file.
			</p>
			</td>
		</tr>
	</table>
	<p />';
	$install_form .= '<table width="650" cellpadding="8">
		<tr>
			<td>
			<input type="checkbox" name="parse_htaccess" value="true"'; if ($settings['parse_htaccess'] != 'false') { $install_form .= ' checked="checked"'; } $install_form .= ' /> Parse .htaccess files
			<p class="small">
				If this box is checked, .htaccess files will be parsed and used by AutoIndex.
			</p>
			</td>
		</tr>
	</table>
	<p />';
	$install_form .= '<table width="650" cellpadding="8">
		<tr>
			<td>
			<p>MD5 calculation max size: <input type="text" name="md5_show" size="3" value="'; if ($settings['md5_show'] != 'false') { $install_form .= $settings['md5_show']; } $install_form .= '" /> MB</p>
			<p class="small">
				Setting this to 0 will disable this feature, and setting it to any other number will set the maximum size of a file to allow users to find the md5sum of (in megabytes).
				<br />(10 is a good setting to start with.)
			</p>
			</td>
		</tr>
	</table>
	<p />';
	$install_form .= '
	<table width="650" cellpadding="8">
		<tr>
			<td>' . $lang_select . '		
			<p class="small">
				The user\'s browser\'s default language is used, unless that language is
				not available in AutoIndex. In that case, the language selected here is
				used.
			</p>
			</td>
		</tr>
	</table>
	<p />
	<hr />
	<p />';
	$install_form .= '<p>
		<input type="submit" value="Configure" />
	</p>
	<p>
		When you press <em>Configure</em>, the script will attempt to write the config data to the file.
		<br />If it cannot (for example if it does not have write permission in the directory) the config file will be downloaded, and you will have to upload it to your server.
		<br />(It should be named <em>'. CONFIG_STORED . '</em> and put in the same folder as <em>index.php</em>)
	</p>
	</form>
	<!--
	Powered by AutoIndex PHP Script (version ' . VERSION . ')
	Copyright (C) 2002-2007 Justin Hagstrom
	http://autoindex.sourceforge.net
	Page generated in ' . round((microtime(true) - START_TIME) * 1000, 1) . ' milliseconds.
	-->
	</body>
</html>';
print ($page_header . $install_form);
?>
