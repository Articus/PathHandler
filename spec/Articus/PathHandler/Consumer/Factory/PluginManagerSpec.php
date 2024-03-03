<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Consumer\Factory;

use ArrayAccess;
use Articus\PathHandler as PH;
use Articus\PluginManager as PM;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;

class PluginManagerSpec extends ObjectBehavior
{
	public function it_gets_configuration_from_default_config_key(ContainerInterface $container, ArrayAccess $config)
	{
		$configKey = PH\RouteInjectionFactory::DEFAULT_CONSUMER_PLUGIN_MANAGER;
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce()->willReturn(null);

		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(PM\Simple::class);
	}

	public function it_gets_configuration_from_custom_config_key(ContainerInterface $container, ArrayAccess $config)
	{
		$configKey = 'test_config_key';
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($configKey);
		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(PM\Simple::class);
	}
}
