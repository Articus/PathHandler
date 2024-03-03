<?php
declare(strict_types=1);

namespace Articus\PathHandler\PhpAttribute;

/**
 * PHP attribute for adding attribute service to handler
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Attribute
{
	public function __construct(
		/**
		 * Name that should be passed to attribute plugin manager
		 */
		public string $name,
		/**
		 * Options that should be passed to attribute plugin manager
		 */
		public array $options = [],
		/**
		 * Priority in which attribute should be executed against request. The higher - the earlier.
		 * If two attributes have equal priority, the one declared earlier will be added earlier.
		 */
		public int $priority = 1,
	)
	{
	}
}
