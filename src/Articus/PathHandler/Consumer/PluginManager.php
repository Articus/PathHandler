<?php
declare(strict_types=1);

namespace Articus\PathHandler\Consumer;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * Consumer plugin manager
 */
class PluginManager extends AbstractPluginManager
{
	protected $instanceOf = ConsumerInterface::class;

	protected $factories = [
		Json::class => InvokableFactory::class,
		Internal::class => InvokableFactory::class,
	];

	protected $aliases = [
		'Json' => Json::class,
		'Internal' => Internal::class,
		'json' => Json::class,
		'internal' => Internal::class,
	];
}