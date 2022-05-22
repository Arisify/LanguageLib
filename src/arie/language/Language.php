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

final class Language{
	public const HARDCODED_LANGUAGE_NAME = "language.name";
	public const HARDCODED_LANGUAGE_VERSION = "language.version";

	public function __construct(
		protected ?string $id = null,
		protected ?string $name = null,
		protected ?float  $version = null,
		protected array   $messages = [],
		protected bool    $factory = false
	){
		if ($this->id === null) {
			throw new \UnexpectedValueException("The id was supposed to be a valid id but null was given!");
		}
		$this->name ??= (string) ($this->messages[self::HARDCODED_LANGUAGE_NAME] ?? LanguageList::getName($this->id));
		$this->version ??= (float) ($this->messages[self::HARDCODED_LANGUAGE_VERSION] ?? -1.0);
	}

	public static function create(string $id, string $name = "unknown", float $version = -1.0, array $messages = [], bool $factory = false) : Language{
		return new Language($id, $name, $version, $messages, $factory);
	}

	public static function createFromYML(string $filePath, ?string $id = null, ?string $name = null, float $version = -1.0, bool $factory = false) : Language{
		return new Language($id ?? basename($filePath, ".yml"), $name, $version, yaml_parse_file($filePath), $factory);
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
