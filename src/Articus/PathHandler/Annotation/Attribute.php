<?php
declare(strict_types=1);

namespace Articus\PathHandler\Annotation;

/**
 * Annotation for adding attribute service to handler
 * @Annotation
 * @Target({"METHOD","CLASS"})
 */
class Attribute
{
	/**
	 * Name that should be passed to PluginManager::build
	 * @Required
	 * @var string
	 */
	public $name;

	/**
	 * Options that should be passed to PluginManager::build
	 * @var array
	 */
	public $options = null;

	/**
	 * Priority in which attribute should added to request. The higher - the earlier.
	 * If two attributes have equal priority, the one declared earlier will be added earlier.
	 * @var int
	 */
	public $priority = 1;
}