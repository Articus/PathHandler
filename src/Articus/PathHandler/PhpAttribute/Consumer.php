<?php
declare(strict_types=1);

namespace Articus\PathHandler\PhpAttribute;

/**
 * PHP attribute for adding consumer service to handler
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Consumer
{
	public function __construct(
		/**
		 * Content media range of the requests for which consumer should be used
		 */
		public string $mediaRange,
		/**
		 * Name that should be passed to PluginManager::build
		 */
		public string $name,
		/**
		 * Options that should be passed to PluginManager::build
		 */
		public null|array $options = null,
		/**
		 * Priority in which media type for consumer should check against request. The higher - the earlier.
		 * If two consumers have equal priority, the one declared earlier will be checked earlier.
		 */
		public int $priority = 1,
	)
	{
	}
}
