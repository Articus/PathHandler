<?php
declare(strict_types=1);

namespace Articus\PathHandler\Annotation;

/**
 * Annotation for adding producer service to handler
 * @Annotation
 * @Target({"METHOD", "CLASS"})
 */
class Producer
{
	/**
	 * Content media type of the responses for which producer should be used
	 * @Required
	 * @var string
	 */
	public $mediaType;

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
	 * Priority in which media type for producer should check against request. The higher - the earlier.
	 * If two producers have equal priority, the one declared earlier will be checked earlier.
	 * @var int
	 */
	public $priority = 1;
}