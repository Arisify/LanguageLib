<?php
/*
 * Copyright (c) 2022 Arisify
 *
 * This program is freeware, so you are free to redistribute and/or modify
 * it under the conditions of the MIT License.
 *
 * @author Arisify
 * @link   https://github.com/Arisify
 * @license https://opensource.org/licenses/MIT MIT License
 * •.,¸,.•*`•.,¸¸,.•*¯ ╭━━━━━━━━━━━━╮
 * •.,¸,.•*¯`•.,¸,.•*¯.|:::::::/\___/\
 * •.,¸,.•*¯`•.,¸,.•* <|:::::::(｡ ●ω●｡)
 * •.,¸,.•¯•.,¸,.•╰ *  し------し---Ｊ
 *
 *
*/
declare(strict_types=1);

namespace arie\language;

use pocketmine\plugin\PluginBase;

final class Utils{
	public const JSON = 0;
	public const YML = 1;
	public const LANG = 2;

	public const LANGUAGES_NAME = [
		"aa" => "Afar",
		"ab" => "Abkhazian",
		"ae" => "Avestan",
		"af" => "Afrikaans",
		"ak" => "Akan",
		"am" => "Amharic",
		"an" => "Aragonese",
		"ar" => "Arabic",
		"as" => "Assamese",
		"av" => "Avaric",
		"ay" => "Aymara",
		"az" => "Azerbaijani",
		"ba" => "Bashkir",
		"be" => "Belarusian",
		"bg" => "Bulgarian",
		"bh" => "Bihari",
		"bi" => "Bislama",
		"bm" => "Bambara",
		"bn" => "Bengali",
		"bo" => "Tibetan",
		"br" => "Breton",
		"bs" => "Bosnian",
		"ca" => "Catalan",
		"ce" => "Chechen",
		"ch" => "Chamorro",
		"co" => "Corsican",
		"cr" => "Cree",
		"cs" => "Czech",
		"cu" => "Church Slavic",
		"cv" => "Chuvash",
		"cy" => "Welsh",
		"da" => "Danish",
		"de" => "German",
		"dv" => "Divehi",
		"dz" => "Dzongkha",
		"ee" => "Ewe",
		"el" => "Greek",
		"en" => "English",
		"eo" => "Esperanto",
		"es" => "Spanish",
		"et" => "Estonian",
		"eu" => "Basque",
		"fa" => "Persian",
		"ff" => "Fulah",
		"fi" => "Finnish",
		"fj" => "Fijian",
		"fo" => "Faroese",
		"fr" => "French",
		"fy" => "Western Frisian",
		"ga" => "Irish",
		"gd" => "Scottish Gaelic",
		"gl" => "Galician",
		"gn" => "Guarani",
		"gu" => "Gujarati",
		"gv" => "Manx",
		"ha" => "Hausa",
		"he" => "Hebrew",
		"hi" => "Hindi",
		"ho" => "Hiri Motu",
		"hr" => "Croatian",
		"ht" => "Haitian",
		"hu" => "Hungarian",
		"hy" => "Armenian",
		"hz" => "Herero",
		"ia" => "Interlingua",
		"id" => "Indonesian",
		"ie" => "Interlingue",
		"ig" => "Igbo",
		"ii" => "Sichuan Yi",
		"ik" => "Inupiaq",
		"io" => "Ido",
		"is" => "Icelandic",
		"it" => "Italian",
		"iu" => "Inuktitut",
		"ja" => "Japanese",
		"jv" => "Javanese",
		"ka" => "Georgian",
		"kg" => "Kongo",
		"ki" => "Kikuyu",
		"kj" => "Kwanyama",
		"kk" => "Kazakh",
		"kl" => "Kalaallisut",
		"km" => "Khmer",
		"kn" => "Kannada",
		"ko" => "Korean",
		"kr" => "Kanuri",
		"ks" => "Kashmiri",
		"ku" => "Kurdish",
		"kv" => "Komi",
		"kw" => "Cornish",
		"ky" => "Kirghiz",
		"la" => "Latin",
		"lb" => "Luxembourgish",
		"lg" => "Ganda",
		"li" => "Limburgish",
		"ln" => "Lingala",
		"lo" => "Lao",
		"lt" => "Lithuanian",
		"lu" => "Luba-Katanga",
		"lv" => "Latvian",
		"mg" => "Malagasy",
		"mh" => "Marshallese",
		"mi" => "Maori",
		"mk" => "Macedonian",
		"ml" => "Malayalam",
		"mn" => "Mongolian",
		"mr" => "Marathi",
		"ms" => "Malay",
		"mt" => "Maltese",
		"my" => "Burmese",
		"na" => "Nauru",
		"nb" => "Norwegian Bokmal",
		"nd" => "North Ndebele",
		"ne" => "Nepali",
		"ng" => "Ndonga",
		"nl" => "Dutch",
		"nn" => "Norwegian Nynorsk",
		"no" => "Norwegian",
		"nr" => "South Ndebele",
		"nv" => "Navajo",
		"ny" => "Chichewa",
		"oc" => "Occitan",
		"oj" => "Ojibwa",
		"om" => "Oromo",
		"or" => "Oriya",
		"os" => "Ossetian",
		"pa" => "Panjabi",
		"pi" => "Pali",
		"pl" => "Polish",
		"ps" => "Pashto",
		"pt" => "Portuguese",
		"qu" => "Quechua",
		"rm" => "Raeto-Romance",
		"rn" => "Kirundi",
		"ro" => "Romanian",
		"ru" => "Russian",
		"rw" => "Kinyarwanda",
		"sa" => "Sanskrit",
		"sc" => "Sardinian",
		"sd" => "Sindhi",
		"se" => "Northern Sami",
		"sg" => "Sango",
		"si" => "Sinhala",
		"sk" => "Slovak",
		"sl" => "Slovenian",
		"sm" => "Samoan",
		"sn" => "Shona",
		"so" => "Somali",
		"sq" => "Albanian",
		"sr" => "Serbian",
		"ss" => "Swati",
		"st" => "Southern Sotho",
		"su" => "Sundanese",
		"sv" => "Swedish",
		"sw" => "Swahili",
		"ta" => "Tamil",
		"te" => "Telugu",
		"tg" => "Tajik",
		"th" => "Thai",
		"ti" => "Tigrinya",
		"tk" => "Turkmen",
		"tl" => "Tagalog",
		"tn" => "Tswana",
		"to" => "Tonga",
		"tr" => "Turkish",
		"ts" => "Tsonga",
		"tt" => "Tatar",
		"tw" => "Twi",
		"ty" => "Tahitian",
		"ug" => "Uighur",
		"uk" => "Ukrainian",
		"ur" => "Urdu",
		"uz" => "Uzbek",
		"ve" => "Venda",
		"vi" => "Vietnamese",
		"vo" => "Volapuk",
		"wa" => "Walloon",
		"wo" => "Wolof",
		"xh" => "Xhosa",
		"yi" => "Yiddish",
		"yo" => "Yoruba",
		"za" => "Zhuang",
		"zh" => "Chinese",
		"zu" => "Zulu"
	];

	public const SUPPORTED_FILE_TYPES = [
		"json" => Utils::JSON,
		"js" => Utils::JSON,
		"lang" => Utils::LANG
	];

	public static function getLocaleName(string $name) : string{
		return self::LANGUAGES_NAME[strtolower(strstr(str_replace('-', '_', $name), "_", true))] ?? "unknown";
	}

	public static function isSupportedFile(string $fileName) : bool{
		return in_array(pathinfo($fileName, PATHINFO_EXTENSION), self::SUPPORTED_FILE_TYPES, true);
	}

	public static function getPluginData(PluginBase $plugin, string $path) : ?array{
		if (!$plugin->isEnabled()) {
			return null;
		}
		$resource = $plugin->getResource($path);
		if ($resource === null) {
			return null;
		}
		$data = yaml_parse(stream_get_contents($resource));
		fclose($resource);
		return $data;
	}

	/**
	 * @return string[]
	 */
	public static function cleanUp(array $messages) : array{
		$result = [];
		foreach ($messages as $key => $message) {
			if (is_array($message)) {
				foreach ($message as $k => $m) {
					$result[$key . $k] = $m;
				}
			}
			$result[(string) $key] = (string) $message;
		}
		return $result;
	}

	/**
	 * @return string[]
	 */
	public static function parseProperties(string $content) : array{
		$result = [];
		if (preg_match_all('~^\s*(\D[\w\-_.]+)[ \t]*=([^\r\n#]*)~umx', $content, $matches) > 0) {
			foreach($matches[1] as $i => $k){
				$result[(string) $k] = trim($matches[2][$i]);
			}
		}

		return $result;
	}
}