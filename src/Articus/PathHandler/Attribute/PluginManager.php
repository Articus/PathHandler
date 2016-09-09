<?php

namespace Articus\PathHandler\Attribute;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * Attribute plugin manager
 */
class PluginManager extends AbstractPluginManager
{
	protected $instanceOf = AttributeInterface::class;

	protected $factories = [
		Transfer::class => Factory::class,
	];

	protected $aliases = [
		'Transfer' => Transfer::class,
		'transfer' => Transfer::class,
	];

	/**
	 * @inheritdoc
	 * @return AttributeInterface
	 */
	public function get($name, array $options = null)
	{
		return parent::get($name, $options);
	}
}