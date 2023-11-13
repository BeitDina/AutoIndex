<?php
/**
 * When a file with the stored config data is not present, this file is
 * automatically included to create a new one.
 *
 * @package AutoIndex
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>, FlorinCB <orynider@users.sourceforge.net>
 * @version 2.2.7 (January 13, 2019 / November 13, 2023)
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

if (!defined('IN_AUTOINDEX') || !IN_AUTOINDEX)
{
	die();
}

$strings = array('base_dir', 'icon_path', 'flag_path', 'language', 'assets_path', 'template', 'log_file',
	'description_file', 'user_list', 'download_count', 'hidden_files',
	'banned_list');
$checkboxes = array('show_dir_size', 'use_login_system', 'force_download',
	'search_enabled', 'anti_leech', 'must_login_to_download', 'archive',
	'parse_htaccess');
$numbers = array('days_new', 'thumbnail_height', 'bandwidth_limit', 'md5_show',
	'entries_per_page');

if (count($_POST) >= count($strings) + count($numbers))
{
	
	$directories = array('base_dir', 'icon_path', 'flag_path', 'assets_path', 'template');
	$output = "<?php\n\n/* AutoIndex PHP Script config file\n\n";
	$request_post_setting = '';
	foreach ($strings as $setting)
	{
		if (!$request->is_post($setting))
		{
			die(simple_display('Required setting <em>' . htmlentities($setting) . '</em> not set.'));
		}
		if ($request->is_post($setting))
		{
			$output .= "$setting\tfalse\n";
			continue;
		}
		$request_post_setting = str_replace('\\', '/', $request->post($setting, TYPE_NO_TAGS)); //make sure there is a slash at the end of directories
		if (in_array($setting, $directories) && !preg_match('#/$#', $request_post_setting))
		{
			$request_post_setting .= '/';
		}
		$output .= "$setting\t{$request_post_setting}\n";
	}
	foreach ($checkboxes as $setting)
	{
		$output .= "$setting\t" . ($request->is_post($setting) ? 'true' : 'false') . "\n";
	}
	foreach ($numbers as $setting)
	{
		if (!$request->is_post($setting))
		{
			die(simple_display('Required setting <em>' . htmlentities($setting) . '</em> not set.'));
		}
		if ($request->is_post($setting))
		{
			$output .= "$setting\t0\n";
			continue;
		}
		$request_post_setting = str_replace('\\', '/', $request->post($setting, TYPE_NO_TAGS)); 
		if ($request_post_setting < 0)
		{
			die(simple_display('The setting <em>' . htmlentities($setting) . '</em> should not be a negitive number.'));
		}
		$request_post_setting = (string)((float)$request_post_setting);
		$output .= "$setting\t{$request_post_setting}\n";
	}
	$output .= "\n*/\n\n?>";
	
	if (!$request->is_post('force_download'))
	{
		if (preg_match('#^(/|[a-z]\:)#i', $request->post('base_dir', TYPE_NO_TAGS)))
		{
			die(simple_display('It seems you are using an absolute path for the Base Directory.' . '<br />This means you must check the "Pipe downloaded files though the PHP script" box.'));
		}
		if ((int)$request->post('bandwidth_limit', TYPE_INT) !== 0)
		{
			die(simple_display('For the Bandwidth Limit feature to work, the "force download" feature needs to be on.'
			. '<br />This means you must check the "Pipe downloaded files though the PHP script" box.'));
		}
	}
	if ($request->is_post('must_login_to_download') && !$request->is_post('use_login_system'))
	{
		die(simple_display('To enable <em>must_login_to_download</em>, the ' . '<em>use_login_system</em> option must also be turned on.'));
	}
	foreach (array('base_dir', 'template') as $valid)
	{
		if (!@is_dir($request->post($valid, TYPE_NO_TAGS)))
		{
			//die(simple_display(htmlentities($valid) . ' setting is not a valid directory.'));
		}
	}
	if (@is_file(CONFIG_STORED)) //if the file already exists, back it up
	{
		$temp_name = CONFIG_STORED . '.bak';
		for ($i = 1; file_exists($temp_name); $i++)
		{
			$temp_name = CONFIG_STORED . '.bak' . (string)$i;
		}
		@copy(CONFIG_STORED, $temp_name);
	}	
	$h = @fopen(CONFIG_STORED, 'wb');
	if ($h === false) //the file could not be written to, so now it must be downloaded through the browser	
	{
		header('Content-Type: text/plain; name="' . CONFIG_STORED . '"');
		header('Content-Disposition: attachment; filename="' . CONFIG_STORED . '"');
		die($output);
	}
	else	//the file was opened successfully, so write to it
	{
		fwrite($h, $output);
		fclose($h);	
		//begin display of "configuration complete" page
		echo '<?xml version="1.0" encoding="iso-8859-2" ?>';
		?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>AutoIndex Configuration: Complete!</title>
	<style type="text/css" title="AutoIndex Default">
		html, body
		{
			background-color: #F0F0F0;
			color: #000020;
			font-family: verdana, lucidia, sans-serif;
			font-size: 15px;
		}
		a:visited, a:active
		{
			color: #00008F;
			text-decoration: none;
		}
		a:link
		{
			color: #0000FF;
			text-decoration: none;
		}
		a:hover
		{
			color: #0000FF;
			text-decoration: overline underline;
		}
		td
		{
			color: #000020;
			font-family: verdana, lucidia, sans-serif;
			font-size: 15px;
			border: 1px solid #7F8FA9;
		}
		tr
		{
			background: #F2F6FC;
		}
	</style>
</head>
<body>
<table border="0" cellpadding="5" cellspacing="0">
<tr><td>
<p>Write successful!<br />AutoIndex configuration is finished.</p>
<p><a href="<?php echo $request->server('PHP_SELF'); ?>">Continue.</a></p>
</td></tr></table>
</body>
</html>
<?php
		die();
	}
}
//list of default settings
$settings = array(
	'base_dir' => './',
	'assets_path' => 'assets/',
	'icon_path' => 'index_icons/winvista/',
	'flag_path' => 'flags/language/',
	'language' => 'en',
	'template' => './templates/default/',
	'log_file' => 'false',
	'description_file' => 'false',
	'user_list' => '.htpasswd.autoindex',
	'download_count' => 'false',
	'hidden_files' => 'hidden_files',
	'banned_list' => 'false',
	'show_dir_size' => 'true',
	'use_login_system' => 'false',
	'force_download' => 'false',
	'search_enabled' => 'true',
	'anti_leech' => 'false',
	'must_login_to_download' => 'false',
	'archive' => 'false',
	'days_new' => '2',
	'entries_per_page' => '300',
	'thumbnail_height' => '100',
	'bandwidth_limit' => '0',
	'md5_show' => '0',
	'parse_htaccess' => 'true'
);
global $config;
if (isset($config)) //if we're reconfiguring the script, use the current settings
{
	foreach ($settings as $key => $data)
	{
		$settings[$key] = $config->__get($key);
	}
}
//begin display of main configuration page:
echo '<?xml version="1.0" encoding="iso-8859-2" ?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>AutoIndex Configuration Generator</title>
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
</style>
</head>
<body>
<form method="post" action="<?php echo $request->server('PHP_SELF'); ?>?action=config">
<h3>
	<a href="http://autoindex.sourceforge.net/">AutoIndex PHP Script</a>
	<br />Configuration
</h3>
<p>
	The default options are currently selected, so just press the configure button at the bottom to use them.
</p>
<hr />
<p />
<table width="650" cellpadding="8"><tr><td>
Base Directory: <input type="text" name="base_dir" value="<?php if ($settings['base_dir'] != 'false') echo $settings['base_dir']; ?>" />
<p class="small">
	This is the folder that will be the root of the directory listing.
	<br />This will be the starting point for the script. Nothing above this directory can be viewed, but its subfolders can.
	<br />Make sure to use a path relative to this index.php file if you can.
</p>
</td></tr>
</table>
<p />
<table width="650" cellpadding="8"><tr><td>
Icon Path: <input type="text" name="icon_path" value="<?php if ($settings['icon_path'] != 'false') echo $settings['icon_path']; ?>" />
<p class="small">
	This is the path to the icon image files (the path web browsers will access them from).
	<br />The included icon sets are <em>apache</em>, <em>kde</em>, <em>osx</em>, and <em>winxp</em>.
	<br />You can leave it blank to not show icons.
</p>
</td></tr>
</table>
<p />
<table width="650" cellpadding="8"><tr><td>
Flag Path: <input type="text" name="flag_path" value="<?php if ($settings['flag_path'] != 'false') echo $settings['flag_path']; ?>" />
<p class="small">
	This is the path to the flag image files (the path web browsers will access them from).
	<br />The included icon sets are <em>country</em>, <em>language</em>.
	<br />You can leave it blank to not show icons.
</p>
</td></tr>
</table>
<p />
<table width="650" cellpadding="8"><tr><td>
<input type="checkbox" name="show_dir_size" value="true"<?php if ($settings['show_dir_size'] != 'false') echo ' checked="checked"'; ?> /> Show Directory Size
<p class="small">
	If this box is checked, the total size of directories will be shown under size (all the folder's contents will be added up).
	<br />Otherwise, it will display "[dir]" under size.
	<br />NOTE: If you are trying to index many files (meaning a few thousand), you will notice a speed improvement with this turned off.
</p>
</td></tr>
</table>
<p />
<table width="650" cellpadding="8"><tr><td>
<input type="checkbox" name="search_enabled" value="true"<?php if ($settings['search_enabled'] != 'false') echo ' checked="checked"'; ?> /> Enable Searching
<p class="small">
	If this box is checked, people will be able to search for a file or folder by its filename.
	<br />It will search the folder you are currently in, and all subfolders.
	<br />Searching is not case sensitive.
</p>
</td></tr>
</table>
<p />
<table width="650" cellpadding="8"><tr><td>
Template Directory: <input type="text" name="template" value="<?php if ($settings['template'] != 'false') echo $settings['template']; ?>" />
<p class="small">
	This is the path where the *.tpl template files are located (relative to this index.php file).
</p>
</td></tr>
</table>
<p />
<table width="650" cellpadding="8"><tr><td>
<input type="checkbox" name="use_login_system" value="true"<?php if ($settings['use_login_system'] != 'true') echo ' checked="checked"'; ?> /> Enable Login System
<br /><input type="checkbox" name="must_login_to_download" value="true"<?php if ($settings['must_login_to_download'] != 'false') echo ' checked="checked"'; ?> /> Users must login to view/download
<br />User List: <input type="text" name="user_list" value="<?php if ($settings['user_list'] != 'true') echo $settings['user_list']; ?>" />
<p class="small">
	User List contains the path to the text file where the usernames and encrypted passwords are stored.
	<br />Make sure the file is chmod'ed so PHP can read and write to it.
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
</td></tr></table>

<p />
<table width="650" cellpadding="8"><tr><td>
Age for "New" Icon: <input type="text" name="days_new" size="3" value="<?php if ($settings['days_new'] != 'false') echo $settings['days_new']; ?>" /> days
<p class="small">
	This contains the number of days old a file can be and still have [New] written next to it.
	<br />If it is set to 0, this feature will be disabled.
</p>
</td></tr></table>

<p />
<table width="650" cellpadding="8"><tr><td>
Number of file entires per page: <input type="text" name="entries_per_page" size="3" value="<?php if ($settings['entries_per_page'] != 'false') echo $settings['entries_per_page']; ?>" />
<p class="small">
	This contains the number of files or folders to display on a single page.
	If there are more files or folders, the display will be separated into different
	pages with <code>Previous</code> and <code>Next</code> buttons.
	<br />If it is set to 0, everything will be displayed on one page.
</p>
</td></tr></table>

<p />
<table width="650" cellpadding="8"><tr><td>
Image Thumbnail Height: <input type="text" name="thumbnail_height" size="3" value="<?php if ($settings['thumbnail_height'] != 'false') echo $settings['thumbnail_height']; ?>" /> pixels
<p class="small">
	This is a feature that will show thumbnails next to images. (NOTE: GDlib 2.0.1 or higher is required)
	<br />Setting it to 0 will disable this feature, and setting it to any other number will set the size of the thumbnail.
	<br />(100 is a good setting to start with.)
</p>
</td></tr></table>

<p />
<table width="650" cellpadding="8"><tr><td>
<input type="checkbox" name="force_download" value="true"<?php if ($settings['force_download'] != 'false') echo ' checked="checked"'; ?> /> Pipe downloaded files though the PHP script
<p>Bandwidth Limit: <input type="text" name="bandwidth_limit" size="3" value="<?php if ($settings['bandwidth_limit'] != 'false') echo $settings['bandwidth_limit']; ?>" /> KB/s</p>
<p class="small">
	This contains the max download speed for files. The above checkbox needs to be checked for this to work.
	<br />If it is set to 0, the script will not limit download speed.
</p>
</td></tr></table>

<p />
<table width="650" cellpadding="8"><tr><td>
<p><input type="checkbox" name="anti_leech" value="true"<?php if ($settings['anti_leech'] != 'false') echo ' checked="checked"'; ?> /> Anti-Leech</p>
<p class="small">
	When downloading a file, this will check to make sure the referrer the browser sends matches the website's URL.
	<br />Since some people turn off referrer sending in their browser, this option is not recommended.
</p>
</td></tr></table>

<p />
<table width="650" cellpadding="8"><tr><td>

<p>
	The following items contain the path and filename to the file where the data for that feature will be stored.
	<br />Leave it blank to turn off that feature.
</p>

<p>Hidden Files List: <input type="text" name="hidden_files" value="<?php if ($settings['hidden_files'] != 'false') echo $settings['hidden_files']; ?>" />
<br /><span class="small">
	Any file or folder matched to an item in this list will be kept hidden.
	<br />The contents of the list are editable when you login as an admin.
</span></p>

<p>Access Log File: <input type="text" name="log_file" value="<?php if ($settings['log_file'] != 'false') echo $settings['log_file']; ?>" />
<br /><span class="small">
	The file to write the access log.
	<br />If this is enabled, you will be able to view the contents of the logfile
	<br />and generate statistics when you login as an admin.
</span></p>

<p>File/Folder Description File: <input type="text" name="description_file" value="<?php if ($settings['description_file'] != 'false') echo $settings['description_file']; ?>" />
<br /><span class="small">
	The file to write the file descriptions to.
	<br />File/Folder descriptions are editable when you login as an admin.
</span></p>

<p>Download Count File: <input type="text" name="download_count" value="<?php if ($settings['download_count'] != 'false') echo $settings['download_count']; ?>" />
<br /><span class="small">
	The file to write the file download counts to.
	<br />The count is automatically increased when a file is downloaded.
</span></p>

<p>Banned User List: <input type="text" name="banned_list" value="<?php if ($settings['banned_list'] != 'false') echo $settings['banned_list']; ?>" />
<br /><span class="small">
	The file to write IP addresses and hostnames that are blocked from accessing this script.
	<br />The contents of the list are editable when you login as an admin.
</span></p>
</td></tr>
</table>
<p />
<table width="650" cellpadding="8"><tr><td>
<input type="checkbox" name="archive" value="true"<?php if ($settings['archive'] != 'false') echo ' checked="checked"'; ?> /> Allow folder archive downloading
<p class="small">
	If this box is checked, users will be able to download the folder's contents as a tar archive file.
</p>
</td></tr>
</table>
<p />
<table width="650" cellpadding="8"><tr><td>
<input type="checkbox" name="parse_htaccess" value="true"<?php if ($settings['parse_htaccess'] != 'false') echo ' checked="checked"'; ?> /> Parse .htaccess files
<p class="small">
	If this box is checked, .htaccess files will be parsed and used by AutoIndex.
</p>
</td></tr>
</table>
<p />
<table width="650" cellpadding="8">
<tr><td>
<p>MD5 calculation max size: <input type="text" name="md5_show" size="3" value="<?php if ($settings['md5_show'] != 'false') echo $settings['md5_show']; ?>" /> MB</p>
<p class="small">
	Setting this to 0 will disable this feature, and setting it to any other number will set the maximum size of a file to allow users to find the md5sum of (in megabytes).
	<br />(10 is a good setting to start with.)
</p>
</td></tr>
</table>
<p />
<table width="650" cellpadding="8"><tr><td>
Default Language: <select name="language">
<?php
	$l = Language::get_all_langs(PATH_TO_LANGUAGES);
	if ($l === false)
	{
		$l = array('en');
	}
	sort($l);
	foreach ($l as $lang)
	{
		$sel = (($lang == $settings['language']) ? ' selected="selected"' : '');
		echo "\t\t<option$sel>$lang</option>\n";
	}
?>
</select>
<p class="small">
	The user's browser's default language is used, unless that language is
	not available in AutoIndex. In that case, the language selected here is
	used.
</p>
</td></tr>
</table>
<p /><hr />
<p />
<p>
	<input type="submit" value="Configure" />
</p>
<p>
	When you press <em>Configure</em>, the script will attempt to write the config data to the file.
	<br />If it cannot (for example if it does not have write permission in the directory) the config file will be downloaded, and you will have to upload it to your server.
	<br />(It should be named <em><?php echo CONFIG_STORED; ?></em> and put in the same folder as <em>index.php</em>)
</p>
</form>
<!--
Powered by AutoIndex PHP Script (version <?php echo VERSION; ?>)
Copyright (C) 2002-2008 Justin Hagstrom
http://autoindex.sourceforge.net
Page generated in <?php echo round((microtime(true) - START_TIME) * 1000, 1); ?> milliseconds.
-->
</body>
</html>
