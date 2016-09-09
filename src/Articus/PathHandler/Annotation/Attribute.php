<?php

namespace Articus\PathHandler\Annotation;

/**
 * @Annotation
 * @Target({"METHOD","CLASS"})
 */
class Attribute
{
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
	 * Priority in which attribute should added to request
	 * @var integer
	 */
	public $priority = 1;
}