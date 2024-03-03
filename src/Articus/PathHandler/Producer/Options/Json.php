<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer\Options;

use const JSON_UNESCAPED_UNICODE;

class Json
{
	/**
	 * Flags for json_encode
	 */
	public int $encodeFlags = JSON_UNESCAPED_UNICODE;

	/**
	 * Depth for json_encode and json_decode
	 */
	public int $depth = 512;

	public function __construct(iterable $options)
	{
		foreach ($options as $key => $value)
		{
			switch ($key)
			{
				case 'encodeFlags':
				case 'encode_flags':
				case 'flags':
					$this->encodeFlags = $value;
					break;
				case 'depth':
					$this->depth = $value;
					break;
			}
		}
	}
}
