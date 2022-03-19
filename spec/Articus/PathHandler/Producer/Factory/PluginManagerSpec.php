<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer\Factory;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;

/**
 * TODO add expected text for LogicExceptions
 */
class PluginManagerSpec extends ObjectBehavior
{
	public function it_gets_configuration_from_default_config_key(ContainerInterface $container, \ArrayAccess $config)
	{
		$configKey = PH\Producer\PluginManager::class;
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce()->willReturn(null);

		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(PH\Producer\PluginManager::class);
	}

	public function it_gets_configuration_from_custom_config_key(ContainerInterface $container, \ArrayAccess $config)
	{
		$configKey = 'test_config_key';
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($configKey);
		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(PH\Producer\PluginManager::class);
	}

	public function it_constructs_itself_and_gets_configuration_from_custom_config_key(ContainerInterface $container, \ArrayAccess $config)
	{
		$configKey = 'test_config_key';
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce()->willReturn(null);

		$service = $this::__callStatic($configKey, [$container, '', null]);
		$service->shouldBeAnInstanceOf(PH\Producer\PluginManager::class);
	}

	public function it_throws_on_too_few_arguments_during_self_construct(ContainerInterface $container)
	{
		$configKey = 'test_config_key';
		$error = new \InvalidArgumentException(\sprintf(
			'To invoke %s with custom configuration key statically 3 arguments are required: container, service name and options.',
			PH\Producer\Factory\PluginManager::class
		));

		$this::shouldThrow($error)->during('__callStatic', [$configKey, []]);
		$this::shouldThrow($error)->during('__callStatic', [$configKey, [$container]]);
		$this::shouldThrow($error)->during('__callStatic', [$configKey, [$container, '']]);
	}
}
