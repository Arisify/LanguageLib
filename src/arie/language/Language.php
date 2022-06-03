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

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

final class Language{
	public const HARDCODED_LANGUAGE_HEADER = "language";
	public const HARDCODED_LANGUAGE_NAME = "name";
	public const HARDCODED_LANGUAGE_VERSION = "version";
	public const DEFAULT_VERSION = -1.0;

	/** @var string[] */
	private array $messages;

	/**
	 * @param string      $id       The input id of the Language
	 * @param string|null $name     The input name of the Language
	 * @param float|null  $version  The input version of the Language
	 * @param array       $messages The input lists of the messages
	 */
	public function __construct(
		protected string  $id,
		protected ?string $name = null,
		protected ?float  $version = null,
		array             $messages = [],
		bool              $filter = true
	){
		$this->name ??= (string) ($messages[self::HARDCODED_LANGUAGE_HEADER . "." . self::HARDCODED_LANGUAGE_NAME] ?? Utils::getLocaleName($id));
		$this->version ??= (float) ($messages[self::HARDCODED_LANGUAGE_HEADER . "." . self::HARDCODED_LANGUAGE_VERSION] ?? self::DEFAULT_VERSION);
		$this->messages = $filter ? $messages : array_map(static fn(string $s) : string => TextFormat::colorize($s), $messages);
	}

	public static function create(string $id, ?string $name = null, ?float $version = null, array $messages = []) : Language{
		return new Language($id, $name, $version, $messages);
	}

	public static function createFromFile(string $filePath, ?string $id = null, ?string $name = null, ?float $version = null) : ?Language{
		$id ??= pathinfo($filePath, PATHINFO_FILENAME);
		$content = file_get_contents($filePath);
		try {
			return match (strtolower(pathinfo($filePath, PATHINFO_EXTENSION))) {
				"yml", "yaml" => new self($id, $name, $version, Utils::cleanUp(yaml_parse($content))),
				"js", "json" => new self($id, $name, $version, Utils::cleanUp(json_decode($content, false, 512, JSON_THROW_ON_ERROR))),
				"txt", "lang" => new self($id, $name, $version, Utils::cleanUp(Utils::parseProperties($content)))
			};
		} catch (\JsonException $e) {
			return null;
		}
	}

	public static function createFromConfig(Config $config, ?string $id = null, ?string $name = null, ?float $version = null) : Language{
		return new Language($id ?? pathinfo($config->getPath(), PATHINFO_FILENAME), $name, $version, Utils::cleanUp($config->getAll(true)));
	}

	public function getMessage(string $key) : ?string{
		return $this->messages[$key] ?? null;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return float
	 */
	public function getVersion() : float{
		return $this->version;
	}

	/**
	 * @return string
	 */
	public function getId() : string{
		return $this->id;
	}
}
