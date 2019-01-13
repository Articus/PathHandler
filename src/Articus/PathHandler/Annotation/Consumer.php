<?php
declare(strict_types=1);

namespace Articus\PathHandler\Annotation;

/**
 * Annotation for adding consumer service to handler
 * @Annotation
 * @Target({"METHOD", "CLASS"})
 */
class Consumer
{
	/**
	 * Content media type of the requests for which consumer should be used
	 * @var string
	 */
	public $mediaType = '*/*';

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
	 * Priority in which media type for consumer should check against request. The higher - the earlier.
	 * If two consumers have equal priority, the one declared earlier will be checked earlier.
	 * @var integer
	 */
	public $priority = 1;
}