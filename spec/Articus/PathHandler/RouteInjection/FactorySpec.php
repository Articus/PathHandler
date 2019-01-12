<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\RouteInjection;

use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;
use Interop\Container\ContainerInterface;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface as Response;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouterInterface;
use Zend\ServiceManager\PluginManagerInterface;

class FactorySpec extends ObjectBehavior
{
	public function let()
	{
		$this->shouldImplement(PH\ConfigAwareFactory::class);
	}

	public function it_returns_router_with_simple_config(ContainerInterface $container, Response $response)
	{
		$config = [
			PH\RouteInjection\Factory::class => []
		];
		$responseGenerator = function () use ($response)
		{
			return $response;
		};
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->get(Response::class)->shouldBeCalledOnce()->willReturn($responseGenerator);

		$this->__invoke($container, 'router')->shouldBeAnInstanceOf(PH\Router\FastRoute::class);
	}

	public function it_returns_router_using_external_services(
		ContainerInterface $container,
		RouterInterface $router,
		PH\MetadataProviderInterface $metadata,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Response $response
	)
	{
		$routerKey = 'router_service';
		$metadataKey = 'metadata_service';
		$handlerManagerKey = 'handler_manager_service';
		$consumerManagerKey = 'consumer_manager_service';
		$attributeManagerKey = 'attribute_manager_service';
		$producerManagerKey = 'producer_manager_service';

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
			PH\RouteInjection\Factory::class => [
				'router' => $routerKey,
				'metadata' => $metadataKey,
				'paths' => $paths,
				'handlers' => $handlerManagerKey,
				'consumers' => $consumerManagerKey,
				'attributes' => $attributeManagerKey,
				'producers' => $producerManagerKey,
			]
		];

		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->has($routerKey)->shouldBeCalledOnce()->willReturn(true);
		$container->get($routerKey)->shouldBeCalledOnce()->willReturn($router);
		$container->has($metadataKey)->shouldBeCalledOnce()->willReturn(true);
		$container->get($metadataKey)->shouldBeCalledOnce()->willReturn($metadata);
		$container->has($handlerManagerKey)->shouldBeCalledOnce()->willReturn(true);
		$container->get($handlerManagerKey)->shouldBeCalledOnce()->willReturn($handlerManager);
		$container->has($consumerManagerKey)->shouldBeCalledOnce()->willReturn(true);
		$container->get($consumerManagerKey)->shouldBeCalledOnce()->willReturn($consumerManager);
		$container->has($attributeManagerKey)->shouldBeCalledOnce()->willReturn(true);
		$container->get($attributeManagerKey)->shouldBeCalledOnce()->willReturn($attributeManager);
		$container->has($producerManagerKey)->shouldBeCalledOnce()->willReturn(true);
		$container->get($producerManagerKey)->shouldBeCalledOnce()->willReturn($producerManager);
		$container->get(Response::class)->shouldBeCalledOnce()->willReturn($responseGenerator);

		$metadata->getHttpMethods($handleNames[0])->shouldBeCalledTimes(2)->willReturn($httpMethods[$handleNames[0]]);
		$metadata->getHttpMethods($handleNames[1])->shouldBeCalledOnce()->willReturn($httpMethods[$handleNames[1]]);
		$metadata->getHttpMethods($handleNames[2])->shouldBeCalledOnce()->willReturn($httpMethods[$handleNames[2]]);
		$metadata->getHttpMethods($handleNames[3])->shouldBeCalledOnce()->willReturn($httpMethods[$handleNames[3]]);

		$metadata->getRoutes($handleNames[0])->shouldBeCalledTimes(2)->will($routeGenerator);
		$metadata->getRoutes($handleNames[1])->shouldBeCalledOnce()->will($routeGenerator);
		$metadata->getRoutes($handleNames[2])->shouldBeCalledOnce()->will($routeGenerator);
		$metadata->getRoutes($handleNames[3])->shouldBeCalledOnce()->will($routeGenerator);

		$router->addRoute(Argument::that($routeChecker))->shouldBeCalledTimes(8);

		$this->__invoke($container, 'router')->shouldBe($router);
	}

	
	public function it_throws_on_empty_router_config(ContainerInterface $container)
	{
		$config = [
			PH\RouteInjection\Factory::class => [
				'router' => [],
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}

	public function it_throws_on_invalid_router(ContainerInterface $container, $router)
	{
		$routerKey = 'invalid_router';
		$config = [
			PH\RouteInjection\Factory::class => [
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
			PH\RouteInjection\Factory::class => [
				'router' => 123,
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}


	public function it_throws_on_empty_metadata_provider_config(ContainerInterface $container)
	{
		$config = [
			PH\RouteInjection\Factory::class => [
				'metadata' => [],
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}

	public function it_throws_on_invalid_metadata_provider(ContainerInterface $container, $metadata)
	{
		$metadataKey = 'invalid_metadata';
		$config = [
			PH\RouteInjection\Factory::class => [
				'metadata' => $metadataKey,
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->has($metadataKey)->shouldBeCalledOnce()->willReturn(true);
		$container->get($metadataKey)->shouldBeCalledOnce()->willReturn($metadata);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}

	public function it_throws_on_invalid_metadata_provider_config(ContainerInterface $container)
	{
		$config = [
			PH\RouteInjection\Factory::class => [
				'metadata' => 123,
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}


	public function it_throws_on_invalid_handler_manager(ContainerInterface $container, $handlerManager)
	{
		$key = 'invalid';
		$config = [
			PH\RouteInjection\Factory::class => [
				'handlers' => $key,
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->has($key)->shouldBeCalledOnce()->willReturn(true);
		$container->get($key)->shouldBeCalledOnce()->willReturn($handlerManager);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}

	public function it_throws_on_invalid_handler_manager_config(ContainerInterface $container)
	{
		$config = [
			PH\RouteInjection\Factory::class => [
				'handlers' => 123,
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}
	

	public function it_throws_on_invalid_consumer_manager(ContainerInterface $container, $consumerManager)
	{
		$key = 'invalid';
		$config = [
			PH\RouteInjection\Factory::class => [
				'consumers' => $key,
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->has($key)->shouldBeCalledOnce()->willReturn(true);
		$container->get($key)->shouldBeCalledOnce()->willReturn($consumerManager);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}

	public function it_throws_on_invalid_consumer_manager_config(ContainerInterface $container)
	{
		$config = [
			PH\RouteInjection\Factory::class => [
				'consumers' => 123,
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}


	public function it_throws_on_invalid_attribute_manager(ContainerInterface $container, $attributeManager)
	{
		$key = 'invalid';
		$config = [
			PH\RouteInjection\Factory::class => [
				'attributes' => $key,
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->has($key)->shouldBeCalledOnce()->willReturn(true);
		$container->get($key)->shouldBeCalledOnce()->willReturn($attributeManager);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}

	public function it_throws_on_invalid_attribute_manager_config(ContainerInterface $container)
	{
		$config = [
			PH\RouteInjection\Factory::class => [
				'attributes' => 123,
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}


	public function it_throws_on_invalid_producer_manager(ContainerInterface $container, $producerManager)
	{
		$key = 'invalid';
		$config = [
			PH\RouteInjection\Factory::class => [
				'producers' => $key,
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->has($key)->shouldBeCalledOnce()->willReturn(true);
		$container->get($key)->shouldBeCalledOnce()->willReturn($producerManager);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}

	public function it_throws_on_invalid_producer_manager_config(ContainerInterface $container)
	{
		$config = [
			PH\RouteInjection\Factory::class => [
				'producers' => 123,
			]
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}

	public function it_throws_on_invalid_response_generator(ContainerInterface $container)
	{
		$config = [
			PH\RouteInjection\Factory::class => []
		];
		$container->get('config')->shouldBeCalledOnce()->willReturn($config);
		$container->get(Response::class)->shouldBeCalledOnce()->willReturn(null);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'router']);
	}
}
