<?php

namespace Articus\PathHandler\Consumer;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * Consumer plugin manager
 */
class PluginManager extends AbstractPluginManager
{
	protected $instanceOf = ConsumerInterface::class;

	protected $aliases = [
		'Json' => Json::class,
		'Internal' => Internal::class,
		'json' => Json::class,
		'internal' => Internal::class,
	];

	protected $factories = [
		Json::class => InvokableFactory::class,
		Internal::class => InvokableFactory::class,
	];
	
	/**
	 * Just for correct auto complete
	 * @inheritdoc
	 * @return ConsumerInterface
	 */
	public function get($name, array $options = null)
	{
		return parent::get($name, $options);
	}
}