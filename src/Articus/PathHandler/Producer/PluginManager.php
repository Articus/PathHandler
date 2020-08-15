<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer;

use Laminas\ServiceManager\AbstractPluginManager;

/**
 * Producer plugin manager
 */
class PluginManager extends AbstractPluginManager
{
	protected $instanceOf = ProducerInterface::class;

	protected $factories = [
		Json::class => Factory\Json::class,
		Template::class => Factory\Template::class,
		Transfer::class => Factory\Transfer::class,
	];

	protected $aliases = [
		'Json' => Json::class,
		'Template' => Template::class,
		'Transfer' => Transfer::class,
		'json' => Json::class,
		'template' => Template::class,
		'transfer' => Transfer::class,
	];
}