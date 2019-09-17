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
 * Creates and displays detailed statistics from the log file.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.1 (July 12, 2004)
 * @package AutoIndex
 */
class Stats
{
	/**
	 * @var array Stores number of downloads per file extension
	 */
	private $extensions;
	
	/**
	 * @var array Hits per day
	 */
	private $dates;
	
	/**
	 * @var array Unique hits per day
	 */
	private $unique_hits;
	
	/**
	 * @var array Keys are the country codes and values are the number of visits
	 */
	private $countries;
	
	/**
	 * @var int Total views of the base_dir
	 */
	private $total_hits;
	
	/**
	 * @var int The number of days that there is a log entry for
	 */
	private $num_days;
	
	/**
	 * @var int Average hits per day ($total_hits / $num_days)
	 */
	private $avg;
	
	/**
	 * Returns $num formatted with a color (green for positive numbers, red
	 * for negative numbers, and black for 0).
	 *
	 * @param int $num
	 * @return string
	 */
	private static function get_change_color($num)
	{
		if ($num > 0)
		{
			return '<span style="color: #00FF00;">+';
		}
		if ($num < 0)
		{
			return '<span style="color: #FF0000;">';
		}
		return '<span style="color: #000000;">';
	}
	
	/**
	 * If $array[$num] is set, it will be incremented by 1, otherwise it will
	 * be set to 1.
	 *
	 * @param int $num
	 * @param array $array
	 */
	private static function add_num_to_array($num, &$array)
	{
		isset($array[$num]) ? $array[$num]++ : $array[$num] = 1;
	}
	
	/**
	 * Reads the log file, and sets the member variables after doing
	 * calculations.
	 */
	public function __construct()
	{
		$extensions = $dates = $unique_hits = $countries = array();
		$total_hits = 0;
		global $config;
		$log_file = $config -> __get('log_file');
		$base_dir = $config -> __get('base_dir');
		$h = @fopen($log_file, 'rb');
		if ($h === false)
		{
			throw new ExceptionDisplay("Cannot open log file: <em>$log_file</em>");
		}
		while (!feof($h))
		{
			$entries = explode("\t", rtrim(fgets($h, 1024), "\r\n"));
			if (count($entries) === 7)
			{
				//find the number of unique visits
				if ($entries[5] == $base_dir)
				{
					$total_hits++;
					if (!in_array($entries[3], $unique_hits))
					{
						$unique_hits[] = Url::html_output($entries[3]);
					}
	
					//find country codes by hostnames
					$cc = FileItem::ext($entries[3]);
					if (preg_match('/^[a-z]+$/i', $cc))
					{
						self::add_num_to_array($cc, $countries);
					}
	
					//find the dates of the visits
					self::add_num_to_array($entries[0], $dates);
				}
	
				//find file extensions
				$ext = FileItem::ext($entries[6]);
				if (preg_match('/^[\w-]+$/', $ext))
				{
					self::add_num_to_array($ext, $extensions);
				}
			}
		}
		fclose($h);
		$this -> num_days = count($dates);
		$this -> avg = round($total_hits / $this -> num_days);
		$this -> extensions = $extensions;
		$this -> dates = $dates;
		$this -> unique_hits = $unique_hits;
		$this -> countries = $countries;
		$this -> total_hits = $total_hits;
	}
	
	/**
	 * Uses the display class to output results.
	 */
	public function display()
	{
		static $country_codes = array(
			'af' => 'Afghanistan',
			'al' => 'Albania',
			'dz' => 'Algeria',
			'as' => 'American Samoa',
			'ad' => 'Andorra',
			'ao' => 'Angola',
			'ai' => 'Anguilla',
			'aq' => 'Antarctica',
			'ag' => 'Antigua and Barbuda',
			'ar' => 'Argentina',
			'am' => 'Armenia',
			'aw' => 'Aruba',
			'au' => 'Australia',
			'at' => 'Austria',
			'ax' => '&Aring;lang Islands',
			'az' => 'Azerbaidjan',
			'bs' => 'Bahamas',
			'bh' => 'Bahrain',
			'bd' => 'Banglades',
			'bb' => 'Barbados',
			'by' => 'Belarus',
			'be' => 'Belgium',
			'bz' => 'Belize',
			'bj' => 'Benin',
			'bm' => 'Bermuda',
			'bo' => 'Bolivia',
			'ba' => 'Bosnia-Herzegovina',
			'bw' => 'Botswana',
			'bv' => 'Bouvet Island',
			'br' => 'Brazil',
			'io' => 'British Indian O. Terr.',
			'bn' => 'Brunei Darussalam',
			'bg' => 'Bulgaria',
			'bf' => 'Burkina Faso',
			'bi' => 'Burundi',
			'bt' => 'Buthan',
			'kh' => 'Cambodia',
			'cm' => 'Cameroon',
			'ca' => 'Canada',
			'cv' => 'Cape Verde',
			'ky' => 'Cayman Islands',
			'cf' => 'Central African Rep.',
			'td' => 'Chad',
			'cl' => 'Chile',
			'cn' => 'China',
			'cx' => 'Christmas Island',
			'cc' => 'Cocos (Keeling) Isl.',
			'co' => 'Colombia',
			'km' => 'Comoros',
			'cg' => 'Congo',
			'ck' => 'Cook Islands',
			'cr' => 'Costa Rica',
			'hr' => 'Croatia',
			'cu' => 'Cuba',
			'cy' => 'Cyprus',
			'cz' => 'Czech Republic',
			'cs' => 'Czechoslovakia',
			'dk' => 'Denmark',
			'dj' => 'Djibouti',
			'dm' => 'Dominica',
			'do' => 'Dominican Republic',
			'tp' => 'East Timor',
			'ec' => 'Ecuador',
			'eg' => 'Egypt',
			'sv' => 'El Salvador',
			'gq' => 'Equatorial Guinea',
			'ee' => 'Estonia',
			'et' => 'Ethiopia',
			'fk' => 'Falkland Isl. (UK)',
			'fo' => 'Faroe Islands',
			'fj' => 'Fiji',
			'fi' => 'Finland',
			'fr' => 'France',
			'fx' => 'France (European Terr.)',
			'tf' => 'French Southern Terr.',
			'ga' => 'Gabon',
			'gm' => 'Gambia',
			'ge' => 'Georgia',
			'de' => 'Germany',
			'gh' => 'Ghana',
			'gi' => 'Gibraltar',
			'gb' => 'Great Britain (UK)',
			'gr' => 'Greece',
			'gl' => 'Greenland',
			'gd' => 'Grenada',
			'gp' => 'Guadeloupe (Fr)',
			'gu' => 'Guam (US)',
			'gt' => 'Guatemala',
			'gn' => 'Guinea',
			'gw' => 'Guinea Bissau',
			'gy' => 'Guyana',
			'gf' => 'Guyana (Fr)',
			'ht' => 'Haiti',
			'hm' => 'Heard &amp; McDonald Isl.',
			'hn' => 'Honduras',
			'hk' => 'Hong Kong',
			'hu' => 'Hungary',
			'is' => 'Iceland',
			'in' => 'India',
			'id' => 'Indonesia',
			'ir' => 'Iran',
			'iq' => 'Iraq',
			'ie' => 'Ireland',
			'il' => 'Israel',
			'it' => 'Italy',
			'ci' => 'Ivory Coast',
			'jm' => 'Jamaica',
			'jp' => 'Japan',
			'jo' => 'Jordan',
			'kz' => 'Kazachstan',
			'ke' => 'Kenya',
			'kg' => 'Kirgistan',
			'ki' => 'Kiribati',
			'kp' => 'North Korea',
			'kr' => 'South Korea',
			'kw' => 'Kuwait',
			'la' => 'Laos',
			'lv' => 'Latvia',
			'lb' => 'Lebanon',
			'ls' => 'Lesotho',
			'lr' => 'Liberia',
			'ly' => 'Libya',
			'li' => 'Liechtenstein',
			'lt' => 'Lithuania',
			'lu' => 'Luxembourg',
			'mo' => 'Macau',
			'mg' => 'Madagascar',
			'mw' => 'Malawi',
			'my' => 'Malaysia',
			'mv' => 'Maldives',
			'ml' => 'Mali',
			'mt' => 'Malta',
			'mh' => 'Marshall Islands',
			'mk' => 'Macedonia',
			'mq' => 'Martinique (Fr.)',
			'mr' => 'Mauritania',
			'mu' => 'Mauritius',
			'mx' => 'Mexico',
			'fm' => 'Micronesia',
			'md' => 'Moldavia',
			'mc' => 'Monaco',
			'mn' => 'Mongolia',
			'ms' => 'Montserrat',
			'ma' => 'Morocco',
			'mz' => 'Mozambique',
			'mm' => 'Myanmar',
			'na' => 'Namibia',
			'nr' => 'Nauru',
			'np' => 'Nepal',
			'an' => 'Netherland Antilles',
			'nl' => 'Netherlands',
			'nt' => 'Neutral Zone',
			'nc' => 'New Caledonia (Fr.)',
			'nz' => 'New Zealand',
			'ni' => 'Nicaragua',
			'ne' => 'Niger',
			'ng' => 'Nigeria',
			'nu' => 'Niue',
			'nf' => 'Norfolk Island',
			'mp' => 'Northern Mariana Isl.',
			'no' => 'Norway',
			'om' => 'Oman',
			'pk' => 'Pakistan',
			'pw' => 'Palau',
			'pa' => 'Panama',
			'pg' => 'Papua New Guinea',
			'py' => 'Paraguay',
			'pe' => 'Peru',
			'ph' => 'Philippines',
			'pn' => 'Pitcairn',
			'pl' => 'Poland',
			'pf' => 'Polynesia (Fr.)',
			'pt' => 'Portugal',
			'pr' => 'Puerto Rico (US)',
			'qa' => 'Qatar',
			're' => 'R&eacute;union (Fr.)',
			'ro' => 'Romania',
			'ru' => 'Russian Federation',
			'rw' => 'Rwanda',
			'lc' => 'Saint Lucia',
			'ws' => 'Samoa',
			'sm' => 'San Marino',
			'sa' => 'Saudi Arabia',
			'sn' => 'Senegal',
			'sc' => 'Seychelles',
			'sl' => 'Sierra Leone',
			'sg' => 'Singapore',
			'sk' => 'Slovak Republic',
			'si' => 'Slovenia',
			'sb' => 'Solomon Islands',
			'so' => 'Somalia',
			'za' => 'South Africa',
			'su' => 'Soviet Union',
			'es' => 'Spain',
			'lk' => 'Sri Lanka',
			'sh' => 'St. Helena',
			'pm' => 'St. Pierre &amp; Miquelon',
			'st' => 'St. Tome and Principe',
			'kn' => 'St. Kitts Nevis Anguilla',
			'vc' => 'St. Vincent &amp; Grenadines',
			'sd' => 'Sudan',
			'sr' => 'Suriname',
			'sj' => 'Svalbard &amp; Jan Mayen Isl.',
			'sz' => 'Swaziland',
			'se' => 'Sweden',
			'ch' => 'Switzerland',
			'sy' => 'Syria',
			'tj' => 'Tadjikistan',
			'tw' => 'Taiwan',
			'tz' => 'Tanzania',
			'th' => 'Thailand',
			'tg' => 'Togo',
			'tk' => 'Tokelau',
			'to' => 'Tonga',
			'tt' => 'Trinidad &amp; Tobago',
			'tn' => 'Tunisia',
			'tr' => 'Turkey',
			'tm' => 'Turkmenistan',
			'tc' => 'Turks &amp; Caicos Islands',
			'tv' => 'Tuvalu',
			'ug' => 'Uganda',
			'ua' => 'Ukraine',
			'ae' => 'United Arab Emirates',
			'uk' => 'United Kingdom',
			'us' => 'United States',
			'uy' => 'Uruguay',
			'um' => 'US Minor outlying Isl.',
			'uz' => 'Uzbekistan',
			'vu' => 'Vanuatu',
			'va' => 'Vatican City State',
			've' => 'Venezuela',
			'vn' => 'Vietnam',
			'vg' => 'Virgin Islands (British)',
			'vi' => 'Virgin Islands (US)',
			'wf' => 'Wallis &amp; Futuna Islands',
			'wlk' => 'Wales',
			'eh' => 'Western Sahara',
			'ye' => 'Yemen',
			'yu' => 'Yugoslavia',
			'zr' => 'Zaire',
			'zm' => 'Zambia',
			'zw' => 'Zimbabwe',
			'mil' => 'United States Military',
			'gov' => 'United States Government',
			'com' => 'Commercial',
			'net' => 'Network',
			'org' => 'Non-Profit Organization',
			'edu' => 'Educational',
			'int' => 'International',
			'aero' => 'Air Transport Industry',
			'biz' => 'Businesses',
			'coop' => 'Non-profit cooperatives',
			'arpa' => 'Arpanet',
			'info' => 'Info',
			'name' => 'Name',
			'nato' => 'Nato',
			'museum' => 'Museum',
			'pro' => 'Pro'
		);
		
		$str = '<table width="40%"><tr><th class="autoindex_th">&nbsp;</th>
		<th class="autoindex_th">Total</th><th class="autoindex_th">Daily</th></tr>'
		. "<tr class='light_row'><td class='autoindex_td'>Hits</td>
		<td class='autoindex_td'>{$this -> total_hits}</td><td class='autoindex_td'>{$this -> avg}"
		. '</td></tr><tr class="light_row"><td class="autoindex_td">Unique Hits</td>
		<td class="autoindex_td">' . count($this -> unique_hits)
		. '</td><td class="autoindex_td">'
		. round(count($this -> unique_hits) / $this -> num_days)
		. '</td></tr></table><p>Percent Unique: '
		. number_format(count($this -> unique_hits) / $this -> total_hits * 100, 1) . '</p>';

		arsort($this -> extensions);
		arsort($this -> countries);

		$date_nums = array_values($this -> dates);
		$str .= '<table width="75%" border="0"><tr><th class="autoindex_th">Date</th>
		<th class="autoindex_th">Hits That Day</th><th class="autoindex_th">Change From Previous Day</th>
		<th class="autoindex_th">Difference From Average (' . $this -> avg
		. ')</th></tr>';
		$i = 0;
		foreach ($this -> dates as $day => $num)
		{
			$diff = $num - $this -> avg;
			$change = (($i > 0) ? ($num - $date_nums[$i-1]) : 0);
			$change_color = self::get_change_color($change);
			$diff_color = self::get_change_color($diff);
			$class = (($i++ % 2) ? 'dark_row' : 'light_row');
			$str .= "<tr class='$class'><td class='autoindex_td'>$day</td>
			<td class='autoindex_td'>$num</td>
			<td class='autoindex_td'>$change_color$change</span></td>
			<td class='autoindex_td'>$diff_color$diff</span></td></tr>";
		}
		
		$str .= '</table><p /><table width="75%" border="0">
		<tr><th class="autoindex_th">Downloads based on file extensions</th>
		<th class="autoindex_th">Total</th><th class="autoindex_th">Daily</th></tr>';
		$i = 0;
		foreach ($this -> extensions as $ext => $num)
		{
			$class = (($i++ % 2) ? 'dark_row' : 'light_row');
			$str .= "<tr class='$class'><td class='autoindex_td'>$ext</td>
			<td class='autoindex_td'>$num</td><td class='autoindex_td'>"
			. number_format($num / $this -> num_days, 1) . "</td></tr>";
		}
		
		$str .= '</table><p /><table width="75%" border="0"><tr>
		<th class="autoindex_th">Hostname ISP extension</th>
		<th class="autoindex_th">Total</th><th class="autoindex_th">Daily</th></tr>';
		$i = 0;
		foreach ($this -> countries as $c => $num)
		{
			$c_code = (isset($country_codes[strtolower($c)]) ? ' <span class="autoindex_small">('
			. $country_codes[strtolower($c)] . ')</span>' : '');
			$class = (($i++ % 2) ? 'dark_row' : 'light_row');
			$str .= "<tr class='$class'><td class='autoindex_td'>$c{$c_code}</td><td class='autoindex_td'>$num</td><td class='autoindex_td'>"
			. number_format($num / $this -> num_days, 1) . "</td></tr>\n";
		}
		$str .= '</table><p><a class="autoindex_a" href="'
		. Url::html_output($_SERVER['PHP_SELF']) . '">Continue.</a></p>';
		echo new Display($str);
		die();
	}
}

?>