<?php
declare(strict_types=1);

namespace Articus\PathHandler\Consumer\Factory;

use Articus\PathHandler\Consumer;
use Articus\PathHandler\RouteInjectionFactory;
use Articus\PluginManager as PM;
use Psr\Container\ContainerInterface;

class PluginManager extends PM\Factory\Simple
{
	public function __construct(string $configKey = RouteInjectionFactory::DEFAULT_CONSUMER_PLUGIN_MANAGER)
	{
		parent::__construct($configKey);
	}

	protected function getServiceConfig(ContainerInterface $container): array
	{
		$defaultConfig = [
			'invokables' => [
				Consumer\Internal::class => Consumer\Internal::class,
			],
			'factories' => [
				Consumer\Json::class => Json::class,
			],
			'aliases' => [
				'Internal' => Consumer\Internal::class,
				'internal' => Consumer\Internal::class,
				'Json' => Consumer\Json::class,
				'json' => Consumer\Json::class,
			],
			'shares' => [
				Consumer\Internal::class => true,
				Consumer\Json::class => true,
			],
		];

		return array_merge_recursive($defaultConfig, parent::getServiceConfig($container));
	}
}
