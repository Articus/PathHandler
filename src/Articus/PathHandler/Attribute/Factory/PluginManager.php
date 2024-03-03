<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute\Factory;

use Articus\PathHandler\Attribute;
use Articus\PathHandler\RouteInjectionFactory;
use Articus\PluginManager as PM;
use Psr\Container\ContainerInterface;

class PluginManager extends PM\Factory\Simple
{
	public function __construct(string $configKey = RouteInjectionFactory::DEFAULT_ATTRIBUTE_PLUGIN_MANAGER)
	{
		parent::__construct($configKey);
	}

	protected function getServiceConfig(ContainerInterface $container): array
	{
		$defaultConfig = [
			'factories' => [
				Attribute\IdentifiableValueLoad::class => IdentifiableValueLoad::class,
				Attribute\IdentifiableValueListLoad::class => IdentifiableValueListLoad::class,
				Attribute\Transfer::class => Transfer::class,
			],
			'aliases' => [
				'IdentifiableValueLoad' => Attribute\IdentifiableValueLoad::class,
				'identifiableValueLoad' => Attribute\IdentifiableValueLoad::class,
				'IdentifiableValueListLoad' => Attribute\IdentifiableValueListLoad::class,
				'identifiableValueListLoad' => Attribute\IdentifiableValueListLoad::class,
				'LoadById' => Attribute\IdentifiableValueLoad::class,
				'loadById' => Attribute\IdentifiableValueLoad::class,
				'LoadByIds' => Attribute\IdentifiableValueListLoad::class,
				'loadByIds' => Attribute\IdentifiableValueListLoad::class,
				'Transfer' => Attribute\Transfer::class,
				'transfer' => Attribute\Transfer::class,
			],
			'shares' => [
				Attribute\IdentifiableValueLoad::class => true,
				Attribute\IdentifiableValueListLoad::class => true,
				Attribute\Transfer::class => true,
			],
		];

		return array_merge_recursive($defaultConfig, parent::getServiceConfig($container));
	}
}
