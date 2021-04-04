<?php
declare(strict_types=1);

namespace Articus\PathHandler\PhpAttribute;

/**
 * PHP attribute for adding producer service to handler
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Producer
{
	public function __construct(
		/**
		 * Content media type of the responses for which producer should be used
		 */
		public string $mediaType,
		/**
		 * Name that should be passed to PluginManager::build
		 */
		public string $name,
		/**
		 * Options that should be passed to PluginManager::build
		 */
		public null|array $options = null,
		/**
		 * Priority in which media type for producer should check against request. The higher - the earlier.
		 * If two producers have equal priority, the one declared earlier will be checked earlier.
		 */
		public int $priority = 1,
	)
	{
	}
}
