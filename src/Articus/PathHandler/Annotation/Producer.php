<?php

namespace Articus\PathHandler\Annotation;

/**
 * @Annotation
 * @Target({"METHOD", "CLASS"})
 */
class Producer
{
	/**
	 * Content media type of the requests for which producer should be used
	 * @var string
	 */
	public $mediaType;

	/**
	 * Name that should be passed to PluginManager::get
	 * @var string
	 */
	public $name;

	/**
	 * Options that should be passed to PluginManager::get
	 * @var array
	 */
	public $options = null;

	/**
	 * Priority in which media type for producer should check against request
	 * @var integer
	 */
	public $priority = 1;
}