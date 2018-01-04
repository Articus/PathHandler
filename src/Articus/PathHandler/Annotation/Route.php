<?php

namespace Articus\PathHandler\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;

/**
 * Annotation to declare request route that should be processed by handler
 * @Annotation
 * @Target({"CLASS"})
 */
class Route
{
	/**
	 * Route pattern that should be passed to FastRoute\RouteCollector::addRoute
	 * @Required()
	 * @var string
	 */
	public $pattern;

	/**
	 * Unique name that will be used to identify route for URI generation.
	 * If not set it will be filled with pattern.
	 * @var string
	 */
	public $name;

	/**
	 * Default values for matched parameters that wll be available after routing.
	 * If handler parameter is not set it will be filled with FQN of the class that was annotated with route.
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