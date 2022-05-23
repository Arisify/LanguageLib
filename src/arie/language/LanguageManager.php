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

	public function __construct(
		protected PluginBase $plugin,
		protected string $folderName = "language",
		protected float $version = -1.0,
		protected string $default_language = "",
		protected $saveLanguage = true,
		protected $loadFromDataDir = true
	){
		$this->filePath = $this->plugin->getDataFolder() . $folderName . "/";
		/** @var resource $resource */
		foreach ($this->plugin->getResources() as $path => $resource) {
			if ($resource === null || basename(dirname($path)) !== $folderName) {
				continue;
			}
			$this->factory_languages[] = basename($path, '.yml');
			if ($this->saveLanguage) {
				$this->plugin->saveResource($path);
			}
			if (!$this->loadFromDataDir) {
				$this->register(Language::create(basename($path, '.yml'), messages: yaml_parse(stream_get_contents($resource)), factory: true));
			}
			fclose($resource);
		}
		if ($this->loadFromDataDir) {
			foreach (glob($this->filePath . "*.yml") as $language) {
				$id  = basename($language, ".yml"); //BRUH (My logic suck)
				$this->register(Language::createFromYML($language, $id, factory: in_array($id, $this->factory_languages, true)), true);
			}
		}
		if (!in_array($this->default_language, $this->factory_languages, true)) {
			throw new \RuntimeException("Default language should be registered!");
		}
		$this->current = $default_language;
		if ($this->version > $this->languages[$default_language]->getVersion()) {
			$language = $this->getLanguage();
			$this->plugin->getLogger()->notice($this->getMessage(LanguageTag::LANGUAGE_OUTDATED,
				[
					TranslatorTag::LANGUAGE_ID => $language->getId(),
					TranslatorTag::LANGUAGE_NAME => $language->getName(),
					TranslatorTag::DEFAULT_VERSION => $this->version
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
		return $this->version;
	}

	public function getPlugin() : PluginBase{
		return $this->plugin;
	}
}
