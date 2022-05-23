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

use pocketmine\utils\EnumTrait;

class TranslatorTag{
	public const DEFAULT_ID = "{DEFAULT_LANGUAGE_ID}";
	public const DEFAULT_NAME = "{DEFAULT_LANGUAGE_NAME}";
	public const DEFAULT_VERSION = "{DEFAULT_LANGUAGE_VERSION}";

	public const LANGUAGE_ID = "{LANGUAGE_ID}";
	public const LANGUAGE_NAME = "{LANGUAGE_NAME}";
	public const LANGUAGE_VERSION = "{LANGUAGE_VERSION}";

	public const MESSAGE_KEY = "{MESSAGE_KEY}";
}