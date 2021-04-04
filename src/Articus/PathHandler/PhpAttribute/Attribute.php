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
		 * Name that should be passed to PluginManager::build
		 */
		public string $name,
		/**
		 * Options that should be passed to PluginManager::build
		 */
		public null|array $options = null,
		/**
		 * Priority in which attribute should added to request. The higher - the earlier.
		 * If two attributes have equal priority, the one declared earlier will be added earlier.
		 */
		public int $priority = 1,
	)
	{
	}
}
