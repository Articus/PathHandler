<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer\Factory;

use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler\Producer;
use Articus\PathHandler\RouteInjectionFactory;
use Articus\PluginManager\PluginFactoryInterface;
use Articus\PluginManager\PluginManagerInterface;
use Psr\Container\ContainerInterface;

class Transfer implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Producer\Transfer
	{
		$parsedOptions = new Producer\Options\Transfer($options);
		return new Producer\Transfer(
			$this->getProducerManager($container)($parsedOptions->producerName, $parsedOptions->producerOptions),
			$container->get(DTService::class),
			$parsedOptions->subset
		);
	}

	protected function getProducerManager(ContainerInterface $container): PluginManagerInterface
	{
		return $container->get(RouteInjectionFactory::DEFAULT_PRODUCER_PLUGIN_MANAGER);
	}
}
