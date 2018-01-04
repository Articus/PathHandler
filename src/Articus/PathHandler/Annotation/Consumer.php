<?php
namespace Articus\PathHandler\Annotation;

use Doctrine\Common\Annotations\Annotation as DA;

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
	 * Name that should be passed to PluginManager::get
	 * @DA\Required()
	 * @var string
	 */
	public $name;

	/**
	 * Options that should be passed to PluginManager::get
	 * @var array
	 */
	public $options = null;

	/**
	 * Priority in which media type for consumer should check against request
	 * @var integer
	 */
	public $priority = 1;
}