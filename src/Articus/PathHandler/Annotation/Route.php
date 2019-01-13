<?php
declare(strict_types=1);

namespace Articus\PathHandler\Annotation;

/**
 * Annotation to declare request route that should be processed by handler
 * @Annotation
 * @Target({"CLASS"})
 */
class Route
{
	/**
	 * Unique name that will be used to identify route for URI generation.
	 * @var string
	 */
	public $name;

	/**
	 * Route path pattern.
	 * The exact syntax depends on router you choose.
	 * @Required
	 * @var string
	 */
	public $pattern;

	/**
	 * Default values for matched parameters that wll be available after routing.
	 * @var array
	 */
	public $defaults = [];

	/**
	 * Priority in which route should be registered in router. The higher - the earlier.
	 * If two routes have equal priority, the one declared earlier will be added earlier.
	 * @var integer
	 */
	public $priority = 1;
}