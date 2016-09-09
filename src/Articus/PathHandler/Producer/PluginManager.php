<?php

namespace Articus\PathHandler\Producer;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * Producer plugin manager
 */
class PluginManager extends AbstractPluginManager
{
	protected $instanceOf = ProducerInterface::class;

	protected $aliases = [
		'Json' => Json::class,
		'Template' => Template::class,
		'Transfer' => Transfer::class,
		'json' => Json::class,
		'template' => Template::class,
		'transfer' => Transfer::class,
	];

	protected $factories = [
		Json::class => InvokableFactory::class,
		Template::class => Factory::class,
		Transfer::class => Factory::class,
	];

	/**
	 * Just for correct auto complete
	 * @inheritdoc
	 * @return ProducerInterface
	 */
	public function get($name, array $options = null)
	{
		return parent::get($name, $options);
	}


}