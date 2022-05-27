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
declare(strict_types = 1);

namespace arie\language;

use pocketmine\utils\EnumTrait;

/**
 * @method static self JSON()
 * @method static self YAML()
 * @method static self TXT()
 * @method static self LANG()
 */

final class SupportedFileType{
	use EnumTrait{
		__construct as Enum__construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self('json', 0),
			new self('yaml', 1),
			new self('txt', 2),
			new self('lang', 3),
		);
	}

	private function __construct(string $name, protected int $format){
		$this->Enum__construct($name);
	}

	public function getFormat() : int{
		return $this->format;
	}

}
