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
	private string $current;
	/** @var Language[] */
	protected array $languages = [];
	/** @var string[] */
	private array $factory_languages = [];
	private string $filePath;

	public array $format = [
		"json" => SupportedFileType::JSON(),
		"js" => SupportedFileType::JSON(),
		"yml" => SupportedFileType::YAML(),
		"yaml" => SupportedFileType::YAML(),
		"txt" => SupportedFileType::TXT(),
		"lang" => SupportedFileType::LANG()
	];


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
		}

		/** @var resource $resource */
		foreach ($plugin->getResources() as $path => $resource) {
			if (dirname($path) !== $this->folderName || $bl($path)) {
				fclose($resource);
				continue;
			}
			if ($this->saveLanguage) {
				$this->plugin->saveResource($path);
			}
			fclose($resource);
		}
		foreach ($blacklists as $blacklist) {
			$gl = glob($prefix . $blacklist);
			if ($gl !== false) {
				$resources = array_diff($resources, $gl);
			}
		}
		$footer = 0;
		foreach ($resources as $resource) {
			$id = pathinfo($resource, PATHINFO_ALL);
			if (in_array($id, $this->factory_languages, true)) {
				$id .= "_{++$footer}";
			} else {
				$footer = 0;
			}
			$this->factory_languages[] = $id;
			if (!$this->custom_language) {
				$this->register(Language::create(basename($resource, '.yml'), messages: yaml_parse(file_get_contents($resource)), factory: true));
			}
		}
		if ($this->custom_language) {
			foreach (glob($this->filePath) as $language) {
				$id  = basename($language, ".yml"); //BRUH (My logic suck)
				$this->register(Language::createFromFile($language, $id, factory: in_array($id, $this->factory_languages, true)), true);
			}
		}
		if (!in_array($this->default_language, $this->factory_languages, true)) {
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
				id: $this->current,factory: true
			));
		}
	}

	public function register(Language $language, bool $replace = false) : bool{
		$id = $language->getId();
		if ($replace || !isset($this->languages[$id])) {
			$this->languages[$id] = $language;
			return true;
		}
		return false;
	}

	public function setLanguage(string $id) : bool{
		if (!array_key_exists($id, $this->languages)) {
			return false;
		}
		$this->current = $id;
		return true;
	}

	public function getMessage(string $key, array $replacements = [], ?string $default = null, ?string $id = null, bool $factory = false) : string{
		$message = $this->getLanguage($id)->getMessage($key);
		if ($message === null) {
			if (!$factory) {
				return $key;
			}
			$message = $this->getFactoryData($key);
			if ($message === null) {
				return $default ?? $key;
			}
		}
		return empty($replacements) ? $message : strtr($message, $replacements);
	}

	public function getLanguage(?string $id = null, bool $default = true) : ?Language{
		return $this->languages[$id] ?? $default ? $this->languages[$this->current] : null;
	}

	public function getFactoryData(string $id = null) : ?array{
		if (!in_array($id, $this->factory_languages, true)) {
			$id = $this->default_language;
		}
		$resource = $this->plugin->getResource("language/$id.yml");
		if ($resource === null) {
			$content = file_get_contents($this->plugin->getPluginLoader()->getAccessProtocol() . $this->filePath . $id . ".yml");
			if ($content !== false) {
				return yaml_parse($content);
			}
			return null;
		}
		$data = yaml_parse(stream_get_contents($resource));
		fclose($resource);
		return $data;
	}

	public function getLanguageList() : array{
		return array_map(static fn(Language $language) : string => $language->getName(), $this->languages);
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
