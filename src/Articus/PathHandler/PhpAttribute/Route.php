<?php
declare(strict_types=1);

namespace Articus\PathHandler\PhpAttribute;

/**
 * PHP attribute to declare request route that should be processed by handler
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class Route
{
	public function __construct(
		/**
		 * Route path pattern.
		 * The exact syntax depends on router you choose.
		 */
		public string $pattern,
		/**
		 * Default values for matched parameters that wll be available after routing.
		 */
		public array $defaults = [],
		/**
		 * Priority in which route should be registered in router. The higher - the earlier.
		 * If two routes have equal priority, the one declared earlier will be added earlier.
		 */
		public int $priority = 1,
		/**
		 * Unique name that will be used to identify route for URI generation.
		 */
		public null|string $name = null,
	)
	{
	}
}
