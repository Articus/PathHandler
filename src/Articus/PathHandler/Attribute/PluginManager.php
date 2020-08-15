<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute;

use Laminas\ServiceManager\AbstractPluginManager;

/**
 * Attribute plugin manager
 */
class PluginManager extends AbstractPluginManager
{
	protected $instanceOf = AttributeInterface::class;

	protected $factories = [
		Transfer::class => Factory\Transfer::class,
	];

	protected $aliases = [
		'Transfer' => Transfer::class,
		'transfer' => Transfer::class,
	];
}