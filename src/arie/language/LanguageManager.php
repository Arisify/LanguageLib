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
	public float $default_version = -1.0;
	protected array $supported_languages = [];

	protected string $language_id;

	protected array $languages = [];
	private array $factory_languages = [];
	private string $filePath;

	public function __construct(
		protected PluginBase $plugin,
		protected string $folderName = "language",
		protected float $version = -1.0,
		protected bool $debug = false,
		protected string $default_language = "",
		protected $saveLanguage = true,
		protected $loadFromDataDir = true
	){
		$this->filePath = $this->plugin->getDataFolder() . $folderName . "/";
		foreach ($this->plugin->getResources() as $path => $resource) {
			if (basename(dirname($path)) !== $folderName) {
				continue;
			}
			$this->factory_languages[] = basename($path, '.yml');
			if ($this->saveLanguage) {
				$this->plugin->saveResource($path);
			}
			if (!$this->loadFromDataDir) {
				/** @var resource $resource */
				$this->register(Language::create(basename($path, '.yml'), messages: yaml_parse(stream_get_contents($resource)), factory: true));
			}
			/** @var resource $resource */
			fclose($resource);
		}
		if ($this->loadFromDataDir) {
			foreach (glob($this->filePath . "*.yml") as $language) {
				$id  = basename($language, ".yml"); //BRUH (My logic suck)
				$this->register(Language::createFromYML($language, factory: in_array($id, $this->factory_languages, true)), true);
			}
		}
	}

		//$this->plugin_language_version = (float) $this->plugin->getDescription()->getMap()["versions"]["language"];
		//$id = (string) $this->plugin->getConfig()->get("language", self::DEFAULT_LANGUAGE);
		/*
		if (!@mkdir($concurrentDirectory = $this->filePath) && !is_dir($concurrentDirectory)) {
			throw new \RuntimeException(sprintf($this->getMessage(LanguageTag::ERROR_DIR_NOT_FOUND), $concurrentDirectory));
		}

		foreach (self::SUPPORTED_LANGUAGES as $language) {
			$this->plugin->saveResource("language/" . $language . ".yml");
		}



		if (!array_key_exists($id, $this->language_names)) {
			$this->plugin->getLogger()->notice($this->getMessage(LanguageTag::LANGUAGE_DEFAULT_NOT_EXIST,
				[
					TranslatorTag::LANGUAGE_ID => $id,
					TranslatorTag::DEFAULT_LANGUAGE_NAME => $this->getLanguageName()
				],
				self::DEFAULT_LANGUAGE,
				true
			));
			$id = self::DEFAULT_LANGUAGE;
		}
		if ($this->plugin_language_version > $this->language_versions[$id]) {
			$this->plugin->getLogger()->notice($this->getMessage(LanguageTag::LANGUAGE_OUTDATED,
				[
					TranslatorTag::LANGUAGE_ID => $this->language_versions[$id],
					TranslatorTag::LANGUAGE_NAME => $this->getLanguageName($id),
					TranslatorTag::PLUGIN_LANGUAGE_VER => $this->plugin_language_version
				],
				$id,
				true
			));
		}
		$this->language_id = $id;
		$this->plugin->getLogger()->info($this->getMessage(LanguageTag::LANGUAGE_SET,
			[
				TranslatorTag::LANGUAGE_NAME => $this->getLanguageName(),
				TranslatorTag::LANGUAGE_ID => $this->language_id,
				TranslatorTag::LANGUAGE_VER => $this->language_versions[$id],
			],
			raw: true
		));
		*/


	public function register(Language $language, bool $replace = false) : bool{
		$id = $language->getId();
		if ($replace || !isset($this->languages[$id])) {
			$this->languages[$id] = $language;
			$this->language_names[$id] = $language->getName();
			$this->language_versions[$id] = $language->getVersion();
			return true;
		}
		return false;
	}

	public function setLanguage(string $id) : bool{
		if ($this->language_id === $id) {
			$this->plugin->getLogger()->info($this->getMessage(LanguageTag::LANGUAGE_ALREADY_SET,
				[
					TranslatorTag::LANGUAGE_NAME => $this->getLanguageName($id),
					TranslatorTag::LANGUAGE_ID => $id,
					TranslatorTag::LANGUAGE_VER => $this->language_versions[$id],
				],
				raw: true
			));
			return false;
		}
		if (!array_key_exists($id, $this->language_names)) {
			$this->plugin->getLogger()->info($this->getMessage(LanguageTag::LANGUAGE_NOT_SUPPORTED,
				[
					TranslatorTag::LANGUAGE_NAME => $this->getLanguageName($id),
				],
				raw: true
			));
			return false;
		}
		$this->language_id = $id;
		$this->raw_language = $this->getFactoryData($id);
		$this->plugin->getLogger()->info($this->getMessage(LanguageTag::LANGUAGE_SET,
			[
				TranslatorTag::LANGUAGE_NAME => $this->getLanguageName($id),
				TranslatorTag::LANGUAGE_ID => $id,
				TranslatorTag::LANGUAGE_VER => $this->language_versions[$id],
			],
			raw: true
		));
		return true;
	}


	public function getMessage(string $key, array $replacements = [], ?string $id = null, bool $raw = false) : string{
		$message = $this->languages[$id ?? $this->language_id]->getMessage($key);
		if ($message === null) {
			if ($this->debug) {
				$this->plugin->getLogger()->info($this->getMessage(LanguageTag::LANGUAGE_KEY_NOT_FOUND,
					[
						TranslatorTag::MESSAGE_KEY => $key,
						TranslatorTag::LANGUAGE_NAME => $this->getLanguageName($id),
						TranslatorTag::LANGUAGE_ID => $id
					]
				));
			}
			if (!$raw) {
				return $key;
			}
			$message = $this->raw_language[$key] ?? null;
			if ($message === null) {
				return $key;
			}
		}
		return empty($replacements) ? $message : strtr($message, $replacements);
	}

	public function getLanguage(string $id = self::DEFAULT_LANGUAGE) : Language{
		return $this->languages[$id];
	}

	public function getLanguageName(string $id = self::DEFAULT_LANGUAGE) : string{
		return $this->language_names[$id];
	}

	public function getPlugin() : PluginBase{
		return $this->plugin;
	}

	public function getFactoryData(string $id = self::DEFAULT_LANGUAGE) : ?array{
		if (!in_array($id, self::SUPPORTED_LANGUAGES, true)) {
			$id = self::DEFAULT_LANGUAGE;
		}
		$resource = $this->plugin->getResource("language/$id.yml");
		if ($resource === null) {
			return null;
		}
		$data = yaml_parse(stream_get_contents($resource));
		fclose($resource);
		return $data;
	}

	public function getLanguageList() : array{
		return $this->language_names;
	}

	public function getLanguageId() : string{
		return $this->language_id;
	}

	public function getPluginLanguageVersion() : float{
		return $this->plugin_language_version;
	}

	public function getLanguageVersion(string $id = self::DEFAULT_LANGUAGE) : float{
		return $this->language_versions[$id];
	}

	/**
	 * @return float
	 */
	public function getVersion() : float{
		return $this->version;
	}
}
