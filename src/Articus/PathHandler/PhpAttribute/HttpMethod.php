<?php
declare(strict_types=1);

namespace Articus\PathHandler\PhpAttribute;

/**
 * PHP attribute to declare that marked handler class method should be used to handle requests with specified HTTP method
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class HttpMethod
{
	public function __construct(
		/**
		 * Name of HTTP method
		 */
		public string $name
	)
	{
	}
}
