<?php
declare(strict_types=1);

namespace Articus\PathHandler\Consumer\Options;

use const JSON_OBJECT_AS_ARRAY;

class Json
{
	/**
	 * Flags for json_decode
	 */
	public int $decodeFlags = JSON_OBJECT_AS_ARRAY;

	/**
	 * Depth for json_decode
	 */
	public int $depth = 512;

	public function __construct(iterable $options)
	{
		foreach ($options as $key => $value)
		{
			switch ($key)
			{
				case 'decodeFlags':
				case 'decode_flags':
				case 'flags':
					$this->decodeFlags = $value;
					break;
				case 'depth':
					$this->depth = $value;
					break;
			}
		}
	}
}
