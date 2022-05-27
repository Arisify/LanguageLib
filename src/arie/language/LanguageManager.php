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

final class LanguageManager{
	public const FIRST_PARTY = 0;
	public const SECOND_PARTY = 1;
	public const THIRD_PARTY = 2;
	public const FOURTH_PARTY = 3;

	private string $current;

	/** @var Language[] */

	protected array $languages = [];

	private string $filePath;

	/** @var int[]  */
	private array $types;

	/**
	 * @param PluginBase $plugin Todo
	 * @param string $folderName Where the language file in plugin resources
	 * @param string|null $default_language The default language
	 * @param float $default_version The default version
	 * @param bool $saveLanguage Should the plugin auto save the language resource to the plugin data folder
	 * @param bool $custom_language Allow 3rd language?
	 * @param array $blacklists A list of black listed files or patterns
	 */
	public function __construct(
		protected PluginBase $plugin,
		protected string $folderName = "",
		protected ?string $default_language = null,
		protected float $default_version = -1.0,
		protected bool $saveLanguage = true,
		protected bool $custom_language = true,
		array $blacklists = []

	){
		$this->filePath = $this->plugin->getDataFolder() . $folderName . "/";
		$bl = static function(string $path) use ($blacklists) : bool{
			foreach ($blacklists as $bl) {
				if (fnmatch($bl, $path)) {
					return true;
				}
			}
			return false;
		};

		/** @var resource $resource */
		foreach ($plugin->getResources() as $path => $resource) {
			if (dirname($path) !== $this->folderName || Utils::isSupportedFile($path) || $bl($path)) {
				fclose($resource);
				continue;
			}
			$id = pathinfo($path, PATHINFO_FILENAME);
			$this->types[$id] = self::FIRST_PARTY;
			if ($this->saveLanguage) {
				$this->plugin->saveResource($path);
			}
			if (!$this->custom_language) {
				$result = $this->register(language: Language::create($id, messages: yaml_parse(stream_get_contents($resource))));
				if ($result === false) {
					unset($this->types[$id]);
				}
			}
			fclose($resource);
		}
		if (!$this->custom_language) {
			foreach (glob($this->filePath . "*") as $path) {
				if (is_dir($path) || $bl(str_replace(DIRECTORY_SEPARATOR, "/", substr($path, strlen($this->filePath))))) {
					continue;
				}
				$id = pathinfo($path, PATHINFO_FILENAME);
				$this->types[$id] = isset($this->types[$id]) ? self::SECOND_PARTY : self::THIRD_PARTY;
				$result = $this->register(Language::createFromFile($path, $id));
				if ($result === false) {
					unset($this->types[$id]);
				}
			}
		}

		if (!isset($this->languages[$default_language])) {
			throw new \RuntimeException("Your default language must be registered before using!");
		}
		$this->current = $default_language;
		if ($default_version > $this->languages[$default_language]->getVersion()) {
			$language = $this->getLanguage();
			$this->plugin->getLogger()->notice($this->getMessage(LanguageTag::LANGUAGE_OUTDATED,
				[
					TranslatorTag::LANGUAGE_ID => $language->getId(),
					TranslatorTag::LANGUAGE_NAME => $language->getName(),
					TranslatorTag::DEFAULT_VERSION => $default_version
				],
				id: $this->current
			));
		}
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
		if ($replace || !isset($this->languages[$id])) {
			$this->languages[$id] = $language;
			if (!$this->types[$id] !== null) {
				$this->types[$id] = self::FOURTH_PARTY;
			}
			return true;
		}
		return false;
	}

	public function deleteLanguage(string $id) : bool{
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
		$this->current = $id;
		return true;
	}

	public function getMessage(string $key, array $replacements = [], ?string $default = null, ?string $id = null) : string{
		$message = $this->getLanguage($id)->getMessage($key);
		if ($message === null) {
			return $default ?? $key;
		}
		return empty($replacements) ? $message : strtr($message, $replacements);
	}

	public function getLanguage(?string $id = null, bool $default = true) : ?Language{
		return $this->languages[$id] ?? $default ? $this->languages[$this->current] : null;
	}

	public function getLanguageList() : array{
		return array_map(static fn(Language $language) : string => $language->getName(), $this->languages);
	}

	public function getTypes() : array{
		return $this->types;
	}

	public function getCurrent() : string{
		return $this->current;
	}

	public function getVersion() : float{
		return $this->default_version;
	}

	public function getPlugin() : PluginBase{
		return $this->plugin;
	}
}
