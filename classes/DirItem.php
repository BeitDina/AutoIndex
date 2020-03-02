<?php
/**
 * @package AutoIndex
 *
 * @copyright Copyright (C) 2002-2004 Justin Hagstrom
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 *
 * @link http://autoindex.sourceforge.net
 */

 /**
 * Modifications:
 *		26.11.2018 - ported for indexing flags in ../images/flags/language/ subfolder - by OryNider
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
 * Subclass of item that specifically represents a directory.
 *
 * @author Justin Hagstrom <JustinHagstrom@yahoo.com>
 * @version 1.0.1 (June 30, 2004)
 * @package AutoIndex
 */
class DirItem extends Item
{
	/**
	 * @var DirectoryList The list of this directory's contents
	 */
	private $temp_list;
	
	/**
	 * @var string
	 */
	protected $description;
	
	/**
	 * @return string Always returns 'dir', since this is a directory, not a file
	 */
	public function file_ext()
	{
		return 'dir';
	}
	
	/**
	 * @return int The total size in bytes of the folder (recursive)
	 */
	private function dir_size()
	{
		if (!isset($this -> temp_list))
		{
			$this -> temp_list = new DirectoryList($this -> parent_dir . $this -> filename);
		}
		return $this -> temp_list -> size_recursive();
	}
	
	/**
	 * @return int The total number of files in the folder (recursive)
	 */
	public function num_subfiles()
	{
		if (!isset($this -> temp_list))
		{
			$this -> temp_list = new DirectoryList($this -> parent_dir . $this -> filename);
		}
		return $this -> temp_list -> num_files();
	}
	
	/**
	 * @param string $path
	 * @return string The parent directory of $path
	 */
	public static function get_parent_dir($path)
	{
		$path = str_replace('\\', '/', $path);
		while (preg_match('#/$#', $path))
		//remove all slashes from the end
		{
			$path = substr($path, 0, -1);
		}
		$pos = strrpos($path, '/');
		if ($pos === false)
		{
			return '';
		}
		$path = substr($path, 0, $pos + 1);
		return (($path === false) ? '' : $path);
	}
	
	/**
	 * @param string $parent_dir
	 * @param string $filename
	 */
	public function __construct($parent_dir, $filename)
	{
		$filename = self::make_sure_slash($filename);
		parent::__construct($parent_dir, $filename);
		global $config, $subdir;
		$this -> downloads = '&nbsp;';
		if ($filename == '../')
		//link to parent directory
		{
			if ($subdir != '')
			{
				global $words;
				$this -> is_parent_dir = true;
				$this -> filename = $words -> __get('parent directory');
				$this -> icon = (ICON_PATH ? $config -> __get('icon_path') . 'back.png' : '');
				$this -> size = new Size(true);
				$this -> link = Url::html_output($_SERVER['PHP_SELF']) . '?dir=' . Url::translate_uri(self::get_parent_dir($subdir));
				$this -> parent_dir = $this -> new_icon = '';
				$this -> a_time = $this -> m_time = false;
			}
			else
			{
				$this -> is_parent_dir = $this -> filename = false;
			}
		}
		else
		{
			//regular folder
			$file = $this -> parent_dir . $filename;
			
			if (!@is_dir($file))
			{
				throw new ExceptionDisplay('Directory <em>'	. Url::html_output($this -> parent_dir . $filename) . '</em> does not exist.');
			}
			
			$this -> filename = $filename = substr($filename, 0, -1); 
			$mb_strlen = mb_strlen($filename);
			$this -> icon = $config -> __get('icon_path') . 'dir.png';
			
			if (($mb_strlen > 1) && ($mb_strlen < 6)) 
			{
				$decoded_lang_name = self::decode_country_name($filename, 'language');
				if (!empty($decoded_lang_name))
				{
					$this -> icon = FLAG_PATH ? $config -> __get('flag_path') . $filename . '.png' : $config -> __get('icon_path') . $filename . '.png';
				}
				
			}
			
			if (($mb_strlen > 1) && ($mb_strlen < 25)) 
			{
				$decoded_lang_name = self::decode_country_name($filename, 'language');
			
				$file_name = substr($filename, 0, strrpos($filename, '.'));
				
				global $descriptions, $words;
				
				if ($words -> is_set($file_name))
				{
					$description = ($words -> is_set($file_name) ? $words -> __get($file_name) : $file_name);
				}
				elseif (!empty($decoded_lang_name))
				{
					$description = ($words -> is_set($decoded_lang_name) ? $words -> __get($decoded_lang_name) : $decoded_lang_name);
				}
				else
				{
					$description = ($words -> is_set($file_name) ? $words -> __get($file_name) : $file_name);
				}
				
				$this -> description = ($words -> is_set($description) ? $words -> __get($description) : $description);
			}
			
			$this -> link = Url::html_output($_SERVER['PHP_SELF']) . '?dir=' . Url::translate_uri(substr($this -> parent_dir, strlen($config -> __get('base_dir'))) . $filename);
		
		}
	}

	/**
	 * function decode_lang from mx_traslator phpBB3 Extension
	 *
	 * $mx_user_lang = decode_country_name($lang['USER_LANG'], 'country');
	 *
	 * @param unknown_type $file_dir
	 * @param unknown_type $lang_country = 'country' or 'language'
	 * @param array $langs_countries
	 * @return unknown
	 */
	private function decode_country_name($file_dir, $lang_country = 'country', $langs_countries = false)
	{
		/* known languages */
		switch ($file_dir)
		{
				case 'aa':
					$lang_name = 'AFAR';
					$country_name = 'AFAR'; //Ethiopia
				break;
				
				case 'aae':
					$lang_name = 'AFRICAN-AMERICAN_ENGLISH';
					$country_name = 'UNITED_STATES'; 
				break;

				case 'ab':
					$lang_name = 'ABKHAZIAN';
					$country_name = 'ABKHAZIA';
				break;

				case 'ad':
					$lang_name = 'ANGOLA';
					$country_name = 'ANGOLA';
				break;

				case 'ae':
					$lang_name = 'AVESTAN';
					$country_name = 'UNITED_ARAB_EMIRATES'; //Persia
				break;

				case 'af':
					$country_name = 'AFGHANISTAN'; // langs: pashto and dari
					$lang_name = 'AFRIKAANS'; // speakers: 6,855,082 - 13,4%
				break;
				
				
				case 'ag':
					$lang_name = 'ENGLISH-CREOLE';
					$country_name = 'ANTIGUA_&AMP;_BARBUDA';
				break;
				
				case 'ai':
					$lang_name = 'Anguilla';
					$country_name = 'ANGUILLA';
				break;
				
				case 'aj':
					$lang_name = 'AROMANIAN';
					$country_name = 'Aromaya';
				break;
				
				case 'ak':
					$lang_name = 'AKAN';
					$country_name = '';
				break;

				case 'al':
					$lang_name = 'ALBANIAN';
					$country_name = 'ALBANIA';
				break;


				case 'am':
					$lang_name = 'AMHARIC';
					//$lang_name = 'armenian';
					$country_name = 'ARMENIA';
				break;

				case 'an':
					$lang_name = 'ARAGONESE'; //
					//$country_name = 'Andorra';
					$country_name = 'NETHERLAND_ANTILLES';
				break;
				
				case 'ao':
					$lang_name = 'ANGOLIAN';
					$country_name = 'ANGOLA';
				break;
				
				case 'ap':
					$lang_name = 'ANGIKA';
					$country_name = 'ANGA'; //India
				break;

				case 'ar':
					$lang_name = 'ARABIC';
					$country_name = 'ARGENTINA';
				break;
				
				case 'arq':
					$lang_name = 'ALGERIAN_ARABIC'; //known as Darja or Dziria in Algeria
					$country_name = 'ALGERIA';
				break;
				
				case 'arc':
					$country_name = 'ASHURIA';
					$lang_name = 'ARAMEIC';
				break;
				
				case 'ary':
					$lang_name = 'MOROCCAN_ARABIC'; //known as Moroccan Arabic or Moroccan Darija or Algerian Saharan Arabic
					$country_name = 'MOROCCO';
				break;
				
				//jrb – Judeo-Arabic
				//yhd – Judeo-Iraqi Arabic
				//aju – Judeo-Moroccan Arabic
				//yud – Judeo-Tripolitanian Arabic
				//ajt – Judeo-Tunisian Arabic
				//jye – Judeo-Yemeni Arabic	
				case 'jrb':
					$lang_name = 'JUDEO-ARABIC';
					$country_name = 'JUDEA';
				break;
				
				case 'kab':
					$lang_name = 'KABYLE'; //known as Kabyle (Tamazight)
					$country_name = 'ALGERIA';
				break;
				
				case 'aq':
					$lang_name = '';
					$country_name = 'ANTARCTICA';
				break;

				case 'as':
					$lang_name = 'ASSAMESE';
					$country_name = 'AMERICAN_SAMOA';
				break;

				case 'at':
					$lang_name = 'GERMAN';
					$country_name = 'AUSTRIA';
				break;

				case 'av':
					$lang_name = 'AVARIC';
					$country_name = '';
				break;

				case 'av-da':
				case 'av_da':
				case 'av_DA':
					$lang_name = 'AVARIAN_KHANATE';
					$country_name = 'Daghestanian';
				break;

				case 'ay':
					$lang_name = 'AYMARA';
					$country_name = '';
				break;

				case 'aw':
					$lang_name = 'ARUBA';
					$country_name = 'ARUBA';
				break;

				case 'au':
					$lang_name = 'en-au'; //
					$country_name = 'AUSTRALIA';
				break;

				case 'az':
					$lang_name = 'AZERBAIJANI';
					$country_name = 'AZERBAIJAN';
				break;
				
				case 'ax':
					$lang_name = 'FINNISH';
					$country_name = 'ÅLAND_ISLANDS';  //The Åland Islands or Åland (Swedish: Åland, IPA: [ˈoːland]; Finnish: Ahvenanmaa) is an archipelago province at the entrance to the Gulf of Bothnia in the Baltic Sea belonging to Finland.
				break;
				
				case 'ba':
					$lang_name = 'BASHKIR'; //Baskortostán (Rusia)
					$country_name = 'BOSNIA_&AMP;_HERZEGOVINA'; //Bosnian, Croatian, Serbian
				break;
				
				//Bavarian (also known as Bavarian Austrian or Austro-Bavarian; Boarisch [ˈbɔɑrɪʃ] or Bairisch; 
				//German: Bairisch [ˈbaɪʁɪʃ] (About this soundlisten); Hungarian: bajor.
				case 'bar':
					$lang_name = 'BAVARIAN';
					$country_name = 'BAVARIA'; //Germany
				break;
				
				case 'bb':
					$lang_name = 'Barbados';
					$country_name = 'BARBADOS';
				break;

				case 'bd':
					$lang_name = 'Bangladesh';
					$country_name = 'BANGLADESH';
				break;

				case 'be':
					$lang_name = 'BELARUSIAN';
					$country_name = 'BELGIUM';
				break;

				case 'bf':
					$lang_name = 'Burkina Faso';
					$country_name = 'BURKINA_FASO';
				break;
				
				case 'bg':
					$lang_name = 'BULGARIAN';
					$country_name = 'BULGARIA';
				break;

				case 'bh':
					$lang_name = 'BHOJPURI'; // Bihar (India) 
					$country_name = 'BAHRAIN'; // Mamlakat al-Ba?rayn (arabic)
				break;

				case 'bi':
					$lang_name = 'BISLAMA';
					$country_name = 'BURUNDI';
				break;


				case 'bj':
					$lang_name = 'BENIN';
					$country_name = 'BENIN';
				break;
				
				case 'bl':
					$lang_name = 'BONAIRE';
					$country_name = 'BONAIRE';
				break;
				
				case 'bm':
					$lang_name = 'BAMBARA';
					$country_name = 'Bermuda';
				break;

				case 'bn':
					$country_name = 'BRUNEI';
					$lang_name = 'BENGALI';

				break;
				
				case 'bo':
					$lang_name = 'TIBETAN';
					$country_name = 'BOLIVIA';
				break;
				
				case 'br':
					$lang_name = 'BRETON';
					$country_name = 'BRAZIL'; //pt
				break;
				
				case 'bs':
					$lang_name = 'BOSNIAN';
					$country_name = 'BAHAMAS';
				break;

				case 'bt':
					$lang_name = 'Bhutan';
					$country_name = 'Bhutan';
				break;

				case 'bw':
					$lang_name = 'Botswana';
					$country_name = 'BOTSWANA';
				break;

				case 'bz':
					$lang_name = 'BELIZE';
					$country_name = 'BELIZE';
				break;

				case 'by':
					$lang_name = 'BELARUSIAN';
					$country_name = 'Belarus';
				break;
				
				case 'en-CM':
				case 'en_cm':
					$lang_name = 'CAMEROONIAN_PIDGIN_ENGLISH';
					$country_name = 'Cameroon';
				break;
				
				case 'wes':
					$lang_name = 'CAMEROONIAN'; //Kamtok
					$country_name = 'CAMEROON'; //Wes Cos
				break;

				case 'cm':
					$lang_name = 'CAMEROON';
					$country_name = 'CAMEROON';
				break;

				case 'ca':
					$lang_name = 'CATALAN';
					$country_name = 'CANADA';
				break;
				
				case 'cc':
					$lang_name = 'COA_A_COCOS'; //COA A Cocos dialect of Betawi Malay [ente (you) and ane (me)] and AU-English
					$country_name = 'COCOS_ISLANDS'; //CC 	Cocos (Keeling) Islands
				break;
				
				case 'cd':
					$lang_name = 'Congo Democratic Republic';
					$country_name = 'CONGO_DEMOCRATIC_REPUBLIC';
				break;
				
				//нохчийн мотт
				case 'ce':
					$lang_name = 'CHECHEN';
					$country_name = 'Chechenya';
				break;

				case 'cf':
					$lang_name = 'Central African Republic';
					$country_name = 'CENTRAL_AFRICAN_REPUBLIC';
				break;

				case 'cg':
					$lang_name = 'CONGO';
					$country_name = 'CONGO';
				break;
				
				case 'ch':
					$lang_name = 'CHAMORRO'; //Finu' Chamoru
					$country_name = 'SWITZERLAND';
				break;
				
				case 'ci':
					$lang_name = 'Cote D-Ivoire';
					$country_name = 'COTE_D-IVOIRE';
				break;
				
				case 'ck':
					$lang_name = '';
					$country_name = 'COOK_ISLANDS'; //CK 	Cook Islands
				break;
				
				case 'cl':
					$lang_name = 'Chile';
					$country_name = 'CHILE';
				break;
				
				case 'cn':
				//Chinese Macrolanguage
				case 'zh': //639-1: zh
				case 'chi': //639-2/B: chi
				case 'zho': //639-2/T and 639-3: zho
					$lang_name = 'CHINESE';
					$country_name = 'CHINA';
				break;
				//Chinese Individual Languages 
			    //	中文
				// Fujian Province, Republic of China
				case 'cn-fj':
				//	閩東話
				case 'cdo': 	//Chinese Min Dong  
					$lang_name = 'CHINESE_DONG';
					$country_name = 'CHINA';
				break;
				//1. Bingzhou		spoken in central Shanxi (the ancient Bing Province), including Taiyuan.
				//2. Lüliang		spoken in western Shanxi (including Lüliang) and northern Shaanxi.
				//3. Shangdang	spoken in the area of Changzhi (ancient Shangdang) in southeastern Shanxi.
				//4. Wutai			spoken in parts of northern Shanxi (including Wutai County) and central Inner Mongolia.
				//5. Da–Bao		spoken in parts of northern Shanxi and central Inner Mongolia, including Baotou.
				//6. Zhang-Hu	spoken in Zhangjiakou in northwestern Hebei and parts of central Inner Mongolia, including Hohhot.
				//7. Han-Xin		spoken in southeastern Shanxi, southern Hebei (including Handan) and northern Henan (including Xinxiang).
				//8. Zhi-Yan		spoken in Zhidan County and Yanchuan County in northern Shaanxi.
				//	晋语 / 晉語
				case 'cjy': 	//Chinese Jinyu 晉 	
					$lang_name = 'CHINA_JINYU';
					$country_name = 'CHINA';
				break;
				// Cantonese is spoken in Hong Kong
				// 官話
				case 'cmn': 	//Chinese Mandarin 普通话 (Pǔ tōng huà) literally translates into “common tongue.” 
					$lang_name = 'CHINESE_MANDARIN';
					$country_name = 'CHINA';
				break;
				// Mandarin is spoken in Mainland China and Taiwan
				// 閩語 / 闽语
				//semantic shift has occurred in Min or the rest of Chinese: 
			    //*tiaŋB 鼎 "wok". The Min form preserves the original meaning "cooking pot".
			    //*dzhənA "rice field". scholars identify the Min word with chéng 塍 (MC zying) "raised path between fields", but Norman argues that it is cognate with céng 層 (MC dzong) "additional layer or floor".
			    //*tšhioC 厝 "house". the Min word is cognate with shù 戍 (MC syuH) "to guard".
			    //*tshyiC 喙 "mouth". In Min this form has displaced the common Chinese term kǒu 口. It is believed to be cognate with huì 喙 (MC xjwojH) "beak, bill, snout; to pant".
				//Austroasiatic origin for some Min words:
			    //*-dəŋA "shaman" compared with Vietnamese đồng (/ɗoŋ2/) "to shamanize, to communicate with spirits" and Mon doŋ "to dance (as if) under demonic possession".
			    //*kiɑnB 囝 "son" appears to be related to Vietnamese con (/kɔn/) and Mon kon "child".
				
				// Southern Min: 
				//		Datian Min; 
				//		Hokkien 話; Hokkien-Taiwanese 閩台泉漳語 - Philippine Hokkien 咱儂話.
				//		Teochew; 
				//		Zhenan Min; 
				//		Zhongshan Min, etc.
				
				//Pu-Xian Min (Hinghwa); Putian dialect: Xianyou dialect.
				
				//Northern Min:  Jian'ou dialect; Jianyang dialect; Chong'an dialect; Songxi dialect; Zhenghe dialect;
				
				//Shao-Jiang Min: Shaowu dialect, Jiangle dialect, Guangze dialect, Shunchang dialect;
				//http://www.shanxigov.cn/
				//Central Min: Sanming dialect; Shaxian dialect; Yong'an dialect,
				
				//Leizhou Min	: Leizhou Min.
				
				//Abbreviation
				//Simplified Chinese:	闽
				//Traditional Chinese:	閩
				//Literal meaning:	Min [River]	
				
				//莆仙片  
				case 'cpx': 	//Chinese Pu-Xian Min, Sing-iú-uā / 仙游話, (Xianyou dialect) http://www.putian.gov.cn/
					$lang_name = 'CHINESE_PU-XIAN';
					$country_name = 'CHINA';
				break;
				// 徽語
				case 'czh': 	//Chinese HuiZhou 	惠州 http://www.huizhou.gov.cn/ | Song dynasty
					$lang_name = 'CHINESE_HUIZHOU';
					$country_name = 'CHINA';
				break;
				// 閩中片
				case 'czo': 	//Chinese Min Zhong 閩中語 |  闽中语  http://zx.cq.gov.cn/ | Zhong-Xian | Zhong  忠县
					$lang_name = 'CHINESE_ZHONG';
					$country_name = 'CHINA';
				break;
				// 東干話 SanMing: http://www.sm.gov.cn/ | Sha River (沙溪)
				case 'dng': 	//Ding  Chinese 
					$lang_name = 'DING_CHINESE';
					$country_name = 'CHINA';
				break;
				//	贛語
				case 'gan': 	//Gan Chinese  
					$lang_name = 'GAN_CHINESE';
					$country_name = 'CHINA';
				break;
				// 客家話
				case 'hak': 	//Chinese  Hakka 
					$lang_name = 'CHINESE_HAKKA';
					$country_name = 'CHINA';
				break;
				
				case 'hsn': 	//Xiang Chinese 湘語/湘语	
					$lang_name = 'XIANG_CHINESE';
					$country_name = 'CHINA';
				break;
				//	文言
				case 'lzh': 	//Literary Chinese 	
					$lang_name = 'LITERARY_CHINESE';
					$country_name = 'CHINA';
				break;
				// 閩北片
				case 'mnp': 	//Min Bei Chinese 
					$lang_name = 'MIN_BEI_CHINESE';
					$country_name = 'CHINA';
				break;
				// 閩南語
				case 'nan': 	//Min Nan Chinese 	
					$lang_name = 'MIN_NAN_CHINESE';
					$country_name = 'CHINA';
				break;			 
				 // 吴语
				case 'wuu': 	//Wu Chinese 
					$lang_name = 'WU_CHINESE';
					$country_name = 'CHINA';
				break;
				// 粵語
				case 'yue': 	//Yue or Cartonese Chinese
					$lang_name = 'YUE_CHINESE';
					$country_name = 'CHINA';
				break;
				
				case 'co':
					$lang_name = 'CORSICAN'; // Corsica
					$country_name = 'COLUMBIA';
				break;
				//Eeyou Istchee ᐄᔨᔨᐤ ᐊᔅᒌ
				case 'cr':
					$lang_name = 'CREE';
					$country_name = 'COSTA_RICA';
				break;

				case 'cs':
					$lang_name = 'CZECH';
					$country_name = 'CZECH_REPUBLIC';
				break;

				case 'cu':
					$lang_name = 'SLAVONIC';
					$country_name = 'CUBA'; //langs: 
				break;

				case 'cv':
					$country_name = 'CAPE_VERDE';
					$lang_name = 'CHUVASH';
				break;
				
				case 'cx':
					$lang_name = ''; // Malaysian Chinese origin and  European Australians 
					$country_name = 'CHRISTMAS_ISLAND';
				break;
				
				case 'cy':
					$lang_name = 'CYPRUS';
					$country_name = 'CYPRUS';
				break;
				
				case 'cz':
					$lang_name = 'CZECH';
					$country_name = 'CZECH_REPUBLIC';
				break;
				
				case 'cw':
					$lang_name = 'PAPIAMENTU';   // Papiamentu (Portuguese-based Creole), Dutch, English
					$country_name = 'CURAÇÃO'; // Ilha da Curação (Island of Healing)
				break;
				
				case 'da':
					$lang_name = 'DANISH';
					$country_name = 'DENMARK';
				break;
				
				//Geman (Deutsch)
				/*	deu – German
					gmh – Middle High German
					goh – Old High German
					gct – Colonia Tovar German
					bar – Bavarian
					cim – Cimbrian
					geh – Hutterite German
					ksh – Kölsch
					nds – Low German
					sli – Lower Silesian
					ltz – Luxembourgish
					vmf – Mainfränkisch
					mhn – Mòcheno
					pfl – Palatinate German
					pdc – Pennsylvania German
					pdt – Plautdietsch
					swg – Swabian German
					gsw – Swiss German
					uln – Unserdeutsch
					sxu – Upper Saxon
					wae – Walser German
					wep – Westphalian
					hrx – Riograndenser Hunsrückisch
					yec – Yenish	*/

				
				//Germany 	84,900,000 	75,101,421 (91.8%) 	5,600,000 (6.9%) 	De facto sole nationwide official language
				case 'de':
				case 'de-DE':
				case 'de_de':
				case 'deu':
					$lang_name = 'GERMAN';
					$country_name = 'GERMANY';
				break;
				//Belgium 	11,420,163 	73,000 (0.6%) 	2,472,746 (22%) 	De jure official language in the German speaking community
				case 'de_be':
				case 'de-BE':
					$lang_name = 'BELGIUM_GERMAN';
					$country_name = 'BELGIUM';
				break;
				 //Austria 	8,838,171 	8,040,960 (93%) 	516,000 (6%) 	De jure sole nationwide official language
				case 'de_at':
				case 'de-AT':
					$lang_name = 'AUSTRIAN_GERMAN';
					$country_name = 'AUSTRIA';
				break;
				 // Switzerland 	8,508,904 	5,329,393 (64.6%) 	395,000 (5%) 	Co-official language at federal level; de jure sole official language in 17, co-official in 4 cantons (out of 26)
				case 'de_sw':
				case 'de-SW':
					$lang_name = 'SWISS_GERMAN';
					$country_name = 'SWITZERLAND';
				break;
				
				 //Luxembourg 	602,000 	11,000 (2%) 	380,000 (67.5%) 	De jure nationwide co-official language
				case 'de_lu':
				case 'de-LU':
				case 'ltz':
					$lang_name = 'LUXEMBOURG_GERMAN';
					$country_name = 'LUXEMBOURG';
				break;
				 //Liechtenstein 	37,370 	32,075 (85.8%) 	5,200 (13.9%) 	De jure sole nationwide official language	
				//Alemannic, or rarely Alemmanish
				case 'de_li':
				case 'de-LI':
					$lang_name = 'LIECHTENSTEIN_GERMAN';
					$country_name = 'LIECHTENSTEIN';
				break;
				case 'gsw':
					$lang_name = 'Alemannic_German';
					$country_name = 'SWITZERLAND';
				break;
				//mostly spoken on Lifou Island, Loyalty Islands, New Caledonia. 
				case 'dhv':
					$lang_name = 'DREHU';
					$country_name = 'NEW_CALEDONIA';
				break;
				case 'pdc':
				//Pennsilfaanisch-Deitsche
					$lang_name = 'PENNSYLVANIA_DUTCH';
					$country_name = 'PENNSYLVANIA';
				break;				
				case 'dk':
					$lang_name = 'DANISH';
					$country_name = 'DENMARK';
				break;				
				
				//acf – Saint Lucian / Dominican Creole French		
				case 'acf':
					$lang_name = 'DOMINICAN_CREOLE_FRENCH'; //ROSEAU 
					$country_name = 'DOMINICA';
				break;
				
				case 'en_dm':
				case 'en-DM':
					$lang_name = 'DOMINICA_ENGLISH'; 
					$country_name = 'DOMINICA';
				break;

				case 'do':
				case 'en_do':
				case 'en-DO':
					$lang_name = 'SPANISH'; //Santo Domingo
					$country_name = 'DOMINICAN_REPUBLIC';
				break;

				case 'dj':
				case 'aa-DJ':
				case 'aa_dj':
					$lang_name = 'DJIBOUTI'; //Yibuti, Afar
					$country_name = 'REPUBLIC_OF_DJIBOUTI'; //République de Djibouti
				break;

				case 'dv':
					$lang_name = 'DIVEHI'; //Maldivian
					$country_name = 'MALDIVIA';
				break;
				
				//Berbera Taghelmustă (limba oamenilor albaștri), zisă și Tuaregă, este vorbită în Sahara occidentală.
				//Berbera Tamazigtă este vorbită în masivul Atlas din Maroc, la sud de orașul Meknes.
				//Berbera Zenatică zisă și Rifană, este vorbită în masivul Rif din Maroc, în nord-estul țării.
				//Berbera Șenuană zisă și Telică, este vorbită în masivul Tell din Algeria, în nordul țării.
				//Berbera Cabilică este vorbită în jurul masivelor Mitigea și Ores din Algeria, în nordul țării.
				//Berbera Șauiană este vorbită în jurul orașului Batna din Algeria.
				//Berbera Tahelhită, zisă și Șlănuană (în limba franceză Chleuh) este vorbită în jurul masivului Tubkal din Maroc, în sud-vestul țării.
				//Berbera Tamașekă, zisă și Sahariană, este vorbită în Sahara de nord, în Algeria, Libia și Egipt.
				//Berber: Tacawit (@ city Batna from Chaoui, Algery), Shawiya (Shauian)
				case 'shy':
					$lang_name = 'SHAWIYA_BERBER';
					$country_name = 'ALGERIA'; 
				break;

				case 'dz':
					$lang_name = 'DZONGKHA';
					$country_name = 'ALGERIA'; //http://www.el-mouradia.dz/
				break;

				case 'ec':
					$country_name = 'ECUADOR';
					$lang_name = 'ECUADOR';
				break;

				case 'eg':
					$country_name = 'EGYPT';
					$lang_name = 'EGYPT';
				break;

				case 'eh':
					$lang_name = 'WESTERN_SAHARA';
					$country_name = 'WESTERN_SAHARA';
				break;

				case 'ee':
					//Kɔsiɖagbe (Sunday)
					//Dzoɖagbe (Monday) 	
					//Braɖagbe, Blaɖagbe (Tuesday) 	
					//Kuɖagbe (Wednesday)
					//Yawoɖagbe (Thursday)
					//Fiɖagbe (Friday)
					//Memliɖagbe (Saturday)
					$lang_name = 'EWE'; //Èʋegbe Native to Ghana, Togo
					$country_name = 'ESTONIA';
				break;
				
				//Greek Language:
				//ell – Modern Greek
				//grc – Ancient Greek
				//cpg – Cappadocian Greek
				//gmy – Mycenaean Greek
				//pnt – Pontic
				//tsd – Tsakonian
				//yej – Yevanic
				
				case 'el':
					$lang_name = 'GREEK'; 
					$country_name = 'GREECE';
				break;
				
				case 'cpg':
					$lang_name = 'CAPPADOCIAN_GREEK';
					$country_name = 'GREECE';
				break;	
				case 'gmy':
					$lang_name = 'MYCENAEAN_GREEK';
					$country_name = 'GREECE';
				break;	
				case 'pnt':
					$lang_name = 'PONTIC';
					$country_name = 'GREECE';
				break;	
				case 'tsd':
					$lang_name = 'TSAKONIAN';
					$country_name = 'GREECE';
				break;	
				//Albanian: Janina or Janinë, Aromanian: Ianina, Enina, Turkish: Yanya;
				case 'yej':
					$lang_name = 'YEVANIC';	
					$country_name = 'GREECE';
				break;
				
				case 'en_uk':
				case 'en-UK':
				case 'uk':
					$lang_name = 'BRITISH_ENGLISH'; //used in United Kingdom
					$country_name = 'GREAT_BRITAIN';
				break;
				
				case 'en_fj':
				case 'en-FJ':
					$lang_name = 'FIJIAN_ENGLISH';
					$country_name = 'FIJI';
				break;
				
				case 'GibE':
				case 'en_gb':
				case 'en-GB':
				case 'gb':
					$lang_name = 'GIBRALTARIAN _ENGLISH'; //used in Gibraltar
					$country_name = 'GIBRALTAR';
				break;
				
				case 'en_us':
				case 'en-US':
					$lang_name = 'AMERICAN_ENGLISH';
					$country_name = 'UNITED_STATES_OF_AMERICA';
				break;
				
				case 'en_ie':
				case 'en-IE':
				case 'USEng':
					$lang_name = 'HIBERNO_ENGLISH'; //Irish English
					$country_name = 'IRELAND';
				break;
				
				case 'en_il':
				case 'en-IL':
				case 'ILEng':
				case 'heblish':
				case 'engbrew':
					$lang_name = 'ISRAELY_ENGLISH'; 
					$country_name = 'ISRAEL';
				break;
				
				case 'en_ca':
				case 'en-CA':
				case 'CanE':
					$lang_name = 'CANADIAN_ENGLISH'; 
					$country_name = 'CANADA';
				break;	
				
				case 'en_ck':
					$lang_name = 'COOK_ISLANDS_ENGLISH';
					$country_name = 'COOK_ISLANDS'; //CK 	Cook Islands
				break;	
				
				case 'en_in':
				case 'en-IN':
					$lang_name = 'INDIAN_ENGLISH'; 
					$country_name = 'REPUBLIC_OF_INDIA';
				break;
				
				case 'en_ai':
				case 'en-AI':
					$lang_name = 'ANGUILLAN_ENGLISH'; 
					$country_name = 'ANGUILLA';
				break;
				
				case 'en_au':
				case 'en-AU':
				case 'AuE': 
					$lang_name = 'AUSTRALIAN_ENGLISH'; 
					$country_name = 'AUSTRALIA';
				break;	
				
				case 'en_nz':
				case 'en-NZ':
				case 'NZE': 
					$lang_name = 'NEW_ZEALAND_ENGLISH'; 
					$country_name = 'NEW_ZEALAND';
				break;	
				
				//New England English
				case 'en_ne':
					$lang_name = 'NEW_ENGLAND_ENGLISH';
					$country_name = 'NEW_ENGLAND';
				break;
				
				//
				case 'en_bm':
					$lang_name = 'BERMUDIAN ENGLISH.';
					$country_name = 'BERMUDA';
				break;
								
				case 'en_nu':
					$lang_name = 'NIUEAN_ENGLISH'; //Niuean (official) 46% (a Polynesian language closely related to Tongan and Samoan)
					$country_name = 'NIUE'; // Niuean: Niuē
				break;
				
				case 'en_ms':
					$lang_name = 'MONTSERRAT_ENGLISH';
					$country_name = 'MONTSERRAT';
				break;	
				
				case 'en_pn':
					$lang_name = 'PITCAIRN_ISLAND_ENGLISH';
					$country_name = 'PITCAIRN_ISLAND';
				break;
								
				case 'en_sh':
					$lang_name = 'ST_HELENA_ENGLISH';
					$country_name = 'ST_HELENA';
				break;
				
				case 'en_tc':
					$lang_name = 'TURKS_&AMP;_CAICOS_IS_ENGLISH';
					$country_name = 'TURKS_&AMP;_CAICOS_IS';
				break;	

				case 'en_vg':
					$lang_name = 'VIRGIN_ISLANDS_ENGLISH';
					$country_name = 'VIRGIN_ISLANDS_(BRIT)';
				break;
				
				case 'eo':
					$lang_name = 'ESPERANTO'; //created in the late 19th century by L. L. Zamenhof, a Polish-Jewish ophthalmologist. In 1887
					$country_name = 'EUROPE';
				break;

				case 'er':
					$lang_name = 'ERITREA';
					$country_name = 'ERITREA';
				break;

				//See: 
				// http://www.webapps-online.com/online-tools/languages-and-locales
				// https://www.ibm.com/support/knowledgecenter/ko/SSS28S_3.0.0/com.ibm.help.forms.doc/locale_spec/i_xfdl_r_locale_quick_reference.html
				case 'es':	
				//Spanish Main	
					$lang_name = 'SPANISH';
					$country_name = 'SPAIN';
				break;
				case 'es_MX':
				case 'es_mx':
				//Spanish (Mexico) (es-MX)
					$lang_name = 'SPANISH_MEXICO';
					$country_name = 'MEXICO';
				break;				
				case 'es_US':
				case 'es_us':
					$lang_name = 'SPANISH_UNITED_STATES';
					$country_name = 'UNITED_STATES';
				break;				
				case 'es_419':	
				//Spanish	Latin America and the Caribbean
					$lang_name = 'SPANISH_CARIBBEAN';
					$country_name = 'CARIBBE';
				break;
				case 'es_ar':	
				//		Spanish	Argentina
					$lang_name = 'SPANISH_ARGENTINIAN';
					$country_name = 'ARGENTINA';
				break;
				case 'es_BO':
				case 'es_bo':
					$lang_name = 'SPANISH_BOLIVIAN';
					$country_name = 'BOLIVIA';
				break;				
				case 'es_BR':
				case 'es_br':
					$lang_name = 'SPANISH_BRAZILIAN';
					$country_name = 'BRAZIL';
				break;				
				case 'es_cl':	
				//		Spanish	Chile
					$lang_name = 'SPANISH_CHILEAN';
					$country_name = 'CHILE';
				break;
				case 'es_CO':	
				case 'es_co':	
				//	Spanish (Colombia) (es-CO)
					$lang_name = 'SPANISH_COLOMBIAN';
					$country_name = 'COLOMBIA';
				break;
				// Variety of es-419 Spanish Latin America and the Caribbean
				// Spanish language as spoken in 
				// the Caribbean islands of Cuba, 
				// Puerto Rico, and the Dominican Republic 
				// as well as in Panama, Venezuela, 
				// and the Caribbean coast of Colombia.
				case 'es-CU':	
				case 'es-cu':	
				//	Spanish (Cuba) (es-CU)
					$lang_name = 'CUBAN_SPANISH';
					$country_name = 'CUBA';
				break;
				case 'es_CR':
				case 'es_cr':
					$lang_name = 'SPANISH_COSTA_RICA';
					$country_name = 'COSTA_RICA';
				break;				
				case 'es_DO':	
				case 'es_do':
				//Spanish (Dominican Republic) (es-DO)
					$lang_name = 'SPANISH_DOMINICAN_REPUBLIC';
					$country_name = 'DOMINICAN_REPUBLIC';
				break;		
				case 'es_ec':	
				//		Spanish (Ecuador) (es-EC)
					$lang_name = 'SPANISH';
					$country_name = 'SPAIN';
				break;
				case 'es_es':	
				case 'es_ES':				
				//		Spanish	Spain
					$lang_name = 'SPANISH';
					$country_name = 'SPAIN';
				break;
				case 'es_ES_tradnl':	
				case 'es_es_tradnl':	
					$lang_name = 'SPANISH_NL';
					$country_name = 'NL';
				break;	
				case 'es_EU':	
				case 'es_eu':	
					$lang_name = 'SPANISH_EUROPE';
					$country_name = 'EUROPE';
				break;	
				case 'es_gt':
				case 'es_GT':				
				//	Spanish (Guatemala) (es-GT)
					$lang_name = 'SPANISH';
					$country_name = 'SPAIN';
				break;
				case 'es_HN':	
				case 'es_hn':	
				//Spanish (Honduras) (es-HN)
					$lang_name = 'SPANISH';
					$country_name = 'SPAIN';
				break;		
				case 'es_la':
				case 'es_LA':					
				//		Spanish	Lao
					$lang_name = 'SPANISH';
					$country_name = 'SPAIN';
				break;
				case 'es_NI':
				case 'es_ni':
				//		Spanish (Nicaragua) (es-NI)
					$lang_name = 'SPANISH_NICARAGUAN';
					$country_name = 'NICARAGUA';
				break;
				case 'es_PA':	
				case 'es_pa':	
				//Spanish (Panama) (es-PA)
					$lang_name = 'SPANISH_PANAMIAN';
					$country_name = 'PANAMA';
				break;		
				case 'es_pe':	
				case 'es_PE':					
				//Spanish (Peru) (es-PE)
					$lang_name = 'SPANISH_PERU';
					$country_name = 'PERU';
				break;
				case 'es_PR':	
				case 'es_pr':	
				//Spanish (Puerto Rico) (es-PR)
					$lang_name = 'SPANISH_PUERTO_RICO';
					$country_name = 'PUERTO_RICO';
				break;	
				case 'es_PY':	
				case 'es_py':	
				//Spanish (Paraguay) (es-PY)
					$lang_name = 'SPANISH_PARAGUAY';
					$country_name = 'PARAGUAY';
				break;	
				case 'es_SV':	
				case 'es_sv':	
				//Spanish (El Salvador) (es-SV)
					$lang_name = 'SPANISH_EL_SALVADOR';
					$country_name = 'EL_SALVADOR';
				break;	
				case 'es-US':	
				case 'es-us':	
				//	Spanish (United States) (es-US)
					$lang_name = 'SPANISH_UNITED_STATES';
					$country_name = 'UNITED_STATES';
				break;
				//This dialect is often spoken with an intonation resembling that of the Neapolitan language of Southern Italy, but there are exceptions.
				case 'es_AR':	
				case 'es_ar':
				//Spanish (Argentina) (es-AR)
					$lang_name = 'RIOPLATENSE_SPANISH_ARGENTINA';
					$country_name = 'ARGENTINA';
				break;	
				case 'es_UY':	
				case 'es_uy':
				//Spanish (Uruguay) (es-UY)
					$lang_name = 'SPANISH_URUGUAY';
					$country_name = 'URUGUAY';
				break;	
				case 'es_ve':	
				case 'es_VE':	
				//	Spanish (Venezuela) (es-VE)
					$lang_name = 'SPANISH_VENEZUELA';
					$country_name = 'BOLIVARIAN_REPUBLIC_OF_VENEZUELA';
				break;
				case 'es_xl':
				case 'es_XL':					
				//	Spanish	Latin America	
					$lang_name = 'SPANISH_LATIN_AMERICA';
					$country_name = 'LATIN_AMERICA';
				break;

				case 'et':
					$lang_name = 'ESTONIAN';
					$country_name = 'ESTONIA';
				break;

				case 'eu':
					$lang_name = 'BASQUE';
					$country_name = '';
				break;

				case 'fa':
					$lang_name = 'PERSIAN';
					$country_name = '';
				break;
				
				//for Fulah (also spelled Fula) the ISO 639-1 code is ff.
			    //fub – Adamawa Fulfulde
			    //fui – Bagirmi Fulfulde
			    //fue – Borgu Fulfulde
			    //fuq – Central-Eastern Niger Fulfulde
			    //ffm – Maasina Fulfulde
			    //fuv – Nigerian Fulfulde
			    //fuc – Pulaar
			    //fuf – Pular
			    //fuh – Western Niger Fulfulde			
			
				case 'fub':
					$lang_name = 'ADAMAWA_FULFULDE';
					$country_name = '';
				break;
				
				case 'fui':
					$lang_name = 'BAGIRMI_FULFULDE';
					$country_name = '';
				break;
				
				case 'fue':
					$lang_name = 'BORGU_FULFULDE';
					$country_name = '';
				break;
				
				case 'fuq':
					$lang_name = 'CENTRAL-EASTERN_NIGER_FULFULDE';
					$country_name = '';
				break;
				
				case 'ffm':
					$lang_name = 'MAASINA_FULFULDE';
					$country_name = '';
				break;
				
				case 'fuv':
					$lang_name = 'NIGERIAN_FULFULDE';
					$country_name = '';
				break;
				
				case 'fuc':
					$lang_name = 'PULAAR';
					$country_name = 'SENEGAMBIA_CONFEDERATION'; //sn //gm
				break;
				
				case 'fuf':
					$lang_name = 'PULAR';
					$country_name = '';
				break;
				
				case 'fuh':
					$lang_name = 'WESTERN_NIGER_FULFULDE';
					$country_name = '';
				break;
				
				case 'ff':
					$lang_name = 'FULAH';
					$country_name = '';
				break;	
				
				case 'fi':		
				case 'fin':
					$lang_name = 'FINNISH';
					$country_name = 'FINLAND';
				break;
				
				case 'fkv':
					$lang_name = 'KVEN';
					$country_name = 'NORWAY';
				break;
				
				case 'fit':
					$lang_name = 'KVEN';
					$country_name = 'SWEDEN';
				break;
				
				case 'fj':
					$lang_name = 'FIJIAN';
					$country_name = 'FIJI';
				break;

				case 'fk':
					$lang_name = 'FALKLANDIAN';
					$country_name = 'FALKLAND_ISLANDS';
				break;

				case 'fm':
					$lang_name = 'MICRONESIA';
					$country_name = 'MICRONESIA';
				break;

				case 'fo':
					$lang_name = 'FAROESE';
					$country_name = 'FAROE_ISLANDS';
				break;
				
				//Metropolitan French (French: France Métropolitaine or la Métropole)
				case 'fr':
				case 'fr_me':
					$lang_name = 'FRENCH';
					$country_name = 'FRANCE';
				break;
				//Acadian French
				case 'fr_ac':
					$lang_name = 'ACADIAN_FRENCH';
					$country_name = 'ACADIA';
				break;
				
				case 'fr_dm':
				case 'fr-DM':
					$lang_name = 'DOMINICA_FRENCH'; 
					$country_name = 'DOMINICA';
				break;
				
				//al-dîzāyīr
				case 'fr_dz':
					$lang_name = 'ALGERIAN_FRENCH';
					$country_name = 'ALGERIA';
				break;
				//Aostan French (French: français valdôtain)
				//Seventy:		septante[a] [sɛp.tɑ̃t]
				//Eighty:		huitante[b] [ɥi.tɑ̃t]
				//Ninety:		nonante[c] [nɔ.nɑ̃t]
				case 'fr_ao':
					$lang_name = 'AOSTAN_FRENCH';
					$country_name = 'ITALY';
				break;
				//Belgian French
				case 'fr_bl':
					$lang_name = 'BELGIAN_FRENCH';
					$country_name = 'BELGIUM';
				break;
				//Cambodian French -  French Indochina
				case 'fr_cb':
					$lang_name = 'CAMBODIAN_FRENCH';
					$country_name = 'CAMBODIA';
				break;
				//Cajun French - Le Français Cajun - New Orleans
				case 'fr_cj':
					$lang_name = 'CAJUN_FRENCH';
					$country_name = 'UNITED_STATES';
				break;
				//Canadian French  (French: Français Canadien)
				//Official language in Canada,  New Brunswick, Northwest Territories, Nunavut, Quebec, Yukon, 
				//Official language in United States, Maine (de facto),  New Hampshire
				case 'fr_ca':
				case 'fr-CA':
					$lang_name = 'CANADIAN_FRENCH';
					$country_name = 'CANADA';
				break;
				//Guianese French
				case 'gcr':
				case 'fr_gu':
					$lang_name = 'GUIANESE_FRENCH';
					$country_name = 'FRENCH_GUIANA';
				break;
				//Guianese English
				case 'gyn':
				case 'en_gy':
					$lang_name = 'GUYANESE_CREOLE';
					$country_name = 'ENGLISH_GUIANA';
				break;
				//Haitian French
				case 'fr-HT':
				case 'fr_ht':
					$lang_name = 'HAITIAN_FRENCH';
					$country_name = 'HAITI'; //UNITED_STATES
				break;
				//Haitian English
				case 'en-HT':
				case 'en_ht':
					$lang_name = 'HAITIAN_CREOLE';
					$country_name = 'HAITI'; //UNITED_STATES
				break;				
				//Indian French
				case 'fr_id':
					$lang_name = 'INDIAN_FRENCH';
					$country_name = 'INDIA';
				break;
				case 'en_id':
					$lang_name = 'INDIAN_ENGLISH';
					$country_name = 'INDIA';
				break;
				//Jersey Legal French - Anglo-Norman French 
				case 'xno':
				case 'fr_je':
					$lang_name = 'JERSEY_LEGAL_FRENCH';
					$country_name = 'UNITED_STATES';
				break;
				
				case 'fr_kh':
					$lang_name = 'CAMBODIAN_FRENCH';
					$country_name = 'CAMBODIA';
				break;
				
				//Lao French
				case 'fr_la':
					$lang_name = 'LAO_FRENCH';
					$country_name = 'LAOS';
				break;
				//Louisiana French (French: Français de la Louisiane, Louisiana Creole: Françé la Lwizyàn)
				case 'frc':
				case 'fr_lu':
					$lang_name = 'LOUISIANIAN_FRENCH';
					$country_name = 'LOUISIANA'; 
				break;
				//Louisiana Creole
				case 'lou':
					$lang_name = 'LOUISIANA_CREOLE';
					$country_name = 'LOUISIANA'; 
				break;
				//Meridional French (French: Français Méridional, also referred to as Francitan)
				case 'fr_mr':
					$lang_name = 'MERIDIONAL_FRENCH'; 
					$country_name = 'OCCITANIA';
				break;
				//Missouri French
				case 'fr_mi':
					$lang_name = 'MISSOURI_FRENCH';
					$country_name = 'MISSOURI‎';
				break;
				//New Caledonian French vs New Caledonian Pidgin French
				case 'fr_nc':
					$lang_name = 'NEW_CALEDONIAN_FRENCH';
					$country_name = 'NEW_CALEDONIA';
				break;
				//Newfoundland French (French: Français Terre-Neuvien),
				case 'fr_nf':
					$lang_name = 'NEWFOUNDLAND_FRENCH';
					$country_name = 'CANADA';
				break;
				//New England French
				case 'fr_ne':
					$lang_name = 'NEW_ENGLAND_FRENCH';
					$country_name = 'NEW_ENGLAND';
				break;
				//Quebec French (French: français québécois; also known as Québécois French or simply Québécois)
				case 'fr_qb':
					$lang_name = 'QUEBEC_FRENCH';
					$country_name = 'CANADA';
				break;
				//Swiss French
				case 'fr_sw':
					$lang_name = 'SWISS_FRENCH';
					$country_name = 'SWITZERLAND';
				break;
				//French Southern and Antarctic Lands
				case 'fr_tf':				
				case 'tf':
					$lang_name = 'FRENCH_SOUTHERN_TERRITORIES'; //
					$country_name = 'SOUTHERN_TERRITORIES'; //Terres australes françaises
				break;
				//Vietnamese French
				case 'fr_vt':
					$lang_name = 'VIETNAMESE_FRENCH';
					$country_name = 'VIETNAM';
				break;
				//West Indian French
				case 'fr_if':
					$lang_name = 'WEST_INDIAN_FRENCH';
					$country_name = 'INDIA';
				break;
				
				case 'fr_wf':
					$country_name = 'TERRITORY_OF_THE_WALLIS_AND_FUTUNA_ISLANDS';
					$lang_name = 'WALLISIAN_FRENCH'; 
				break;	
				
				case 'fy':
					$lang_name = 'WESTERN_FRISIAN';
					$country_name = 'FRYSK';
				break;
				
				case 'ga':
					$lang_name = 'IRISH';
					$country_name = 'GABON';
				break;
				
				case 'GenAm':
					$lang_name = 'General American';
					$country_name = 'UNITED_STATES';
				break;

				//gcf – Guadeloupean Creole		
				case 'gcf':
					$lang_name = 'GUADELOUPEAN_CREOLE_FRENCH'; 
					$country_name = 'GUADELOUPE';
				break;
				
				case 'gd':
					$lang_name = 'SCOTTISH';
					$country_name = 'GRENADA';
				break;
				
				case 'ge':
					$lang_name = 'GEORGIAN';
					$country_name = 'GEORGIA';
				break;
				
				case 'gi':
					$lang_name = 'LLANITO'; //Llanito or Yanito
					$country_name = 'GIBRALTAR';
				break;
				
				case 'gg':
					$lang_name = 'GUERNESIAIS'; //English, Guernésiais, Sercquiais, Auregnais
					$country_name = 'GUERNSEY';
				break;
				
				case 'gh':
					$lang_name = 'Ghana';
					$country_name = 'GHANA';
				break;
				
				case 'ell':
					$lang_name = 'MODERN_GREEK'; 
					$country_name = 'GREECE';
				break;
				
				case 'gr':
				case 'gre':
					$lang_name = 'MODERN_GREEK'; 
					$country_name = 'GREECE';
				break;
				
				case 'grc':
					$lang_name = 'ANCIENT_GREEK'; 
					$country_name = 'GREECE';
				break;				
				
				//Galician is spoken by some 2.4 million people, mainly in Galicia, 
				//an autonomous community located in northwestern Spain.
				case 'gl':
					$lang_name = 'GALICIAN'; //Galicia
					$country_name = 'GREENLAND';
				break;
				
				case 'gm':
					$lang_name = 'Gambia';
					$country_name = 'GAMBIA';
				break;
				 
				//grn is the ISO 639-3 language code for Guarani. Its ISO 639-1 code is gn. 
				//    nhd – Chiripá
				//    gui – Eastern Bolivian Guaraní
				//    gun – Mbyá Guaraní
				//    gug – Paraguayan Guaraní
				//    gnw – Western Bolivian Guaraní
				case 'gn':
					$lang_name = 'GUARANI';
					$country_name = 'GUINEA';
				break;
				//Nhandéva is also known as Chiripá. 
				//The Spanish spelling, Ñandeva, is used in the Paraguayan Chaco 
				// to refer to the local variety of Eastern Bolivian, a subdialect of Avá.
				case 'nhd':
					$lang_name = 'Chiripa';
					$country_name = 'PARAGUAY';
				break;	
				case 'gui':
					$lang_name = 'EASTERN_BOLIVIAN_GUARANI';
					$country_name = 'BOLIVIA';
				break;				
				case 'gun':
					$lang_name = 'MBYA_GUARANI';
					$country_name = 'PARAGUAY';
				break;
				case 'gug':
					$lang_name = 'PARAGUAYAN_GUARANI';
					$country_name = 'PARAGUAY';
				break;
				case 'gnw':
					$lang_name = 'WESTERN_BOLIVIAN_GUARANI';
					$country_name = 'BOLIVIA';
				break;				
				
				case 'gs':
					$lang_name = 'ENGLISH';
					$country_name = 'SOUTH_GEORGIA_AND_THE_SOUTH_SANDWICH_ISLANDS';
				break;
				
				case 'gt':
					$lang_name = 'Guatemala';
					$country_name = 'GUATEMALA';
				break;
				
				case 'gq':
					$lang_name = 'Equatorial Guinea';
					$country_name = 'EQUATORIAL_GUINEA';
				break;

				case 'gu':
					$lang_name = 'GUJARATI';
					$country_name = 'GUAM';
				break;

				case 'gv':
					$lang_name = 'manx';
					$country_name = '';
				break;
				
				case 'gw':
					$lang_name = 'Guinea Bissau';
					$country_name = 'GUINEA_BISSAU';
				break;

				case 'gy':
					$lang_name = 'Guyana';
					$country_name = 'GUYANA';
				break;

				case 'ha':
					$country_name = '';
					$lang_name = 'HAUSA';
				break;

				//heb – Modern Hebrew
				//hbo – Classical Hebrew (liturgical)
				//smp – Samaritan Hebrew (liturgical)
				//obm – Moabite (extinct)
				//xdm – Edomite (extinct)
				case 'he':
				case 'heb':
					$country_name = 'ISRAEL';
					$lang_name = 'HEBREW';
				break;
				case 'hbo':
					$country_name = 'ISRAEL';
					$lang_name = 'CLASSICAL_HEBREW';
				break;
				case 'sam':
					$country_name = 'SAMARIA';
					$lang_name = 'SAMARITAN_ARAMEIC';
				break;
				case 'smp':
					$country_name = 'SAMARIA';
					$lang_name = 'SAMARITAN_HEBREW';
				break;
				case 'obm':
					$country_name = 'MOAB';
					$lang_name = 'MOABITE';
				break;
				case 'xdm':
					$country_name = 'EDOMITE';
					$lang_name = 'EDOM';
				break;
				case 'hi':
					$lang_name = 'hindi';
					$country_name = '';
				break;
				
				case 'ho':
					$lang_name = 'hiri_motu';
					$country_name = '';
				break;
				
				case 'hk':
					$lang_name = 'Hong Kong';
					$country_name = 'HONG_KONG';
				break;
				
				case 'hn':
					$country_name = 'Honduras';
					$lang_name = 'HONDURAS';
				break;
				
				case 'hr':
					$lang_name = 'croatian';
					$country_name = 'CROATIA';
				break;
				
				case 'ht':
					$lang_name = 'haitian';
					$country_name = 'HAITI';
				break;
				
				case 'ho':
					$lang_name = 'hiri_motu';
					$country_name = '';
				break;
				
				case 'hu':
					$lang_name = 'hungarian';
					$country_name = 'HUNGARY';
				break;
				
				case 'hy':
				case 'hy-am':
					$lang_name = 'ARMENIAN';
					$country_name = '';
				break;

				case 'hy-AT':
				case 'hy_at':
					$lang_name = 'ARMENIAN-ARTSAKH';
					$country_name = 'REPUBLIC_OF_ARTSAKH';
				break;

				case 'hz':
					$lang_name = 'HERERO';
					$country_name = '';
				break;
				
				case 'ia':
					$lang_name = 'INTERLINGUA';
					$country_name = '';
				break;
				
				case 'ic':
					$lang_name = '';
					$country_name = 'CANARY_ISLANDS';
				break;
				
				case 'id':
					$lang_name = 'INDONESIAN';
					$country_name = 'INDONESIA';
				break;
				
				case 'ie':
					$lang_name = 'interlingue';
					$country_name = 'IRELAND';
				break;
				
				case 'ig':
					$lang_name = 'igbo';
					$country_name = '';
				break;
				
				case 'ii':
					$lang_name = 'sichuan_yi';
					$country_name = '';
				break;
				
				case 'ik':
					$lang_name = 'inupiaq';
					$country_name = '';
				break;
				
				//Mostly spoken on  Ouvéa Island or Uvea Island of the Loyalty Islands, New Caledonia. 
				case 'iai':
					$lang_name = 'IAAI';
					$country_name = 'NEW_CALEDONIA';
				break;
				
				case 'il':
					$lang_name = 'ibrit';
					$country_name = 'ISRAEL';
				break;
				
				case 'im':
					$lang_name = 'Isle of Man';
					$country_name = 'ISLE_OF_MAN';
				break;
				
				case 'in':
					$lang_name = 'India';
					$country_name = 'INDIA';
				break;
				
				
				case 'ir':
					$lang_name = 'Iran';
					$country_name = 'IRAN';
				break;
				
				case 'is':
					$lang_name = 'Iceland';
					$country_name = 'ICELAND';
				break;
				
				case 'it':
					$lang_name = 'ITALIAN';
					$country_name = 'ITALY';
				break;
				
				case 'iq':
					$lang_name = 'Iraq';
					$country_name = 'IRAQ';
				break;
				
				case 'je':
					$lang_name = 'jerriais'; //Jèrriais
					$country_name = 'JERSEY'; //Bailiwick of Jersey
				break;
				
				case 'jm':
					$lang_name = 'Jamaica';
					$country_name = 'JAMAICA';
				break;
				
				case 'jo':
					$lang_name = 'Jordan';
					$country_name = 'JORDAN';
				break;
				
				case 'jp':
					$lang_name = 'japanese';
					$country_name = 'JAPAN';
				break;
				
				case 'jv':
					$lang_name = 'javanese';
					$country_name = '';
				break;
				
				case 'kh':
					$lang_name = 'KH';
					$country_name = 'CAMBODIA';
				break;
				
				case 'ke':
					$lang_name = 'SWAHILI';
					$country_name = 'KENYA';
				break;
				
				case 'ki':
					$lang_name = 'Kiribati';
					$country_name = 'KIRIBATI';
				break;
				
				//Bantu languages 
				//zdj – Ngazidja Comorian
				case 'zdj':
					$lang_name = 'Ngazidja Comorian';
					$country_name = 'COMOROS';
				break;
				//wni – Ndzwani  Comorian (Anjouani) dialect
				case 'wni':
					$lang_name = 'Ndzwani Comorian';
					$country_name = 'COMOROS';
				break;
				//swb – Maore Comorian dialect
				case 'swb':
					$lang_name = 'Maore Comorian';
					$country_name = 'COMOROS';
				break;
				//wlc – Mwali Comorian dialect				
				case 'wlc':
					$lang_name = 'Mwali Comorian';
					$country_name = 'COMOROS';
				break;
				
				case 'km':
					$lang_name = 'KHMER';
					$country_name = 'COMOROS';
				break;			
				
				case 'kn':
					$lang_name = 'kannada';
					$country_name = 'ST_KITTS-NEVIS';
				break;
				
				case 'ko':
				case 'kp':
					$lang_name = 'korean';
					// kor – Modern Korean
					// jje – Jeju
					// okm – Middle Korean
					// oko – Old Korean
					// oko – Proto Korean
					// okm Middle Korean
					 // oko Old Korean
					$country_name = 'Korea North';
				break;
				
				case 'kr':
					$lang_name = 'korean';
					$country_name = 'KOREA_SOUTH';
				break;
				
				case 'kn':
					$lang_name = 'St Kitts-Nevis';
					$country_name = 'ST_KITTS-NEVIS';
				break;
				
				case 'ks':
					$lang_name = 'kashmiri'; //Kashmir
					$country_name = 'KOREA_SOUTH';
				break;
				
				case 'ky':
					$lang_name = 'Cayman Islands';
					$country_name = 'CAYMAN_ISLANDS';
				break;

				case 'kz':
					$lang_name = 'Kazakhstan';
					$country_name = 'KAZAKHSTAN';
				break;

				case 'kw':
					//endonim: Kernewek
					$lang_name = 'Cornish';
					$country_name = 'KUWAIT';
				break;

				case 'kg':
					$lang_name = 'Kyrgyzstan';
					$country_name = 'KYRGYZSTAN';
				break;

				case 'la':
					$lang_name = 'Laos';
					$country_name = 'LAOS';
				break;

				case 'lk':
					$lang_name = 'Sri Lanka';
					$country_name = 'SRI_LANKA';
				break;

				case 'lv':
					$lang_name = 'Latvia';
					$country_name = 'LATVIA';
				break;
				
				case 'lb':
					$lang_name = 'LUXEMBOURGISH';
					$country_name = 'LEBANON';
				break;
				
				case 'lc':
					$lang_name = 'St Lucia';
					$country_name = 'ST_LUCIA';
				break;
				
				case 'ls':
					$lang_name = 'Lesotho';
					$country_name = 'LESOTHO';
				break;
				
				case 'lo':
					$lang_name = 'LAO';
					$country_name = 'LAOS'; 
				break;
				
				case 'lr':
					$lang_name = 'Liberia';
					$country_name = 'LIBERIA';
				break;

				case 'ly':
					$lang_name = 'Libya';
					$country_name = 'Libya';
				break;

				case 'li':
					$lang_name = 'LIMBURGISH';
					$country_name = 'LIECHTENSTEIN';
				break;

				case 'lt':
					$country_name = 'Lithuania';
					$lang_name = 'LITHUANIA';
				break;

				case 'lu':
					$lang_name = 'LUXEMBOURGISH';
					$country_name = 'LUXEMBOURG';
				break;
				
				case 'ma':
					$lang_name = 'Morocco';
					$country_name = 'MOROCCO';
				break;
				
				case 'mc':
					$country_name = 'MONACO';
					$lang_name = 'Monaco';
				break;
		
				case 'md':
					$country_name = 'MOLDOVA';
					$lang_name = 'romanian';
				break;	
				
				case 'me':
					$lang_name = 'MONTENEGRIN'; //Serbo-Croatian, Cyrillic, Latin
					$country_name = 'MONTENEGRO'; //Црна Гора
				break;
				
				case 'mf':
					$lang_name = 'FRENCH'; //
					$country_name = 'SAINT_MARTIN_(FRENCH_PART)'; 
				break;
				
				case 'mg':
					$lang_name = 'Madagascar';
					$country_name = 'MADAGASCAR';
				break;

				case 'mh':
					$lang_name = 'Marshall Islands';
					$country_name = 'MARSHALL_ISLANDS';
				break;
				
				case 'mi':
					$lang_name = 'MAORI';
					$country_name = 'Maori';
				break;
				
				//Mi'kmaq hieroglyphic writing was a writing system and memory aid used by the Mi'kmaq, 
				//a First Nations people of the east coast of Canada, Mostly spoken in Nova Scotia and Newfoundland.
				case 'mic':
					$lang_name = 'MIKMAQ';
					$country_name = 'CANADA';
				break;	
				
				case 'mk':
					$lang_name = 'Macedonia';
					$country_name = 'MACEDONIA';
				break;

				case 'mr':
					$lang_name = 'Mauritania';
					$country_name = 'Mauritania';
				break;

				case 'mu':
					$lang_name = 'Mauritius';
					$country_name = 'MAURITIUS';
				break;
				
				case 'mo':
					$lang_name = 'Macau';
					$country_name = 'MACAU';
				break;
				
				case 'mn':
					$lang_name = 'Mongolia';
					$country_name = 'MONGOLIA';
				break;

				case 'ms':
					$lang_name = 'Montserrat';
					$country_name = 'MONTSERRAT';
				break;
				
				case 'mz':
					$lang_name = 'Mozambique';
					$country_name = 'MOZAMBIQUE';
				break;
				
				case 'mm':
					$lang_name = 'Myanmar';
					$country_name = 'MYANMAR';
				break;
				
				case 'mp':
					$lang_name = 'chamorro'; //Carolinian
					$country_name = 'NORTHERN_MARIANA_ISLANDS';
				break;
				
				case 'mw':
					$country_name = 'Malawi';
					$lang_name = 'MALAWI';
				break;

				case 'my':
					$lang_name = 'Myanmar';
					$country_name = 'MALAYSIA';
				break;

				case 'mv':
					$lang_name = 'Maldives';
					$country_name = 'MALDIVES';
				break;

				case 'ml':
					$lang_name = 'Mali';
					$country_name = 'MALI';
				break;

				case 'mt':
					$lang_name = 'Malta';
					$country_name = 'MALTA';
				break;
				
				case 'mx':
					$lang_name = 'Mexico';
					$country_name = 'MEXICO';
				break;
				
				case 'mq':
					$lang_name = 'antillean-creole'; // Antillean Creole (Créole Martiniquais)
					$country_name = 'MARTINIQUE';
				break;
				
				case 'na':
					$lang_name = 'Nambia';
					$country_name = 'NAMBIA';
				break;
				
				case 'ni':
					$lang_name = 'Nicaragua';
					$country_name = 'NICARAGUA';
				break;
				
				//Barber: Targuí, tuareg
				case 'ne':
					$lang_name = 'Niger';
					$country_name = 'NIGER';
				break;
				
				//Mostly spoken on  Maré Island of the Loyalty Islands, New Caledonia. 
				case 'nen':
					$lang_name = 'NENGONE';
					$country_name = 'NEW_CALEDONIA';
				break;	
				
				case 'new':
					$lang_name = 'NEW_LANGUAGE'; 
					$country_name = 'NEW_COUNTRY';
				break;	
				
				case 'nc':
					$lang_name = 'paicî'; //French, Nengone, Paicî, Ajië, Drehu
					$country_name = 'NEW_CALEDONIA';
				break;
				
				case 'nk':
					$lang_name = 'Korea North';
					$country_name = 'KOREA_NORTH';
				break;
				
				case 'ng':
					$lang_name = 'Nigeria';
					$country_name = 'NIGERIA';
				break;
				
				case 'nf':
					$lang_name = 'Norfolk Island';
					$country_name = 'NORFOLK_ISLAND';
				break;
				
				case 'nl':
					$lang_name = 'DUTCH'; //Netherlands, Flemish.
					$country_name = 'NETHERLANDS';
				break;
				
				case 'no':
					$lang_name = 'Norway';
					$country_name = 'NORWAY';
				break;
				
				case 'np':
					$lang_name = 'Nepal';
					$country_name = 'NEPAL';
				break;
				
				case 'nr':
					$lang_name = 'Nauru';
					$country_name = 'NAURU';
				break;
				
				case 'niu':
					$lang_name = 'NIUEAN'; //Niuean (official) 46% (a Polynesian language closely related to Tongan and Samoan)
					$country_name = 'NIUE'; // Niuean: Niuē
				break;
				
				case 'nu':
					$lang_name = 'NU'; //Niuean (official) 46% (a Polynesian language closely related to Tongan and Samoan)
					$country_name = 'NIUE'; // Niuean: Niuē
				break;
				
				case 'nz':
					$lang_name = 'New Zealand';
					$country_name = 'NEW_ZEALAND';
				break;
				
				case 'ny':
					$lang_name = 'Chewa';
					$country_name = 'Nyanja';
				break;
				//langue d'oc
				case 'oc':
					$lang_name = 'OCCITAN';
					$country_name = 'OCCITANIA';
				break;

				case 'oj':
					$lang_name = 'ojibwa';
					$country_name = '';
				break;

				case 'om':
					$lang_name = 'Oman';
					$country_name = 'OMAN';
				break;

				case 'or':
					$lang_name = 'oriya';
					$country_name = '';
				break;

				case 'os':
					$lang_name = 'ossetian';
					$country_name = '';
				break;

				case 'pa':
					$country_name = 'Panama';
					$lang_name = 'PANAMA';
				break;


				case 'pe':
					$country_name = 'Peru';
					$lang_name = 'PERU';
				break;

				case 'ph':
					$lang_name = 'Philippines';
					$country_name = 'PHILIPPINES';
				break;
				
				case 'pf':
					$country_name = 'French Polynesia';
					$lang_name = 'tahitian'; //Polynésie française
				break;
				
				case 'pg':
					$country_name = 'PAPUA_NEW_GUINEA';
					$lang_name = 'Papua New Guinea';
				break;
				
				case 'pi':
					$lang_name = 'pali';
					$country_name = '';
				break;
				
				case 'pl':
					$lang_name = 'Poland';
					$country_name = 'POLAND';
				break;
				
				case 'pn':
					$lang_name = 'Pitcairn Island';
					$country_name = 'PITCAIRN_ISLAND';
				break;
				
				case 'pr':
					$lang_name = 'Puerto Rico';
					$country_name = 'PUERTO_RICO';
				break;
				
				case 'pt':
				case 'pt_pt':
					$lang_name = 'PORTUGUESE';
					$country_name = 'PORTUGAL';
				break;
				
					case 'pt_br':
					$lang_name = 'PORTUGAL';
					$country_name = 'BRAZIL'; //pt
				break;
				
				case 'pk':
					$lang_name = 'Pakistan';
					$country_name = 'PAKISTAN';
				break;
				
				case 'pw':
					$country_name = 'Palau Island';
					$lang_name = 'PALAU_ISLAND';
				break;
				
				case 'ps':
					$country_name = 'Palestine';
					$lang_name = 'PALESTINE';
				break;
				
				case 'py':
					$country_name = 'PARAGUAY';
					$lang_name = 'PARAGUAY';
				break;
				
				case 'qa':
					$lang_name = 'Qatar';
					$country_name = 'QATAR';
				break;
				
				//    rmn – Balkan Romani
				//    rml – Baltic Romani
				//    rmc – Carpathian Romani
				//    rmf – Kalo Finnish Romani
				//    rmo – Sinte Romani
				//    rmy – Vlax Romani
				//    rmw – Welsh Romani				
				case 'ri':
				case 'rom':
					$country_name = 'EASTEN_EUROPE';
					$lang_name = 'ROMANI';
				break;
				
				case 'ro':
					$country_name = 'ROMANIA';
					$lang_name = 'ROMANIAN';
				break;
				
				case 'ro_md':
				case 'ro_MD':
					$country_name = 'ROMANIA';
					$lang_name = 'ROMANIAN_MOLDAVIA';
				break;
				
				case 'ro_ro':
				case 'ro_RO':
					$country_name = 'ROMANIA';
					$lang_name = 'ROMANIAN_ROMANIA';
				break;				
				
				case 'rn':
					$lang_name = 'kirundi';
					$country_name = '';
				break;
				
				case 'rm':
					$country_name = '';
					$lang_name = 'romansh'; //Switzerland
				break;
				
				case 'rs':
					$country_name = 'REPUBLIC_OF_SERBIA'; //Република Србија //Republika Srbija
					$lang_name = 'serbian'; //Serbia, Србија / Srbija
				break;
				
				case 'ru':
				case 'ru_ru':
				case 'ru_RU':
					$country_name = 'RUSSIA';
					$lang_name = 'RUSSIAN';
				break;
				
				case 'rw':
					$country_name = 'RWANDA';
					$lang_name = 'Rwanda';
				break;

				
				case 'sa':
					$lang_name = 'arabic';
					$country_name = 'SAUDI_ARABIA';
				break;
				
				case 'sb':
					$lang_name = 'Solomon Islands';
					$country_name = 'SOLOMON_ISLANDS';
				break;
				
				case 'sc':
					$lang_name = 'seychellois-creole';
					$country_name = 'SEYCHELLES';
				break;
				
				case 'sco':
					$lang_name = 'SCOTISH';
					$country_name = 'Scotland';
				break;

				//scf – San Miguel Creole French (Panama)		
				case 'scf':
					$lang_name = 'SAN_MIGUEL_CREOLE_FRENCH';  
					$country_name = 'SAN_MIGUEL';
				break;	
				
				case 'sd':
					$lang_name = 'Sudan';
					$country_name = 'SUDAN';
				break;
				
				case 'si':
					$lang_name = 'SLOVENIAN';
					$country_name = 'SLOVENIA';
				break;
				
				case 'sh':
					$lang_name = 'SH';
					$country_name = 'ST_HELENA';
				break;
				
				case 'sk':
					$country_name = 'SLOVAKIA';
					$lang_name = 'Slovakia';
				break;
				
				case 'sg':
					$country_name = 'SINGAPORE';
					$lang_name = 'Singapore';
				break;
				
				case 'sl':
					$country_name = 'SIERRA_LEONE';
					$lang_name = 'Sierra Leone';
				break;
				
				case 'sm':
					$lang_name = 'San Marino';
					$country_name = 'SAN_MARINO';
				break;
				
				case 'smi':
					$lang_name = 'Sami';
					$country_name = 'Norway'; //Native to	Finland, Norway, Russia, and Sweden
				break;
				
				case 'sn':
					$lang_name = 'Senegal';
					$country_name = 'SENEGAL';
				break;
				
				case 'so':
					$lang_name = 'Somalia';
					$country_name = 'SOMALIA';
				break;
				
				case 'sq':
					$lang_name = 'ALBANIAN';
					$country_name = 'Albania';
				break;
				
				case 'sr':
					$lang_name = 'Suriname';
					$country_name = 'SURINAME';
				break;
				
				case 'ss':
					$lang_name = ''; //Bari [Karo or Kutuk ('mother tongue', Beri)], Dinka, Luo, Murle, Nuer, Zande
					$country_name = 'REPUBLIC_OF_SOUTH_SUDAN';
				break;
				
				case 'sse':
					$lang_name = 'STANDARD_SCOTTISH_ENGLISH';
					$country_name = 'Scotland';
				break;
				
				case 'st':
					$lang_name = 'Sao Tome &amp; Principe';
					$country_name = 'SAO_TOME_&AMP;_PRINCIPE';
				break;
				
				case 'sv':
					$lang_name = 'El Salvador';
					$country_name = 'EL_SALVADOR';
				break;
				
				case 'sx':
					$lang_name = 'dutch';
					$country_name = 'SINT_MAARTEN_(DUTCH_PART)';
				break;
				
				
				case 'sz':
					$lang_name = 'Swaziland';
					$country_name = 'SWAZILAND';
				break;
				
				case 'se':
				case 'sv-SE':
				case 'sv-se':
				//Swedish (Sweden) (sv-SE)
					$lang_name = 'Sweden';
					$country_name = 'SWEDEN';
				break;

				case 'sy':
					$lang_name = 'SYRIAC'; //arabic syrian
					$country_name = 'SYRIA';
				break;
				
				//ISO 639-2	swa
				//ISO 639-3	swa – inclusive code
				
				//Individual codes:
				//swc – Congo Swahili
				//swh – Coastal Swahili
				//ymk – Makwe
				//wmw – Mwani
				
				//Person	Mswahili
				//People	Waswahili
				//Language	Kiswahili				
				case 'sw':
					$lang_name = 'SWAHILI';
					$country_name = 'KENYA';
				break;
				case 'swa':
					$lang_name = 'SWAHILI';
					$country_name = 'AFRICAN_GREAT_LAKES';
				break;
				//swa – inclusive code
				//
				//Individual codes:
				//swc – Congo Swahili
				case 'swc':
					$lang_name = 'CONGO_SWAHILI';
					$country_name = 'CONGO';
				break;
				//swh – Coastal Swahili
				case 'swh':
					$lang_name = 'COASTAL_SWAHILI';
					$country_name = 'AFRIKA_EAST_COAST';
				break;	
				//ymk – Makwe
				case 'ymk':
					$lang_name = 'MAKWE';
					$country_name = 'CABO_DELGADO_PROVINCE_OF_MOZAMBIQUE';
				break;
				//wmw – Mwani
				case 'wmw':
					$lang_name = 'MWANI';
					$country_name = 'COAST_OF_CABO_DELGADO_PROVINCE_OF_MOZAMBIQUE';
				break;
				
				case 'tc':
					$lang_name = 'Turks &amp; Caicos Is';
					$country_name = 'TURKS_&AMP;_CAICOS_IS';
				break;
				
				case 'td':
					$lang_name = 'Chad';
					$country_name = 'CHAD';
				break;
				
				case 'tf':
					$lang_name = 'french '; //
					$country_name = 'FRENCH_SOUTHERN_TERRITORIES'; //Terres australes françaises
				break;
				
				case 'tj':
					$lang_name = 'Tajikistan';
					$country_name = 'TAJIKISTAN';
				break;
				
				case 'tg':
					$lang_name = 'Togo';
					$country_name = 'TOGO';
				break;
				
				case 'th':
					$country_name = 'Thailand';
					$lang_name = 'THAILAND';
				break;
				
				case 'tk':
					//260 speakers of Tokelauan, of whom 2,100 live in New Zealand, 
					//1,400 in Tokelau, 
					//and 17 in Swains Island
					$lang_name = 'Tokelauan'; // /toʊkəˈlaʊən/ Tokelauans or Polynesians
					$country_name = 'TOKELAUAU'; //Dependent territory of New Zealand
				break;
				
				case 'tl':
					$country_name = 'East Timor';
					$lang_name = 'East Timor';
				break;	
				
				case 'to':
					$country_name = 'Tonga';
					$lang_name = 'TONGA';
				break;
				
				case 'tt':
					$country_name = 'Trinidad &amp; Tobago';
					$lang_name = 'TRINIDAD_&AMP;_TOBAGO';
				break;
				
				case 'tn':
					$lang_name = 'Tunisia';
					$country_name = 'TUNISIA';
				break;
				
				case 'tm':
					$lang_name = 'Turkmenistan';
					$country_name = 'TURKMENISTAN';
				break;
				
				case 'tr':
					$lang_name = 'Turkey';
					$country_name = 'TURKEY';
				break;
				
				case 'tv':
					$lang_name = 'Tuvalu';
					$country_name = 'TUVALU';
				break;
				
				case 'tw':
					$lang_name = 'TAIWANESE_HOKKIEN'; //Taibei Hokkien
					$country_name = 'TAIWAN';
				break;
				
				case 'tz':
					$country_name = 'TANZANIA';
					$lang_name = 'Tanzania';
				break;

				case 'ug':
					$lang_name = 'Uganda';
					$country_name = 'UGANDA';
				break;

				case 'ua':
					$lang_name = 'Ukraine';
					$country_name = 'UKRAINE';
				break;

				case 'us':
					$lang_name = 'en-us';
					$country_name = 'UNITED_STATES_OF_AMERICA';
				break;
				
				case 'uz':
					$lang_name = 'uzbek'; //Uyghur Perso-Arabic alphabet
					$country_name = 'UZBEKISTAN';
				break;
				
				case 'uy':
					$lang_name = 'Uruguay';
					$country_name = 'URUGUAY';
				break;
				
				case 'va':
				case 'lat':
					$country_name = 'VATICAN_CITY'; //Holy See
					$lang_name = 'LATIN';
				break;
				
				case 'vc':
					$country_name = 'ST_VINCENT_&AMP;_GRENADINES'; //
					$lang_name = 'vincentian-creole';
				break;
				
				case 've':
					$lang_name = 'Venezuela';
					$country_name = 'VENEZUELA';
				break;
				
				case 'vi':
					$lang_name = 'Virgin Islands (USA)';
					$country_name = 'VIRGIN_ISLANDS_(USA)';
				break;
				
				case 'fr_vn':
					$lang_name = 'FRENCH_VIETNAM';
					$country_name = 'VIETNAM';
				break;				
				
				case 'vn':
					$lang_name = 'Vietnam';
					$country_name = 'VIETNAM';
				break;

				case 'vg':
					$lang_name = 'Virgin Islands (Brit)';
					$country_name = 'VIRGIN_ISLANDS_(BRIT)';
				break;
				
				case 'vu':
					$lang_name = 'Vanuatu';
					$country_name = 'VANUATU';
				break;
				
				case 'wls':
					$lang_name = 'WALLISIAN';
					$country_name = 'WALES';
				break;
				
				case 'wf':
					$country_name = 'TERRITORY_OF_THE_WALLIS_AND_FUTUNA_ISLANDS';
					$lang_name = 'WF'; 
					//Wallisian, or ʻUvean 
					//Futunan - Austronesian, Malayo-Polynesian
				break;
				
				case 'ws':
					$country_name = 'SAMOA';
					$lang_name = 'Samoa';
				break;
				
				case 'ye':
					$lang_name = 'Yemen';
					$country_name = 'YEMEN';
				break;
				
				case 'yt':
					$lang_name = 'Mayotte'; //Shimaore:
					$country_name = 'DEPARTMENT_OF_MAYOTTE'; //Département de Mayotte
				break;
				
				case 'za':
					$lang_name = 'zhuang';
					$country_name = 'SOUTH_AFRICA';
				break;
				case 'zm':
					$lang_name = 'zambian';
					$country_name = 'ZAMBIA';
				break;
				case 'zw':
					$lang_name = 'Zimbabwe';
					$country_name = 'ZIMBABWE';
				break;
				case 'zu':
					$lang_name = 'zulu';
					$country_name = 'ZULU';
				break;
				default:
					$lang_name = $file_dir;
					$country_name = $file_dir;
				break;
			}
			$return = ($lang_country == 'country') ? $country_name : $lang_name;
			$return = ($langs_countries == true) ? $lang_name[$country_name] : $return;
			return $return ;
	}
	
	/**
	 * @param string $var The key to look for
	 * @return mixed The data stored at the key
	 */
	public function __get($var)
	{
		if (isset($this -> $var))
		{
			return $this -> $var;
		}
		if ($var == 'size')
		{
			$this -> size = new Size(SHOW_DIR_SIZE ? $this -> dir_size() : false);
			return $this -> size;
		}
		throw new ExceptionDisplay('Variable <em>' . Url::html_output($var)
		. '</em> not set in DirItem class.');
	}
}

?>
