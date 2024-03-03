<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer\Factory;

use Articus\PathHandler\Producer;
use Articus\PathHandler\RouteInjectionFactory;
use Articus\PluginManager as PM;
use Psr\Container\ContainerInterface;

class PluginManager extends PM\Factory\Simple
{
	public function __construct(string $configKey = RouteInjectionFactory::DEFAULT_PRODUCER_PLUGIN_MANAGER)
	{
		parent::__construct($configKey);
	}

	protected function getServiceConfig(ContainerInterface $container): array
	{
		$defaultConfig = [
			'factories' => [
				Producer\Json::class => Json::class,
				Producer\Template::class => Template::class,
				Producer\Text::class => Text::class,
				Producer\Transfer::class => Transfer::class,
			],
			'aliases' => [
				'Json' => Producer\Json::class,
				'json' => Producer\Json::class,
				'Template' => Producer\Template::class,
				'template' => Producer\Template::class,
				'Text' => Producer\Text::class,
				'text' => Producer\Text::class,
				'Transfer' => Producer\Transfer::class,
				'transfer' => Producer\Transfer::class,
			],
			'shares' => [
				Producer\Json::class => true,
				Producer\Template::class => true,
				Producer\Text::class => true,
				Producer\Transfer::class => true,
			],
		];

		return array_merge_recursive($defaultConfig, parent::getServiceConfig($container));
	}
}
