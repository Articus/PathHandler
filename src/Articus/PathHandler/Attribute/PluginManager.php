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
		IdentifiableValueLoad::class => Factory\IdentifiableValueLoad::class,
		IdentifiableValueListLoad::class => Factory\IdentifiableValueListLoad::class,
		Transfer::class => Factory\Transfer::class,
	];

	protected $aliases = [
		'IdentifiableValueLoad' => IdentifiableValueLoad::class,
		'identifiableValueLoad' => IdentifiableValueLoad::class,
		'IdentifiableValueListLoad' => IdentifiableValueListLoad::class,
		'identifiableValueListLoad' => IdentifiableValueListLoad::class,
		'LoadById' => IdentifiableValueLoad::class,
		'loadById' => IdentifiableValueLoad::class,
		'LoadByIds' => IdentifiableValueListLoad::class,
		'loadByIds' => IdentifiableValueListLoad::class,
		'Transfer' => Transfer::class,
		'transfer' => Transfer::class,
	];
}