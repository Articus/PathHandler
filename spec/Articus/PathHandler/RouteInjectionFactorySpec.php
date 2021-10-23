<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use Mezzio\Router\Route;
use Mezzio\Router\RouterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\SimpleCache\CacheInterface;

class RouteInjectionFactorySpec extends ObjectBehavior
{
	public function it_returns_router_with_empty_config(
		ContainerInterface $container,
		PH\MetadataProviderInterface $metadataProvider,
		PH\Handler\PluginManager $handlerManager,
		PH\Consumer\PluginManager $consumerManager,
		PH\Attribute\PluginManager $attributeManager,
		PH\Producer\PluginManager $producerManager,
		Response $response
	)
	{
		$config = [
			PH\RouteInjectionFactory::class => []
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->get(PH\MetadataProviderInterface::class)->shouldBeCalledOnce()->willReturn($metadataProvider);
		$container->get(PH\Handler\PluginManager::class)->shouldBeCalledOnce()->willReturn($handlerManager);
		$container->get(PH\Consumer\PluginManager::class)->shouldBeCalledOnce()->willReturn($consumerManager);
		$container->get(PH\Attribute\PluginManager::class)->shouldBeCalledOnce()->willReturn($attributeManager);
		$container->get(PH\Producer\PluginManager::class)->shouldBeCalledOnce()->willReturn($producerManager);
		$responseGenerator = function () use ($response)
		{
			return $response;
		};
		$container->get(Response::class)->shouldBeCalledOnce()->willReturn($responseGenerator);

		$this->__invoke($container, 'router')->shouldBeAnInstanceOf(PH\Router\FastRoute::class);
	}

	public function it_returns_router_using_custom_default_producer(
		ContainerInterface $container,
		PH\MetadataProviderInterface $metadataProvider,
		PH\Handler\PluginManager $handlerManager,
		PH\Consumer\PluginManager $consumerManager,
		PH\Attribute\PluginManager $attributeManager,
		PH\Producer\PluginManager $producerManager,
		Response $response
	)
	{
		$config = [
			PH\RouteInjectionFactory::class => [
				'default_producer' => [
					'media_type' => 'test/mime',
					'name' => 'test_producer',
					'options' => ['test_option' => 123]
				],
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->get(PH\MetadataProviderInterface::class)->shouldBeCalledOnce()->willReturn($metadataProvider);
		$container->get(PH\Handler\PluginManager::class)->shouldBeCalledOnce()->willReturn($handlerManager);
		$container->get(PH\Consumer\PluginManager::class)->shouldBeCalledOnce()->willReturn($consumerManager);
		$container->get(PH\Attribute\PluginManager::class)->shouldBeCalledOnce()->willReturn($attributeManager);
		$container->get(PH\Producer\PluginManager::class)->shouldBeCalledOnce()->willReturn($producerManager);
		$responseGenerator = function () use ($response)
		{
			return $response;
		};
		$container->get(Response::class)->shouldBeCalledOnce()->willReturn($responseGenerator);

		$this->__invoke($container, 'router')->shouldBeAnInstanceOf(PH\Router\FastRoute::class);
		//TODO check that middleware received provided default producer
	}

	public function it_returns_router_with_simple_config_using_external_cache_service(
		ContainerInterface $container,
		PH\MetadataProviderInterface $metadataProvider,
		PH\Handler\PluginManager $handlerManager,
		PH\Consumer\PluginManager $consumerManager,
		PH\Attribute\PluginManager $attributeManager,
		PH\Producer\PluginManager $producerManager,
		Response $response,
		CacheInterface $routerCache
	)
	{
		$routerCacheServiceKey = 'router_cache_service';
		$config = [
			PH\RouteInjectionFactory::class => [
				'router' => [
					'cache' => $routerCacheServiceKey,
				],
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->get(PH\MetadataProviderInterface::class)->shouldBeCalledOnce()->willReturn($metadataProvider);
		$container->get(PH\Handler\PluginManager::class)->shouldBeCalledOnce()->willReturn($handlerManager);
		$container->get(PH\Consumer\PluginManager::class)->shouldBeCalledOnce()->willReturn($consumerManager);
		$container->get(PH\Attribute\PluginManager::class)->shouldBeCalledOnce()->willReturn($attributeManager);
		$container->get(PH\Producer\PluginManager::class)->shouldBeCalledOnce()->willReturn($producerManager);
		$responseGenerator = function () use ($response)
		{
			return $response;
		};
		$container->get(Response::class)->shouldBeCalledOnce()->willReturn($responseGenerator);
		$container->has($routerCacheServiceKey)->shouldBeCalledOnce()->willReturn(true);
		$container->get($routerCacheServiceKey)->shouldBeCalledOnce()->willReturn($routerCache);

		$this->__invoke($container, 'router')->shouldBeAnInstanceOf(PH\Router\FastRoute::class);
		//TODO check that router and metadata provider use provided cache services
	}

	public function it_returns_router_using_external_services(
		ContainerInterface $container,
		RouterInterface $router,
		PH\MetadataProviderInterface $metadataProvider,
		PH\Handler\PluginManager $handlerManager,
		PH\Consumer\PluginManager $consumerManager,
		PH\Attribute\PluginManager $attributeManager,
		PH\Producer\PluginManager $producerManager,
		Response $response
	)
	{
		$routerKey = 'router_service';

		$handleNames = ['test', 'test_1', 'test_2', 'test_3'];
		$httpMethods = [
			$handleNames[0] => ['GET', 'HEAD'],
			$handleNames[1] => ['POST'],
			$handleNames[2] => ['PUT', 'PATCH'],
			$handleNames[3] => ['DELETE'],
		];
		$routes = [
			$handleNames[0] => [
				[null, '/test/1', []],
				['0_2', '/test/2', ['test' => 123]],
			],
			$handleNames[1] => [
				['1_1', '/test_1/1', []],
				[null, '/test_1/2', ['test_1' => 123]],
			],
			$handleNames[2] => [
				['2', '/test_2', []],
			],
			$handleNames[3] => [
				[null, '/test_3', []],
			],
		];
		$paths = [
			'' => [$handleNames[0], $handleNames[1]],
			'/1' => [$handleNames[0], $handleNames[2]],
			'/2' => [$handleNames[3]],
		];
		$routeGenerator = function (array $args, $object, $method) use ($routes)
		{
			[$handlerName] = $args;
			yield from $routes[$handlerName];
		};
		$routeChecker = function (Route $routeObject) use ($paths, $httpMethods, $routes)
		{
			$valid = function () use ($paths, $httpMethods, $routes)
			{
				foreach ($paths as $prefix => $handlerNames)
				{
					foreach ($handlerNames as $handlerName)
					{
						foreach ($routes[$handlerName] as [$name, $pattern, $defaults])
						{
							$path = $prefix . $pattern;
							$allowedMethods = $httpMethods[$handlerName];
							$name = $name ?? ($path . '^' . \implode(':', $allowedMethods));
							$options = empty($defaults) ? [] : ['defaults' => $defaults];
							yield [$name, $path, $allowedMethods, $options];
						}
					}
				}
			};
			$result = false;
			foreach ($valid() as [$name, $path, $allowedMethods, $options])
			{
				$result = ($result
					|| (($routeObject->getName() === $name)
						&& ($routeObject->getPath() === $path)
						&& ($routeObject->getAllowedMethods() === $allowedMethods)
						&& ($routeObject->getOptions() === $options)
					)
				);
			}
			return $result;
		};
		$responseGenerator = function () use ($response)
		{
			return $response;
		};

		$config = [
			PH\RouteInjectionFactory::class => [
				'router' => $routerKey,
				'paths' => $paths,
			]
		];

		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->has($routerKey)->shouldBeCalledOnce()->willReturn(true);
		$container->get($routerKey)->shouldBeCalledOnce()->willReturn($router);


		$container->get(PH\MetadataProviderInterface::class)->shouldBeCalledOnce()->willReturn($metadataProvider);
		$container->get(PH\Handler\PluginManager::class)->shouldBeCalledOnce()->willReturn($handlerManager);
		$container->get(PH\Consumer\PluginManager::class)->shouldBeCalledOnce()->willReturn($consumerManager);
		$container->get(PH\Attribute\PluginManager::class)->shouldBeCalledOnce()->willReturn($attributeManager);
		$container->get(PH\Producer\PluginManager::class)->shouldBeCalledOnce()->willReturn($producerManager);
		$container->get(Response::class)->shouldBeCalledOnce()->willReturn($responseGenerator);

		$metadataProvider->getHttpMethods($handleNames[0])->shouldBeCalledTimes(2)->willReturn($httpMethods[$handleNames[0]]);
		$metadataProvider->getHttpMethods($handleNames[1])->shouldBeCalledOnce()->willReturn($httpMethods[$handleNames[1]]);
		$metadataProvider->getHttpMethods($handleNames[2])->shouldBeCalledOnce()->willReturn($httpMethods[$handleNames[2]]);
		$metadataProvider->getHttpMethods($handleNames[3])->shouldBeCalledOnce()->willReturn($httpMethods[$handleNames[3]]);

		$metadataProvider->getRoutes($handleNames[0])->shouldBeCalledTimes(2)->will($routeGenerator);
		$metadataProvider->getRoutes($handleNames[1])->shouldBeCalledOnce()->will($routeGenerator);
		$metadataProvider->getRoutes($handleNames[2])->shouldBeCalledOnce()->will($routeGenerator);
		$metadataProvider->getRoutes($handleNames[3])->shouldBeCalledOnce()->will($routeGenerator);

		$router->addRoute(Argument::that($routeChecker))->shouldBeCalledTimes(8);

		$this->__invoke($container, 'router')->shouldBe($router);
	}

	
	public function it_throws_on_empty_router_config(ContainerInterface $container)
	{
		$config = [
			PH\RouteInjectionFactory::class => [
				'router' => [],
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}

	public function it_throws_on_invalid_router_cache(ContainerInterface $container, $routerCache)
	{
		$routerCacheKey = 'invalid_cache';
		$config = [
			PH\RouteInjectionFactory::class => [
				'router' => [
					'cache' => $routerCacheKey,
				],
			],
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->has($routerCacheKey)->shouldBeCalledOnce()->willReturn(true);
		$container->get($routerCacheKey)->shouldBeCalledOnce()->willReturn($routerCache);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}

	public function it_throws_on_invalid_router_cache_config(ContainerInterface $container)
	{
		$config = [
			PH\RouteInjectionFactory::class => [
				'router' => [
					'cache' => 123,
				],
			],
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}

	public function it_throws_on_invalid_router(ContainerInterface $container, $router)
	{
		$routerKey = 'invalid_router';
		$config = [
			PH\RouteInjectionFactory::class => [
				'router' => $routerKey,
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->has($routerKey)->shouldBeCalledOnce()->willReturn(true);
		$container->get($routerKey)->shouldBeCalledOnce()->willReturn($router);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}

	public function it_throws_on_invalid_router_config(ContainerInterface $container)
	{
		$config = [
			PH\RouteInjectionFactory::class => [
				'router' => 123,
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}

	public function it_gets_configuration_from_custom_config_key(
		ContainerInterface $container,
		PH\MetadataProviderInterface $metadataProvider,
		PH\Handler\PluginManager $handlerManager,
		PH\Consumer\PluginManager $consumerManager,
		PH\Attribute\PluginManager $attributeManager,
		PH\Producer\PluginManager $producerManager,
		Response $response,
		\ArrayAccess $config
	)
	{
		$responseGenerator = function () use ($response)
		{
			return $response;
		};

		$configKey = 'test_config_key';
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce()->willReturn([]);
		$container->get(PH\MetadataProviderInterface::class)->shouldBeCalledOnce()->willReturn($metadataProvider);
		$container->get(PH\Handler\PluginManager::class)->shouldBeCalledOnce()->willReturn($handlerManager);
		$container->get(PH\Consumer\PluginManager::class)->shouldBeCalledOnce()->willReturn($consumerManager);
		$container->get(PH\Attribute\PluginManager::class)->shouldBeCalledOnce()->willReturn($attributeManager);
		$container->get(PH\Producer\PluginManager::class)->shouldBeCalledOnce()->willReturn($producerManager);
		$container->get(Response::class)->shouldBeCalledOnce()->willReturn($responseGenerator);

		$this->beConstructedWith($configKey);
		$this->__invoke($container, 'router')->shouldBeAnInstanceOf(PH\Router\FastRoute::class);
	}

	public function it_constructs_itself_and_gets_configuration_from_custom_config_key(
		ContainerInterface $container,
		PH\MetadataProviderInterface $metadataProvider,
		PH\Handler\PluginManager $handlerManager,
		PH\Consumer\PluginManager $consumerManager,
		PH\Attribute\PluginManager $attributeManager,
		PH\Producer\PluginManager $producerManager,
		Response $response,
		\ArrayAccess $config
	)
	{
		$responseGenerator = function () use ($response)
		{
			return $response;
		};

		$configKey = 'test_config_key';
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$config->offsetExists($configKey)->shouldBeCalledOnce()->willReturn(true);
		$config->offsetGet($configKey)->shouldBeCalledOnce()->willReturn([]);
		$container->get(PH\MetadataProviderInterface::class)->shouldBeCalledOnce()->willReturn($metadataProvider);
		$container->get(PH\Handler\PluginManager::class)->shouldBeCalledOnce()->willReturn($handlerManager);
		$container->get(PH\Consumer\PluginManager::class)->shouldBeCalledOnce()->willReturn($consumerManager);
		$container->get(PH\Attribute\PluginManager::class)->shouldBeCalledOnce()->willReturn($attributeManager);
		$container->get(PH\Producer\PluginManager::class)->shouldBeCalledOnce()->willReturn($producerManager);
		$container->get(Response::class)->shouldBeCalledOnce()->willReturn($responseGenerator);

		$this::__callStatic($configKey, [$container, 'router', null])->shouldBeAnInstanceOf(PH\Router\FastRoute::class);
	}

	public function it_throws_on_too_few_arguments_during_self_construct(ContainerInterface $container)
	{
		$configKey = 'test_config_key';
		$error = new \InvalidArgumentException(\sprintf(
			'To invoke %s with custom configuration key statically 3 arguments are required: container, service name and options.',
			PH\RouteInjectionFactory::class
		));

		$this::shouldThrow($error)->during('__callStatic', [$configKey, []]);
		$this::shouldThrow($error)->during('__callStatic', [$configKey, [$container]]);
		$this::shouldThrow($error)->during('__callStatic', [$configKey, [$container, 'router']]);
	}

}
