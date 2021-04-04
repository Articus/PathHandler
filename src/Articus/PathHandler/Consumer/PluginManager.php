<?php
declare(strict_types=1);

namespace Articus\PathHandler\Consumer;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Factory\InvokableFactory;

/**
 * Consumer plugin manager
 */
class PluginManager extends AbstractPluginManager
{
	protected $instanceOf = ConsumerInterface::class;

	protected $factories = [
		Json::class => Factory\Json::class,
		Internal::class => InvokableFactory::class,
	];

	protected $aliases = [
		'Json' => Json::class,
		'Internal' => Internal::class,
		'json' => Json::class,
		'internal' => Internal::class,
	];
}