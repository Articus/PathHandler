<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\MetadataProvider\Factory;

use ArrayAccess;
use Articus\PathHandler as PH;
use Articus\PluginManager as PM;
use LogicException;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use stdClass;

/**
 * TODO add expected text for LogicExceptions
 */
class PhpAttributeSpec extends ObjectBehavior
{
	public function it_gets_configuration_from_default_config_key(
		ContainerInterface $container, PM\PluginManagerInterface $handlerManager, ArrayAccess $config
	)
	{
		$container->get(PH\RouteInjectionFactory::DEFAULT_HANDLER_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($handlerManager);

		$configKey = PH\MetadataProvider\PhpAttribute::class;
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce()->willReturn(null);

		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(PH\MetadataProvider\PhpAttribute::class);
	}

	public function it_gets_configuration_from_custom_config_key(
		ContainerInterface $container, PM\PluginManagerInterface $handlerManager, ArrayAccess $config
	)
	{
		$container->get(PH\RouteInjectionFactory::DEFAULT_HANDLER_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($handlerManager);

		$configKey = 'test_config_key';
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($configKey);
		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(PH\MetadataProvider\PhpAttribute::class);
	}

	public function it_creates_service_with_empty_configuration(ContainerInterface $container, PM\PluginManagerInterface $handlerManager)
	{
		$container->get(PH\RouteInjectionFactory::DEFAULT_HANDLER_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($handlerManager);

		$container->get('config')->shouldBeCalledOnce()->willReturn([]);

		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(PH\MetadataProvider\PhpAttribute::class);
		$service->shouldHavePropertyOfType('cache', PH\Cache\DataFilePerKey::class);
	}

	public function it_creates_service_with_cache_configuration(ContainerInterface $container, PM\PluginManagerInterface $handlerManager)
	{
		$container->get(PH\RouteInjectionFactory::DEFAULT_HANDLER_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($handlerManager);

		$config = [
			PH\MetadataProvider\PhpAttribute::class => ['cache' => ['directory' => 'data/cache']]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);

		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(PH\MetadataProvider\PhpAttribute::class);
		$service->shouldHavePropertyOfType('cache', PH\Cache\DataFilePerKey::class);
	}

	public function it_creates_service_with_cache_from_container(
		ContainerInterface $container, PM\PluginManagerInterface $handlerManager, CacheInterface $cache
	)
	{
		$container->get(PH\RouteInjectionFactory::DEFAULT_HANDLER_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($handlerManager);

		$cacheServiceName = 'test_cache_service';
		$config = [
			PH\MetadataProvider\PhpAttribute::class => ['cache' => $cacheServiceName]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->has($cacheServiceName)->shouldBeCalledOnce()->willReturn(true);
		$container->get($cacheServiceName)->shouldBeCalledOnce()->willReturn($cache);

		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(PH\MetadataProvider\PhpAttribute::class);
		$service->shouldHaveProperty('cache', $cache);
	}

	public function it_throws_on_invalid_cache_service_in_container(
		ContainerInterface $container, PM\PluginManagerInterface $handlerManager, $cache
	)
	{
		$container->get(PH\RouteInjectionFactory::DEFAULT_HANDLER_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($handlerManager);

		$cacheServiceName = 'test_cache_service';
		$config = [
			PH\MetadataProvider\PhpAttribute::class => ['cache' => $cacheServiceName]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->has($cacheServiceName)->shouldBeCalledOnce()->willReturn(true);
		$container->get($cacheServiceName)->shouldBeCalledOnce()->willReturn($cache);

		$this->shouldThrow(LogicException::class)->during('__invoke', [$container, '']);
	}

	public function it_throws_on_invalid_cache_configuration(ContainerInterface $container, PM\PluginManagerInterface $handlerManager)
	{
		$container->get(PH\RouteInjectionFactory::DEFAULT_HANDLER_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($handlerManager);

		$config = [
			PH\MetadataProvider\PhpAttribute::class => ['cache' => new stdClass()]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);

		$this->shouldThrow(LogicException::class)->during('__invoke', [$container, '']);
	}
}
