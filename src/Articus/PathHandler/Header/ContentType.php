<?php
declare(strict_types=1);

namespace Articus\PathHandler\Header;

use InvalidArgumentException;
use LogicException;
use function count;
use function ord;
use function sprintf;
use function str_split;
use function strlen;
use function substr;

/**
 * A bit lengthy, but fairly optimized implentaion of "Content-Type" HTTP header parser and matcher.
 * @see https://tools.ietf.org/html/rfc7231#section-3.1.1.5
 */
class ContentType
{
	//Internal constants for states of header value parsing
	protected const STATE_TYPE_HEAD = 1;
	protected const STATE_TYPE_TAIL = 2;
	protected const STATE_SUBTYPE_HEAD = 3;
	protected const STATE_SUBTYPE_TAIL = 4;
	protected const STATE_SPACE_AFTER_SUBTYPE = 5;
	protected const STATE_SPACE_BEFORE_PARAM_NAME = 6;
	protected const STATE_PARAM_NAME_TAIL = 7;
	protected const STATE_PARAM_VALUE_HEAD = 8;
	protected const STATE_PARAM_UNQUOTED_VALUE_TAIL = 9;
	protected const STATE_PARAM_QUOTED_VALUE_BODY = 10;
	protected const STATE_PARAM_QUOTED_VALUE_ESCAPED_SYMBOL = 11;
	protected const STATE_PARAM_QUOTED_VALUE_TAIL = 12;
	protected const STATE_SPACE_AFTER_PARAM_VALUE = 13;
	//Internal constants for actions during header value parsing
	protected const ACTION_NONE = 0;
	protected const ACTION_AUGMENT_TYPE = 1;
	protected const ACTION_AUGMENT_SUBTYPE = 2;
	protected const ACTION_AUGMENT_PARAM_NAME = 3;
	protected const ACTION_AUGMENT_PARAM_VALUE = 4;
	protected const ACTION_COMPLETE_PARAM = 5;
	//Internal constant for default parsing context - (type, subtype, params, paramName, paramValue)
	protected const DEFAULT_CONTEXT = ['', '', [], '', ''];

	protected string $type;

	protected string $subtype;

	/**
	 * List of tuples ("parameter name", "parameter value")
	 * @var array<int, array{0: string, 1: string}>
	 */
	protected array $params;

	public function __construct(string $headerValue)
	{
		[$this->type, $this->subtype, $this->params] = self::parseHeaderValue($headerValue);
	}

	protected static function parseHeaderValue(string $headerValue): array
	{
		//Parsing context
		[$type, $subtype, $params, $paramName, $paramValue] = self::DEFAULT_CONTEXT;
		//Initial state
		$state = self::STATE_TYPE_HEAD;
		//Process header value
		foreach ((empty($headerValue) ? [] : str_split($headerValue)) as $index => $symbol)
		{
			[$state, $action] = self::switchState($state, $index, $symbol);
			switch ($action)
			{
				case self::ACTION_AUGMENT_TYPE:
					$type .= $symbol;
					break;
				case self::ACTION_AUGMENT_SUBTYPE:
					$subtype .= $symbol;
					break;
				case self::ACTION_AUGMENT_PARAM_NAME:
					$paramName .= $symbol;
					break;
				case self::ACTION_AUGMENT_PARAM_VALUE:
					$paramValue .= $symbol;
					break;
				case self::ACTION_COMPLETE_PARAM:
					$params[] = [$paramName, $paramValue];
					[ , , , $paramName, $paramValue] = self::DEFAULT_CONTEXT;
					break;
			}
		}
		//Process last state
		switch ($state)
		{
			case self::STATE_TYPE_HEAD:
				throw new InvalidArgumentException('Invalid media range: empty type');
			case self::STATE_TYPE_TAIL:
				throw new InvalidArgumentException('Invalid media range: no subtype');
			case self::STATE_SUBTYPE_HEAD:
				throw new InvalidArgumentException('Invalid media range: empty subtype');
			case self::STATE_SUBTYPE_TAIL:
				//Correct finite state, no extra work to do
				break;
			case self::STATE_SPACE_AFTER_SUBTYPE:
				throw new InvalidArgumentException('Invalid header: ended with whitespace after subtype');
			case self::STATE_SPACE_BEFORE_PARAM_NAME:
				throw new InvalidArgumentException('Invalid media range: no parameter');
			case self::STATE_PARAM_NAME_TAIL:
				throw new InvalidArgumentException('Invalid media range parameter: no value');
			case self::STATE_PARAM_VALUE_HEAD:
				throw new InvalidArgumentException('Invalid media range parameter: empty unquoted value');
			case self::STATE_PARAM_QUOTED_VALUE_BODY:
				throw new InvalidArgumentException('Invalid media range parameter: no closing quote for value');
			case self::STATE_PARAM_UNQUOTED_VALUE_TAIL:
			case self::STATE_PARAM_QUOTED_VALUE_TAIL:
				//Correct finite state, complete last parameter
				$params[] = [$paramName, $paramValue];
				break;
			case self::STATE_PARAM_QUOTED_VALUE_ESCAPED_SYMBOL:
				throw new InvalidArgumentException('Invalid media range parameter quoted value: no symbol to escape');
			case self::STATE_SPACE_AFTER_PARAM_VALUE:
				throw new InvalidArgumentException('Invalid header: ended with whitespace after parameter value');
			default:
				throw new LogicException(sprintf('Unknown state %s', $state));
		}

		return [$type, $subtype, $params];
	}

	protected static function switchState(int $state, int $step, string $symbol): array
	{
		switch ($state)
		{
			case self::STATE_TYPE_HEAD:
				switch ($symbol)
				{
					//tchar from https://tools.ietf.org/html/rfc7230#section-3.2.6
					case "!": case "#": case "$": case "%": case "&": case "'": case "*": case "+": case "-": case ".":
					case "^": case "_": case "`": case "|": case "~":
					case "0": case "1": case "2": case "3": case "4": case "5": case "6": case "7": case "8": case "9":
					case "a": case "b": case "c": case "d": case "e": case "f": case "g": case "h": case "i": case "j":
					case "k": case "l": case "m": case "n": case "o": case "p": case "q": case "r": case "s": case "t":
					case "u": case "v": case "w": case "x": case "y": case "z":
					case "A": case "B": case "C": case "D": case "E": case "F": case "G": case "H": case "I": case "J":
					case "K": case "L": case "M": case "N": case "O": case "P": case "Q": case "R": case "S": case "T":
					case "U": case "V": case "W": case "X": case "Y": case "Z":
						return [self::STATE_TYPE_TAIL, self::ACTION_AUGMENT_TYPE];
					default:
						throw new InvalidArgumentException(sprintf('Position %s: unexpected symbol code %s', $step, ord($symbol)));
				}
			case self::STATE_TYPE_TAIL:
				switch ($symbol)
				{
					//tchar from https://tools.ietf.org/html/rfc7230#section-3.2.6
					case "!": case "#": case "$": case "%": case "&": case "'": case "*": case "+": case "-": case ".":
					case "^": case "_": case "`": case "|": case "~":
					case "0": case "1": case "2": case "3": case "4": case "5": case "6": case "7": case "8": case "9":
					case "a": case "b": case "c": case "d": case "e": case "f": case "g": case "h": case "i": case "j":
					case "k": case "l": case "m": case "n": case "o": case "p": case "q": case "r": case "s": case "t":
					case "u": case "v": case "w": case "x": case "y": case "z":
					case "A": case "B": case "C": case "D": case "E": case "F": case "G": case "H": case "I": case "J":
					case "K": case "L": case "M": case "N": case "O": case "P": case "Q": case "R": case "S": case "T":
					case "U": case "V": case "W": case "X": case "Y": case "Z":
						return [$state, self::ACTION_AUGMENT_TYPE];
					case "/":
						return [self::STATE_SUBTYPE_HEAD, self::ACTION_NONE];
					default:
						throw new InvalidArgumentException(sprintf('Position %s: unexpected symbol code %s', $step, ord($symbol)));
				}
			case self::STATE_SUBTYPE_HEAD:
				switch ($symbol)
				{
					//tchar from https://tools.ietf.org/html/rfc7230#section-3.2.6
					case "!": case "#": case "$": case "%": case "&": case "'": case "*": case "+": case "-": case ".":
					case "^": case "_": case "`": case "|": case "~":
					case "0": case "1": case "2": case "3": case "4": case "5": case "6": case "7": case "8": case "9":
					case "a": case "b": case "c": case "d": case "e": case "f": case "g": case "h": case "i": case "j":
					case "k": case "l": case "m": case "n": case "o": case "p": case "q": case "r": case "s": case "t":
					case "u": case "v": case "w": case "x": case "y": case "z":
					case "A": case "B": case "C": case "D": case "E": case "F": case "G": case "H": case "I": case "J":
					case "K": case "L": case "M": case "N": case "O": case "P": case "Q": case "R": case "S": case "T":
					case "U": case "V": case "W": case "X": case "Y": case "Z":
						return [self::STATE_SUBTYPE_TAIL, self::ACTION_AUGMENT_SUBTYPE];
					default:
						throw new InvalidArgumentException(sprintf('Position %s: unexpected symbol code %s', $step, ord($symbol)));
				}
			case self::STATE_SUBTYPE_TAIL:
				switch ($symbol)
				{
					//tchar from https://tools.ietf.org/html/rfc7230#section-3.2.6
					case "!": case "#": case "$": case "%": case "&": case "'": case "*": case "+": case "-": case ".":
					case "^": case "_": case "`": case "|": case "~":
					case "0": case "1": case "2": case "3": case "4": case "5": case "6": case "7": case "8": case "9":
					case "a": case "b": case "c": case "d": case "e": case "f": case "g": case "h": case "i": case "j":
					case "k": case "l": case "m": case "n": case "o": case "p": case "q": case "r": case "s": case "t":
					case "u": case "v": case "w": case "x": case "y": case "z":
					case "A": case "B": case "C": case "D": case "E": case "F": case "G": case "H": case "I": case "J":
					case "K": case "L": case "M": case "N": case "O": case "P": case "Q": case "R": case "S": case "T":
					case "U": case "V": case "W": case "X": case "Y": case "Z":
						return [$state, self::ACTION_AUGMENT_SUBTYPE];
					case " ": case "\t":
						return [self::STATE_SPACE_AFTER_SUBTYPE, self::ACTION_NONE];
					case ";":
						return [self::STATE_SPACE_BEFORE_PARAM_NAME, self::ACTION_NONE];
					default:
						throw new InvalidArgumentException(sprintf('Position %s: unexpected symbol code %s', $step, ord($symbol)));
				}
			case self::STATE_SPACE_AFTER_SUBTYPE:
				switch ($symbol)
				{
					case " ": case "\t":
						return [$state, self::ACTION_NONE];
					case ";":
						return [self::STATE_SPACE_BEFORE_PARAM_NAME, self::ACTION_NONE];
					default:
						throw new InvalidArgumentException(sprintf('Position %s: unexpected symbol code %s', $step, ord($symbol)));
				}
			case self::STATE_SPACE_BEFORE_PARAM_NAME:
				switch ($symbol)
				{
					//tchar from https://tools.ietf.org/html/rfc7230#section-3.2.6
					case "!": case "#": case "$": case "%": case "&": case "'": case "*": case "+": case "-": case ".":
					case "^": case "_": case "`": case "|": case "~":
					case "0": case "1": case "2": case "3": case "4": case "5": case "6": case "7": case "8": case "9":
					case "a": case "b": case "c": case "d": case "e": case "f": case "g": case "h": case "i": case "j":
					case "k": case "l": case "m": case "n": case "o": case "p": case "q": case "r": case "s": case "t":
					case "u": case "v": case "w": case "x": case "y": case "z":
					case "A": case "B": case "C": case "D": case "E": case "F": case "G": case "H": case "I": case "J":
					case "K": case "L": case "M": case "N": case "O": case "P": case "Q": case "R": case "S": case "T":
					case "U": case "V": case "W": case "X": case "Y": case "Z":
						return [self::STATE_PARAM_NAME_TAIL, self::ACTION_AUGMENT_PARAM_NAME];
					case " ": case "\t":
						return [$state, self::ACTION_NONE];
					default:
						throw new InvalidArgumentException(sprintf('Position %s: unexpected symbol code %s', $step, ord($symbol)));
				}
			case self::STATE_PARAM_NAME_TAIL:
				switch ($symbol)
				{
					//tchar from https://tools.ietf.org/html/rfc7230#section-3.2.6
					case "!": case "#": case "$": case "%": case "&": case "'": case "*": case "+": case "-": case ".":
					case "^": case "_": case "`": case "|": case "~":
					case "0": case "1": case "2": case "3": case "4": case "5": case "6": case "7": case "8": case "9":
					case "a": case "b": case "c": case "d": case "e": case "f": case "g": case "h": case "i": case "j":
					case "k": case "l": case "m": case "n": case "o": case "p": case "q": case "r": case "s": case "t":
					case "u": case "v": case "w": case "x": case "y": case "z":
					case "A": case "B": case "C": case "D": case "E": case "F": case "G": case "H": case "I": case "J":
					case "K": case "L": case "M": case "N": case "O": case "P": case "Q": case "R": case "S": case "T":
					case "U": case "V": case "W": case "X": case "Y": case "Z":
						return [$state, self::ACTION_AUGMENT_PARAM_NAME];
					case "=":
						return [self::STATE_PARAM_VALUE_HEAD, self::ACTION_NONE];
					default:
						throw new InvalidArgumentException(sprintf('Position %s: unexpected symbol code %s', $step, ord($symbol)));
				}
			case self::STATE_PARAM_VALUE_HEAD:
				switch ($symbol)
				{
					//tchar from https://tools.ietf.org/html/rfc7230#section-3.2.6
					case "!": case "#": case "$": case "%": case "&": case "'": case "*": case "+": case "-": case ".":
					case "^": case "_": case "`": case "|": case "~":
					case "0": case "1": case "2": case "3": case "4": case "5": case "6": case "7": case "8": case "9":
					case "a": case "b": case "c": case "d": case "e": case "f": case "g": case "h": case "i": case "j":
					case "k": case "l": case "m": case "n": case "o": case "p": case "q": case "r": case "s": case "t":
					case "u": case "v": case "w": case "x": case "y": case "z":
					case "A": case "B": case "C": case "D": case "E": case "F": case "G": case "H": case "I": case "J":
					case "K": case "L": case "M": case "N": case "O": case "P": case "Q": case "R": case "S": case "T":
					case "U": case "V": case "W": case "X": case "Y": case "Z":
						return [self::STATE_PARAM_UNQUOTED_VALUE_TAIL, self::ACTION_AUGMENT_PARAM_VALUE];
					case '"':
						return [self::STATE_PARAM_QUOTED_VALUE_BODY, self::ACTION_NONE];
					default:
						throw new InvalidArgumentException(sprintf('Position %s: unexpected symbol code %s', $step, ord($symbol)));
				}
			case self::STATE_PARAM_UNQUOTED_VALUE_TAIL:
				switch ($symbol)
				{
					//tchar from https://tools.ietf.org/html/rfc7230#section-3.2.6
					case "!": case "#": case "$": case "%": case "&": case "'": case "*": case "+": case "-": case ".":
					case "^": case "_": case "`": case "|": case "~":
					case "0": case "1": case "2": case "3": case "4": case "5": case "6": case "7": case "8": case "9":
					case "a": case "b": case "c": case "d": case "e": case "f": case "g": case "h": case "i": case "j":
					case "k": case "l": case "m": case "n": case "o": case "p": case "q": case "r": case "s": case "t":
					case "u": case "v": case "w": case "x": case "y": case "z":
					case "A": case "B": case "C": case "D": case "E": case "F": case "G": case "H": case "I": case "J":
					case "K": case "L": case "M": case "N": case "O": case "P": case "Q": case "R": case "S": case "T":
					case "U": case "V": case "W": case "X": case "Y": case "Z":
						return [$state, self::ACTION_AUGMENT_PARAM_VALUE];
					case " ": case "\t":
						return [self::STATE_SPACE_AFTER_PARAM_VALUE, self::ACTION_NONE];
					case ";":
						return [self::STATE_SPACE_BEFORE_PARAM_NAME, self::ACTION_COMPLETE_PARAM];
					default:
						throw new InvalidArgumentException(sprintf('Position %s: unexpected symbol code %s', $step, ord($symbol)));
				}
			case self::STATE_PARAM_QUOTED_VALUE_BODY:
				switch ($symbol)
				{
					//qdtext from https://tools.ietf.org/html/rfc7230#section-3.2.6
					case " ": case "\t":
					case "\x21":
					case "\x23": case "\x24": case "\x25": case "\x26": case "\x27": case "\x28": case "\x29":
					case "\x2A": case "\x2B": case "\x2C": case "\x2D": case "\x2E": case "\x2F": case "\x30": case "\x31":
					case "\x32": case "\x33": case "\x34": case "\x35": case "\x36": case "\x37": case "\x38": case "\x39":
					case "\x3A": case "\x3B": case "\x3C": case "\x3D": case "\x3E": case "\x3F": case "\x40": case "\x41":
					case "\x42": case "\x43": case "\x44": case "\x45": case "\x46": case "\x47": case "\x48": case "\x49":
					case "\x4A": case "\x4B": case "\x4C": case "\x4D": case "\x4E": case "\x4F": case "\x50": case "\x51":
					case "\x52": case "\x53": case "\x54": case "\x55": case "\x56": case "\x57": case "\x58": case "\x59":
					case "\x5A": case "\x5B":
					case "\x5D": case "\x5E": case "\x5F": case "\x60": case "\x61": case "\x62": case "\x63": case "\x64":
					case "\x65": case "\x66": case "\x67": case "\x68": case "\x69": case "\x6A": case "\x6B": case "\x6C":
					case "\x6D": case "\x6E": case "\x6F": case "\x70": case "\x71": case "\x72": case "\x73": case "\x74":
					case "\x75": case "\x76": case "\x77": case "\x78": case "\x79": case "\x7A": case "\x7B": case "\x7C":
					case "\x7D": case "\x7E":
					case "\x80": case "\x81": case "\x82": case "\x83": case "\x84": case "\x85": case "\x86": case "\x87":
					case "\x88": case "\x89": case "\x8A": case "\x8B": case "\x8C": case "\x8D": case "\x8E": case "\x8F":
					case "\x90": case "\x91": case "\x92": case "\x93": case "\x94": case "\x95": case "\x96": case "\x97":
					case "\x98": case "\x99": case "\x9A": case "\x9B": case "\x9C": case "\x9D": case "\x9E": case "\x9F":
					case "\xA0": case "\xA1": case "\xA2": case "\xA3": case "\xA4": case "\xA5": case "\xA6": case "\xA7":
					case "\xA8": case "\xA9": case "\xAA": case "\xAB": case "\xAC": case "\xAD": case "\xAE": case "\xAF":
					case "\xB0": case "\xB1": case "\xB2": case "\xB3": case "\xB4": case "\xB5": case "\xB6": case "\xB7":
					case "\xB8": case "\xB9": case "\xBA": case "\xBB": case "\xBC": case "\xBD": case "\xBE": case "\xBF":
					case "\xC0": case "\xC1": case "\xC2": case "\xC3": case "\xC4": case "\xC5": case "\xC6": case "\xC7":
					case "\xC8": case "\xC9": case "\xCA": case "\xCB": case "\xCC": case "\xCD": case "\xCE": case "\xCF":
					case "\xD0": case "\xD1": case "\xD2": case "\xD3": case "\xD4": case "\xD5": case "\xD6": case "\xD7":
					case "\xD8": case "\xD9": case "\xDA": case "\xDB": case "\xDC": case "\xDD": case "\xDE": case "\xDF":
					case "\xE0": case "\xE1": case "\xE2": case "\xE3": case "\xE4": case "\xE5": case "\xE6": case "\xE7":
					case "\xE8": case "\xE9": case "\xEA": case "\xEB": case "\xEC": case "\xED": case "\xEE": case "\xEF":
					case "\xF0": case "\xF1": case "\xF2": case "\xF3": case "\xF4": case "\xF5": case "\xF6": case "\xF7":
					case "\xF8": case "\xF9": case "\xFA": case "\xFB": case "\xFC": case "\xFD": case "\xFE": case "\xFF":
						return [$state, self::ACTION_AUGMENT_PARAM_VALUE];
					case "\\":
						return [self::STATE_PARAM_QUOTED_VALUE_ESCAPED_SYMBOL, self::ACTION_NONE];
					case '"':
						return [self::STATE_PARAM_QUOTED_VALUE_TAIL, self::ACTION_NONE];
					default:
						throw new InvalidArgumentException(sprintf('Position %s: unexpected symbol code %s', $step, ord($symbol)));
				}
			case self::STATE_PARAM_QUOTED_VALUE_ESCAPED_SYMBOL:
				switch ($symbol)
				{
					case '"': case "\\":
						return [self::STATE_PARAM_QUOTED_VALUE_BODY, self::ACTION_AUGMENT_PARAM_VALUE];
					default:
						throw new InvalidArgumentException(sprintf('Position %s: unexpected symbol code %s', $step, ord($symbol)));
				}
			case self::STATE_PARAM_QUOTED_VALUE_TAIL:
				switch ($symbol)
				{
					case " ": case "\t":
						return [self::STATE_SPACE_AFTER_PARAM_VALUE, self::ACTION_NONE];
					case ";":
						return [self::STATE_SPACE_BEFORE_PARAM_NAME, self::ACTION_COMPLETE_PARAM];
					default:
						throw new InvalidArgumentException(sprintf('Position %s: unexpected symbol code %s', $step, ord($symbol)));
				}
			case self::STATE_SPACE_AFTER_PARAM_VALUE:
				switch ($symbol)
				{
					case " ": case "\t":
						return [$state, self::ACTION_NONE];
					case ";":
						return [self::STATE_SPACE_BEFORE_PARAM_NAME, self::ACTION_COMPLETE_PARAM];
					default:
						throw new InvalidArgumentException(sprintf('Position %s: unexpected symbol code %s', $step, ord($symbol)));
				}
			default:
				throw new LogicException(sprintf('Unknown state %s', $state));
		}
	}

	/**
	 * Checks if specified media range includes content type
	 * @param string $mediaRange
	 * @return bool
	 */
	public function match(string $mediaRange): bool
	{
		$result = false;
		$prefixes = ['*/*', $this->type . '/*', $this->type . '/' . $this->subtype];
		$prefixCount = count($prefixes);
		for ($index = 0; ($result === false) && ($index < $prefixCount); $index++)
		{
			$prefix = $prefixes[$index];
			switch (substr($mediaRange, 0, strlen($prefix) + 1))
			{
				case $prefix:
				case $prefix . ' ':
				case $prefix . "\t":
				case $prefix . ';':
					$result = true;
					break;
			}
		}
		return $result;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getSubtype(): string
	{
		return $this->subtype;
	}

	public function getMediaType(): string
	{
		return $this->type . '/' . $this->subtype;
	}

	/**
	 * @return array<int, array{0: string, 1: string}>
	 */
	public function getParameters(): array
	{
		return $this->params;
	}
}
