<?php

namespace Articus\PathHandler\Annotation;
use Doctrine\Common\Annotations\Annotation as DA;

/**
 * Annotation for adding attribute service to handler
 * @Annotation
 * @Target({"METHOD","CLASS"})
 */
class Attribute
{
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
	 * Priority in which attribute should added to request
	 * @var integer
	 */
	public $priority = 1;
}