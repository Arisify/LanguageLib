<?php
/**
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
use pocketmine\utils\Utils as PUtils;

final class LanguageManager{
	/** @const LANGUAGE TYPE */
	public const FIRST_PARTY = 0;
	public const SECOND_PARTY = 1;
	public const THIRD_PARTY = 2;
	public const FOURTH_PARTY = 3;

	/** @var string */
	private string $filePath;
	private string $current_language;

	/** @var Language[] */
	protected array $languages = [];

	/** @var int[]  */
	private array $types;

	/**
	 * @param PluginBase  $plugin           Attaching the plugin to the manager.
	 * @param string      $folderName       The name of the storage directory of the data, empty for nothing?
	 * @param string|null $default_language Setting the default language of the manager.
	 * @param float       $latest_version   Setting the default version of the language. This will be used for checking the outdated languages.
	 * @param bool        $saveLanguage     Should the plugin save the language resources to the data folder
	 * @param bool        $custom_language  Allowing users customizing the language messages
	 * @param array       $blacklists       Blacklisted files and patterns (Glob)
	 */
	public function __construct(
		protected PluginBase $plugin,
		protected string     $folderName = "",
		protected ?string    $default_language = null,
		protected float      $latest_version = -1.0,
		protected bool       $saveLanguage = true,
		protected bool       $custom_language = true,
		array                $blacklists = []
	){
		$this->filePath = $this->plugin->getDataFolder() . $folderName . "/";
		$bl = static function(string $path, string $root) use ($blacklists) : bool{
			$path = str_replace(DIRECTORY_SEPARATOR, "/", substr($path, strlen($root)));
			foreach ($blacklists as $bl) {
				if (fnmatch($bl, $path)) {
					return true;
				}
			}
			return false;
		};

		/** @var resource $resource */
		foreach ($plugin->getResources() as $path => $resource) {
			if (dirname($path) !== $folderName || Utils::isSupportedFile($path) || $bl($path, $this->folderName . "/")) {
				continue;
			}
			$id = pathinfo($path, PATHINFO_FILENAME);
			$this->types[$id] = self::FIRST_PARTY;

			if ($this->saveLanguage) {
				$plugin->saveResource($path);
			}
			if (!$custom_language && !$this->register(Language::create($id, messages: yaml_parse(stream_get_contents($resource))))) {
				unset($this->types[$id]);
			}
		}
		if ($custom_language) {
			foreach (glob($this->filePath . "*") as $path) {
				if (is_dir($path) || $bl($path, $this->filePath)) {
					continue;
				}
				$id = pathinfo($path, PATHINFO_FILENAME);
				$this->types[$id] = isset($this->types[$id]) ? self::SECOND_PARTY : self::THIRD_PARTY;
				if (!$this->register(Language::createFromFile($path, $id))) {
					unset($this->types[$id]);
				}
			}
		}

		if (!isset($this->languages[$default_language])) {
			throw new \RuntimeException("The default language must be registered before using!");
		}
		$this->current_language = $default_language;
	}

	/**
	 * Register the language
	 *
	 * @param Language $language The input language
	 * @param bool     $replace  Whether this should replace the existed one or the
	 * @return bool
	 */
	public function register(Language $language, bool $replace = false) : bool{
		$id = $language->getId();
		if (!isset($this->languages[$id]) || $replace) {
			$this->languages[$id] = $language;
			$this->types[$id] = self::FOURTH_PARTY;
			return true;
		}
		return false;
	}

	public function unregister(string $id) : bool{
		if (isset($this->languages[$id])) {
			unset($this->languages[$id], $this->types[$id]);
			return true;
		}
		return false;
	}

	public function setLanguage(string $id) : bool{
		if (!isset($this->languages[$id])) {
			return false;
		}
		$this->current_language = $id;
		return true;
	}

	/**
	 * @param string        $key          The search key
	 * @param array         $replacements The replacements array ["{BOO}" => "foo"]
	 * @param string|null   $default      Return this if the key is not found
	 * @param string|null   $id           The id of the language, default is the current language
	 * @param bool          $source       Search in the plugin source resources
	 * @param \Closure|null $onReturn     Filter function: signature `function(string $message)`
	 * @return string
	 */
	public function getMessage(string $key, array $replacements = [], ?string $default = null, ?string $id = null, bool $source = false, ?\Closure $onReturn = null) : string{
		$id ??= $this->current_language;
		$message = $this->getLanguage($id)->getMessage($key);
		if ($message === null) {
			if ($source) {
				$data = Utils::cleanUp(Utils::getPluginResource($this->plugin, $this->folderName . $id));
				if (isset($data[$key])) {
					return $data[$key];
				}
			}
			return $default ?? $key;
		}
		$m = empty($replacements) ? $message : strtr($message, $replacements);
		if ($onReturn !== null) {
			PUtils::validateCallableSignature(static function(string $message) : void{}, $onReturn);
			return $onReturn($m);
		}
		return $m;
	}

	public function getLanguage(?string $id = null) : ?Language{
		return $this->languages[$id] ?? $this->languages[$this->current_language] ?? null;
	}

	public function getLanguageList() : array{
		return array_map(static fn(Language $language) : string => $language->getName(), $this->languages);
	}

	public function getCurrentLanguage() : string{
		return $this->current_language;
	}

	public function getLatestVersion() : float{
		return $this->latest_version;
	}

	public function checkVersion(?string $id = null) : bool{
		return $this->latest_version > $this->getLanguage($id)->getVersion();
	}

	public function getPlugin() : PluginBase{
		return $this->plugin;
	}
}
