<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\MetadataProvider\Factory;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;
use Psr\SimpleCache\CacheInterface;

/**
 * TODO add expected text for LogicExceptions
 */
class PhpAttributeSpec extends ObjectBehavior
{
	public function let()
	{
		if (\PHP_MAJOR_VERSION < 8)
		{
			throw new SkippingException('PHP 8+ is required');
		}
	}

	public function it_gets_configuration_from_default_config_key(
		ContainerInterface $container, PH\Handler\PluginManager $handlerManager, \ArrayAccess $config
	)
	{
		$container->get(PH\Handler\PluginManager::class)->shouldBeCalledOnce()->willReturn($handlerManager);

		$configKey = PH\MetadataProvider\PhpAttribute::class;
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce();

		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(PH\MetadataProvider\PhpAttribute::class);
	}

	public function it_gets_configuration_from_custom_config_key(
		ContainerInterface $container, PH\Handler\PluginManager $handlerManager, \ArrayAccess $config
	)
	{
		$container->get(PH\Handler\PluginManager::class)->shouldBeCalledOnce()->willReturn($handlerManager);

		$configKey = 'test_config_key';
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce();

		$this->beConstructedWith($configKey);
		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(PH\MetadataProvider\PhpAttribute::class);
	}

	public function it_constructs_itself_and_gets_configuration_from_custom_config_key(
		ContainerInterface $container, PH\Handler\PluginManager $handlerManager, \ArrayAccess $config
	)
	{
		$container->get(PH\Handler\PluginManager::class)->shouldBeCalledOnce()->willReturn($handlerManager);

		$configKey = 'test_config_key';
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce();

		$service = $this::__callStatic($configKey, [$container, '', null]);
		$service->shouldBeAnInstanceOf(PH\MetadataProvider\PhpAttribute::class);
	}

	public function it_throws_on_too_few_arguments_during_self_construct(ContainerInterface $container)
	{
		$configKey = 'test_config_key';
		$error = new \InvalidArgumentException(\sprintf(
			'To invoke %s with custom configuration key statically 3 arguments are required: container, service name and options.',
			PH\MetadataProvider\Factory\PhpAttribute::class
		));

		$this::shouldThrow($error)->during('__callStatic', [$configKey, []]);
		$this::shouldThrow($error)->during('__callStatic', [$configKey, [$container]]);
		$this::shouldThrow($error)->during('__callStatic', [$configKey, [$container, '']]);
	}

	public function it_creates_service_with_empty_configuration(ContainerInterface $container, PH\Handler\PluginManager $handlerManager)
	{
		$container->get(PH\Handler\PluginManager::class)->shouldBeCalledOnce()->willReturn($handlerManager);

		$container->get('config')->shouldBeCalledOnce()->willReturn([]);

		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(PH\MetadataProvider\PhpAttribute::class);
		$service->shouldHavePropertyOfType('cache', PH\Cache\DataFilePerKey::class);
	}

	public function it_creates_service_with_cache_configuration(ContainerInterface $container, PH\Handler\PluginManager $handlerManager)
	{
		$container->get(PH\Handler\PluginManager::class)->shouldBeCalledOnce()->willReturn($handlerManager);

		$config = [
			PH\MetadataProvider\Annotation::class => ['cache' => ['directory' => 'data/cache']]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);

		$service = $this->__invoke($container, '');
		$service->shouldBeAnInstanceOf(PH\MetadataProvider\PhpAttribute::class);
		$service->shouldHavePropertyOfType('cache', PH\Cache\DataFilePerKey::class);
	}

	public function it_creates_service_with_cache_from_container(
		ContainerInterface $container, PH\Handler\PluginManager $handlerManager, CacheInterface $cache
	)
	{
		$container->get(PH\Handler\PluginManager::class)->shouldBeCalledOnce()->willReturn($handlerManager);

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
		ContainerInterface $container, PH\Handler\PluginManager $handlerManager, $cache
	)
	{
		$container->get(PH\Handler\PluginManager::class)->shouldBeCalledOnce()->willReturn($handlerManager);

		$cacheServiceName = 'test_cache_service';
		$config = [
			PH\MetadataProvider\PhpAttribute::class => ['cache' => $cacheServiceName]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->has($cacheServiceName)->shouldBeCalledOnce()->willReturn(true);
		$container->get($cacheServiceName)->shouldBeCalledOnce()->willReturn($cache);

		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, '']);
	}

	public function it_throws_on_invalid_cache_configuration(ContainerInterface $container, PH\Handler\PluginManager $handlerManager)
	{
		$container->get(PH\Handler\PluginManager::class)->shouldBeCalledOnce()->willReturn($handlerManager);

		$config = [
			PH\MetadataProvider\PhpAttribute::class => ['cache' => new \stdClass()]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);

		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, '']);
	}
}
