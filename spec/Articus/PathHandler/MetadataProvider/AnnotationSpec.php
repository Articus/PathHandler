<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\MetadataProvider;
use Doctrine\Common\Annotations\AnnotationException;
use Prophecy\Argument;
use spec\Example;
use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\SimpleCache\CacheInterface;
use Laminas\ServiceManager\PluginManagerInterface;

class AnnotationSpec extends ObjectBehavior
{
	public function it_returns_cached_http_methods_for_handler_if_cache_exists(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$handlerClassName = 'test_class';
		$httpMethods = ['TEST1', 'TEST2'];
		$cacheData = [
			[$handlerName => $handlerClassName],
			[],
			[$handlerClassName => [$httpMethods[0] => 'test1', $httpMethods[1] => 'test2']],
			[],
			[],
			[],
		];

		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn($cacheData);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->getHttpMethods($handlerName)->shouldBe($httpMethods);
		$this->__destruct();
	}

	public function it_returns_http_methods_for_handler_and_saves_them_to_cache_on_destruct_if_cache_is_empty(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$handlerClassName = Example\Handler\ValidHttpMethods::class;
		$handler = new Example\Handler\ValidHttpMethods();
		$httpMethods = ['GET', 'HEAD', 'POST', 'PATCH', 'PUT', 'DELETE', 'OPTIONS', 'CUSTOM_METHOD'];
		$cacheChecker = function (array $cacheData) use ($handlerClassName, $httpMethods)
		{
			return ((!empty($cacheData[2][$handlerClassName]))
				&& \is_array($cacheData[2][$handlerClassName])
				&& (\array_keys($cacheData[2][$handlerClassName]) == $httpMethods)
			);
		};

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);
		$cache->set(PH\MetadataProvider\Annotation::CACHE_KEY, Argument::that($cacheChecker))->shouldBeCalledOnce();

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->getHttpMethods($handlerName)->shouldBe($httpMethods);
		$this->__destruct();
	}

	public function it_throws_on_http_methods_return_for_invalid_handler(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn(null);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->shouldThrow(\InvalidArgumentException::class)->during('getHttpMethods', [$handlerName]);
		$this->__destruct();
	}

	public function it_throws_on_http_methods_return_for_handler_with_several_methods_handling_same_http_method(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$handler = new Example\Handler\SeveralMethodsForSingleHttpMethod();

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->shouldThrow(\LogicException::class)->during('getHttpMethods', [$handlerName]);
		$this->__destruct();
	}

	public function it_throws_on_http_methods_return_for_handler_without_methods_handling_http_methods(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$handler = new Example\Handler\NoMethodsHandlingHttpMethods();

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->shouldThrow(\LogicException::class)->during('getHttpMethods', [$handlerName]);
		$this->__destruct();
	}

	public function it_throws_on_http_methods_return_for_handler_with_empty_http_method(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$handler = new Example\Handler\EmptyHttpMethod();

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->shouldThrow(\LogicException::class)->during('getHttpMethods', [$handlerName]);
		$this->__destruct();
	}

	public function it_throws_on_http_methods_return_for_handler_with_non_string_http_method(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$handler = new Example\Handler\NonStringHttpMethod();

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->shouldThrow(\LogicException::class)->during('getHttpMethods', [$handlerName]);
		$this->__destruct();
	}

	public function it_returns_cached_routes_for_handler_if_cache_exists(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$handlerClassName = 'test_class';
		$routes = [
			['test_1', '/test_1', ['test_1' => 123]],
			['test_2', '/test_2', ['test_2' => 123]],
			['test_3', '/test_3', ['test_3' => 123]],
		];
		$cacheData = [
			[$handlerName => $handlerClassName],
			[$handlerClassName => $routes],
			[$handlerClassName => []],
			[$handlerClassName => []],
			[$handlerClassName => []],
			[$handlerClassName => []],
		];

		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn($cacheData);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->getRoutes($handlerName)->shouldIterateAs($routes);
		$this->__destruct();
	}

	public function it_returns_routes_for_handler_and_saves_them_to_cache_on_destruct_if_cache_is_empty(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$handlerClassName = Example\Handler\ValidRoutes::class;
		$handler = new Example\Handler\ValidRoutes();
		$routes = [
			[null, '/1', []],
			[null, '/2', ['test_2' => 123]],
			['test_3', '/3', []],
			['test_4', '/4', ['test_4' => 123]],
			[null, '/5', []],
			['test_6', '/6', []],
			[null, '/7', ['test_7' => 123]],
			['test_8', '/8', ['test_8' => 123]],
		];
		$cacheChecker = function (array $cacheData) use ($handlerClassName, $routes)
		{
			return ($cacheData[1][$handlerClassName] == $routes);
		};

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);
		$cache->set(PH\MetadataProvider\Annotation::CACHE_KEY, Argument::that($cacheChecker))->shouldBeCalledOnce();

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->getRoutes($handlerName)->shouldIterateAs($routes);
		$this->__destruct();
	}

	public function it_throws_on_routes_return_for_invalid_handler(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn(null);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->getRoutes($handlerName)->shouldThrow(\InvalidArgumentException::class)->during('current', []);
		$this->__destruct();
	}

	public function it_throws_on_routes_return_for_handler_without_routes(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$handler = new Example\Handler\NoRoutes();

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->getRoutes($handlerName)->shouldThrow(\LogicException::class)->during('current', []);
		$this->__destruct();
	}

	public function it_throws_on_routes_return_for_handler_with_no_pattern_route(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$handler = new Example\Handler\NoPatternRoute();

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->getRoutes($handlerName)->shouldThrow(AnnotationException::class)->during('current');
		$this->__destruct();
	}


	public function it_checks_and_returns_cached_consumers_for_handler_method_if_cache_exists(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = ['test_1', 'test_2'];
		$httpMethod = ['TEST1', 'TEST2'];
		$handlerClassName = ['test_class_1', 'test_class_2'];
		$handlerMethod = ['test_method_1', 'test_method_2'];
		$consumers = [
			['test_1/mime', 'test_1', ['test_1' => 123]],
			['test_2/mime', 'test_2', ['test_2' => 123]],
			['test_3/mime', 'test_3', ['test_3' => 123]],
		];
		$cacheData = [
			[
				$handlerName[0] => $handlerClassName[0],
				$handlerName[1] => $handlerClassName[1],
			],
			[
				$handlerClassName[0] => [],
				$handlerClassName[1] => [],
			],
			[
				$handlerClassName[0] => [$httpMethod[0] => $handlerMethod[0]],
				$handlerClassName[1] => [$httpMethod[1] => $handlerMethod[1]],
			],
			[
				$handlerClassName[0] => [$handlerMethod[0] => $consumers],
				$handlerClassName[1] => [$handlerMethod[1] => []],
			],
			[
				$handlerClassName[0] => [],
				$handlerClassName[1] => [],
			],
			[
				$handlerClassName[0] => [],
				$handlerClassName[1] => [],
			],
		];

		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn($cacheData);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->hasConsumers($handlerName[0], $httpMethod[0])->shouldBe(true);
		$this->getConsumers($handlerName[0], $httpMethod[0])->shouldIterateAs($consumers);
		$this->hasConsumers($handlerName[1], $httpMethod[1])->shouldBe(false);
		$this->getConsumers($handlerName[1], $httpMethod[1])->shouldIterateAs([]);
		$this->__destruct();
	}

	public function it_checks_and_returns_consumers_for_handler_method_and_saves_them_to_cache_on_destruct_if_cache_is_empty(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerNames = ['consumers', 'common_consumers'];
		$handlerClassNames = [Example\Handler\ValidConsumers::class, Example\Handler\ValidCommonConsumers::class];
		$handlers = [new Example\Handler\ValidConsumers(), new Example\Handler\ValidCommonConsumers()];
		$httpMethods = ['NO_CONSUMERS', 'SEVERAL_CONSUMERS'];
		$handlerMethods = ['noConsumers', 'severalConsumers'];
		$consumers = [
			$handlerClassNames[0] => [
				$handlerMethods[0] => [],
				$handlerMethods[1] => [
					['*/*', 'test_1', null],
					['*/*', 'test_2', ['test_2' => 123]],
					['test/3', 'test_3', null],
					['test/4', 'test_4', ['test_4' => 123]],
					['*/*', 'test_5', null],
					['test/6', 'test_6', null],
					['*/*', 'test_7', ['test_7' => 123]],
					['test/8', 'test_8', ['test_8' => 123]],
				],
			],
			$handlerClassNames[1] => [
				$handlerMethods[0] => [
					['*/*', 'test_c1', null],
					['*/*', 'test_c2', ['test_c2' => 123]],
					['test/c3', 'test_c3', null],
					['test/c4', 'test_c4', ['test_c4' => 123]],
					['*/*', 'test_c5', null],
					['test/c6', 'test_c6', null],
					['*/*', 'test_c7', ['test_c7' => 123]],
					['test/c8', 'test_c8', ['test_c8' => 123]],
				],
				$handlerMethods[1] => [
					['*/*', 'test_c1', null],
					['*/*', 'test_c2', ['test_c2' => 123]],
					['*/*', 'test_1', null],
					['*/*', 'test_2', ['test_2' => 123]],
					['test/c3', 'test_c3', null],
					['test/c4', 'test_c4', ['test_c4' => 123]],
					['test/3', 'test_3', null],
					['test/4', 'test_4', ['test_4' => 123]],
					['*/*', 'test_c5', null],
					['test/c6', 'test_c6', null],
					['*/*', 'test_c7', ['test_c7' => 123]],
					['test/c8', 'test_c8', ['test_c8' => 123]],
					['*/*', 'test_5', null],
					['test/6', 'test_6', null],
					['*/*', 'test_7', ['test_7' => 123]],
					['test/8', 'test_8', ['test_8' => 123]],
				],
			],
		];
		$cacheChecker = function (array $cacheData) use ($handlerClassNames, $handlerMethods, $consumers)
		{
			$result = true;
			foreach ([0, 1] as $i)
			{
				foreach ([0, 1] as $j)
				{
					$result = ($result
						&& ($cacheData[3][$handlerClassNames[$i]][$handlerMethods[$j]] == $consumers[$handlerClassNames[$i]][$handlerMethods[$j]])
					);
				}
			}
			return $result;
		};

		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);
		$cache->set(PH\MetadataProvider\Annotation::CACHE_KEY, Argument::that($cacheChecker))->shouldBeCalledOnce();

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);

		foreach ([0, 1] as $i)
		{
			$handlerManager->get($handlerNames[$i])->shouldBeCalledOnce()->willReturn($handlers[$i]);

			foreach ([0, 1] as $j)
			{
				$this->hasConsumers($handlerNames[$i], $httpMethods[$j])->shouldBe(!empty($consumers[$handlerClassNames[$i]][$handlerMethods[$j]]));
				$this->getConsumers($handlerNames[$i], $httpMethods[$j])->shouldIterateAs($consumers[$handlerClassNames[$i]][$handlerMethods[$j]]);
			}
		}

		$this->__destruct();
	}

	public function it_throws_on_consumers_check_and_return_for_invalid_handler(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$httpMethod = 'TEST';

		$handlerManager->get($handlerName)->shouldBeCalledTimes(2)->willReturn(null);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->shouldThrow(\InvalidArgumentException::class)->during('hasConsumers', [$handlerName, $httpMethod]);
		$this->getConsumers($handlerName, $httpMethod)->shouldThrow(\InvalidArgumentException::class)->during('current', []);
		$this->__destruct();
	}

	public function it_throws_on_consumers_check_and_return_for_invalid_handler_method(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$handler = new Example\Handler\ValidConsumers();
		$httpMethod = 'UNKNOWN';

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);
		$cache->set(PH\MetadataProvider\Annotation::CACHE_KEY, Argument::any())->shouldBeCalledOnce();

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->shouldThrow(\InvalidArgumentException::class)->during('hasConsumers', [$handlerName, $httpMethod]);
		$this->getConsumers($handlerName, $httpMethod)->shouldThrow(\InvalidArgumentException::class)->during('current', []);

		$this->__destruct();
	}

	public function it_throws_on_consumers_check_and_return_for_handler_with_no_name_consumer(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$httpMethod = 'GET';
		$handler = new Example\Handler\NoNameConsumer();

		$handlerManager->get($handlerName)->shouldBeCalledTimes(2)->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldThrow(AnnotationException::class)->during('hasConsumers', [$handlerName, $httpMethod]);
		$this->getConsumers($handlerName, $httpMethod)->shouldThrow(AnnotationException::class)->during('current', []);
		$this->__destruct();
	}


	public function it_returns_cached_attributes_for_handler_method_if_cache_exists(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = ['test_1', 'test_2'];
		$httpMethod = ['TEST1', 'TEST2'];
		$handlerClassName = ['test_class_1', 'test_class_2'];
		$handlerMethod = ['test_method_1', 'test_method_2'];
		$attributes = [
			['test_1', ['test_1' => 123]],
			['test_2', ['test_2' => 123]],
			['test_3', ['test_3' => 123]],
		];
		$cacheData = [
			[
				$handlerName[0] => $handlerClassName[0],
				$handlerName[1] => $handlerClassName[1],
			],
			[
				$handlerClassName[0] => [],
				$handlerClassName[1] => [],
			],
			[
				$handlerClassName[0] => [$httpMethod[0] => $handlerMethod[0]],
				$handlerClassName[1] => [$httpMethod[1] => $handlerMethod[1]],
			],
			[
				$handlerClassName[0] => [],
				$handlerClassName[1] => [],
			],
			[
				$handlerClassName[0] => [$handlerMethod[0] => $attributes],
				$handlerClassName[1] => [$handlerMethod[1] => []],
			],
			[
				$handlerClassName[0] => [],
				$handlerClassName[1] => [],
			],
		];

		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn($cacheData);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->getAttributes($handlerName[0], $httpMethod[0])->shouldIterateAs($attributes);
		$this->getAttributes($handlerName[1], $httpMethod[1])->shouldIterateAs([]);
		$this->__destruct();
	}

	public function it_returns_attributes_for_handler_method_and_saves_them_to_cache_if_cache_is_empty(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerNames = ['attributes', 'common_attributes'];
		$handlerClassNames = [Example\Handler\ValidAttributes::class, Example\Handler\ValidCommonAttributes::class];
		$handlers = [new Example\Handler\ValidAttributes(), new Example\Handler\ValidCommonAttributes()];
		$httpMethods = ['NO_ATTRIBUTES', 'SEVERAL_ATTRIBUTES'];
		$handlerMethods = ['noAttributes', 'severalAttributes'];
		$attributes = [
			$handlerClassNames[0] => [
				$handlerMethods[0] => [],
				$handlerMethods[1] => [
					['test_1', null],
					['test_2', ['test_2' => 123]],
					['test_3', null],
					['test_4', ['test_4' => 123]],
					['test_5', null],
					['test_6', null],
					['test_7', ['test_7' => 123]],
				],
			],
			$handlerClassNames[1] => [
				$handlerMethods[0] => [
					['test_c1', null],
					['test_c2', ['test_c2' => 123]],
					['test_c3', null],
					['test_c4', ['test_c4' => 123]],
					['test_c5', null],
					['test_c6', null],
					['test_c7', ['test_c7' => 123]],
				],
				$handlerMethods[1] => [
					['test_c1', null],
					['test_c2', ['test_c2' => 123]],
					['test_1', null],
					['test_2', ['test_2' => 123]],
					['test_c3', null],
					['test_c4', ['test_c4' => 123]],
					['test_3', null],
					['test_4', ['test_4' => 123]],
					['test_c5', null],
					['test_c6', null],
					['test_c7', ['test_c7' => 123]],
					['test_5', null],
					['test_6', null],
					['test_7', ['test_7' => 123]],
				],
			],
		];
		$cacheChecker = function (array $cacheData) use ($handlerClassNames, $handlerMethods, $attributes)
		{
			$result = true;
			foreach ([0, 1] as $i)
			{
				foreach ([0, 1] as $j)
				{
					$result = ($result
						&& ($cacheData[4][$handlerClassNames[$i]][$handlerMethods[$j]] == $attributes[$handlerClassNames[$i]][$handlerMethods[$j]])
					);
				}
			}
			return $result;
		};

		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);
		$cache->set(PH\MetadataProvider\Annotation::CACHE_KEY, Argument::that($cacheChecker))->shouldBeCalledOnce();

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);

		foreach ([0, 1] as $i)
		{
			$handlerManager->get($handlerNames[$i])->shouldBeCalledOnce()->willReturn($handlers[$i]);

			foreach ([0, 1] as $j)
			{
				$this->getAttributes($handlerNames[$i], $httpMethods[$j])->shouldIterateAs($attributes[$handlerClassNames[$i]][$handlerMethods[$j]]);
			}
		}

		$this->__destruct();
	}

	public function it_throws_on_attributes_return_for_invalid_handler(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$httpMethod = 'TEST';

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn(null);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->getAttributes($handlerName, $httpMethod)->shouldThrow(\InvalidArgumentException::class)->during('current', []);
		$this->__destruct();
	}

	public function it_throws_on_attributes_return_for_invalid_handler_method(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$handler = new Example\Handler\ValidAttributes();
		$httpMethod = 'UNKNOWN';

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);
		$cache->set(PH\MetadataProvider\Annotation::CACHE_KEY, Argument::any())->shouldBeCalledOnce();

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->getAttributes($handlerName, $httpMethod)->shouldThrow(\InvalidArgumentException::class)->during('current', []);

		$this->__destruct();
	}

	public function it_throws_on_attributes_return_for_handler_with_no_name_attribute(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$httpMethod = 'GET';
		$handler = new Example\Handler\NoNameAttribute();

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($handlerManager, $cache);
		$this->getAttributes($handlerName, $httpMethod)->shouldThrow(AnnotationException::class)->during('current', []);
		$this->__destruct();
	}


	public function it_checks_and_returns_cached_producers_for_handler_method_if_cache_exists(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = ['test_1', 'test_2'];
		$httpMethod = ['TEST1', 'TEST2'];
		$handlerClassName = ['test_class_1', 'test_class_2'];
		$handlerMethod = ['test_method_1', 'test_method_2'];
		$producers = [
			['test_1/mime', 'test_1', ['test_1' => 123]],
			['test_2/mime', 'test_2', ['test_2' => 123]],
			['test_3/mime', 'test_3', ['test_3' => 123]],
		];
		$cacheData = [
			[
				$handlerName[0] => $handlerClassName[0],
				$handlerName[1] => $handlerClassName[1],
			],
			[
				$handlerClassName[0] => [],
				$handlerClassName[1] => [],
			],
			[
				$handlerClassName[0] => [$httpMethod[0] => $handlerMethod[0]],
				$handlerClassName[1] => [$httpMethod[1] => $handlerMethod[1]],
			],
			[
				$handlerClassName[0] => [],
				$handlerClassName[1] => [],
			],
			[
				$handlerClassName[0] => [],
				$handlerClassName[1] => [],
			],
			[
				$handlerClassName[0] => [$handlerMethod[0] => $producers],
				$handlerClassName[1] => [$handlerMethod[1] => []],
			],
		];

		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn($cacheData);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->hasProducers($handlerName[0], $httpMethod[0])->shouldBe(true);
		$this->getProducers($handlerName[0], $httpMethod[0])->shouldIterateAs($producers);
		$this->hasProducers($handlerName[1], $httpMethod[1])->shouldBe(false);
		$this->getProducers($handlerName[1], $httpMethod[1])->shouldIterateAs([]);
		$this->__destruct();
	}

	public function it_checks_and_returns_producers_for_handler_method_and_saves_them_to_cache_on_destruct_if_cache_is_empty(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerNames = ['producers', 'common_producers'];
		$handlerClassNames = [Example\Handler\ValidProducers::class, Example\Handler\ValidCommonProducers::class];
		$handlers = [new Example\Handler\ValidProducers(), new Example\Handler\ValidCommonProducers()];
		$httpMethods = ['NO_PRODUCERS', 'SEVERAL_PRODUCERS'];
		$handlerMethods = ['noProducers', 'severalProducers'];
		$producers = [
			$handlerClassNames[0] => [
				$handlerMethods[0] => [],
				$handlerMethods[1] => [
					['test/1', 'test_1', null],
					['test/2', 'test_2', ['test_2' => 123]],
					['test/3', 'test_3', null],
					['test/4', 'test_4', ['test_4' => 123]],
					['test/5', 'test_5', null],
					['test/6', 'test_6', null],
					['test/7', 'test_7', ['test_7' => 123]],
				],
			],
			$handlerClassNames[1] => [
				$handlerMethods[0] => [
					['test/c1', 'test_c1', null],
					['test/c2', 'test_c2', ['test_c2' => 123]],
					['test/c3', 'test_c3', null],
					['test/c4', 'test_c4', ['test_c4' => 123]],
					['test/c5', 'test_c5', null],
					['test/c6', 'test_c6', null],
					['test/c7', 'test_c7', ['test_c7' => 123]],
				],
				$handlerMethods[1] => [
					['test/c1', 'test_c1', null],
					['test/c2', 'test_c2', ['test_c2' => 123]],
					['test/1', 'test_1', null],
					['test/2', 'test_2', ['test_2' => 123]],
					['test/c3', 'test_c3', null],
					['test/c4', 'test_c4', ['test_c4' => 123]],
					['test/3', 'test_3', null],
					['test/4', 'test_4', ['test_4' => 123]],
					['test/c5', 'test_c5', null],
					['test/c6', 'test_c6', null],
					['test/c7', 'test_c7', ['test_c7' => 123]],
					['test/5', 'test_5', null],
					['test/6', 'test_6', null],
					['test/7', 'test_7', ['test_7' => 123]],
				],
			],
		];
		$cacheChecker = function (array $cacheData) use ($handlerClassNames, $handlerMethods, $producers)
		{
			$result = true;
			foreach ([0, 1] as $i)
			{
				foreach ([0, 1] as $j)
				{
					$result = ($result
						&& ($cacheData[5][$handlerClassNames[$i]][$handlerMethods[$j]] == $producers[$handlerClassNames[$i]][$handlerMethods[$j]])
					);
				}
			}
			return $result;
		};

		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);
		$cache->set(PH\MetadataProvider\Annotation::CACHE_KEY, Argument::that($cacheChecker))->shouldBeCalledOnce();

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);

		foreach ([0, 1] as $i)
		{
			$handlerManager->get($handlerNames[$i])->shouldBeCalledOnce()->willReturn($handlers[$i]);

			foreach ([0, 1] as $j)
			{
				$this->hasProducers($handlerNames[$i], $httpMethods[$j])->shouldBe(!empty($producers[$handlerClassNames[$i]][$handlerMethods[$j]]));
				$this->getProducers($handlerNames[$i], $httpMethods[$j])->shouldIterateAs($producers[$handlerClassNames[$i]][$handlerMethods[$j]]);
			}
		}

		$this->__destruct();
	}

	public function it_throws_on_producers_check_and_return_for_invalid_handler(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$httpMethod = 'TEST';

		$handlerManager->get($handlerName)->shouldBeCalledTimes(2)->willReturn(null);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->shouldThrow(\InvalidArgumentException::class)->during('hasProducers', [$handlerName, $httpMethod]);
		$this->getProducers($handlerName, $httpMethod)->shouldThrow(\InvalidArgumentException::class)->during('current', []);
		$this->__destruct();
	}

	public function it_throws_on_producers_check_and_return_for_invalid_handler_method(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$handler = new Example\Handler\ValidProducers();
		$httpMethod = 'UNKNOWN';

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);
		$cache->set(PH\MetadataProvider\Annotation::CACHE_KEY, Argument::any())->shouldBeCalledOnce();

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->shouldThrow(\InvalidArgumentException::class)->during('hasProducers', [$handlerName, $httpMethod]);
		$this->getProducers($handlerName, $httpMethod)->shouldThrow(\InvalidArgumentException::class)->during('current', []);

		$this->__destruct();
	}

	public function it_throws_on_producers_check_and_return_for_handler_with_no_name_producer(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$httpMethod = 'GET';
		$handler = new Example\Handler\NoNameProducer();

		$handlerManager->get($handlerName)->shouldBeCalledTimes(2)->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldThrow(AnnotationException::class)->during('hasProducers', [$handlerName, $httpMethod]);
		$this->getProducers($handlerName, $httpMethod)->shouldThrow(AnnotationException::class)->during('current', []);
		$this->__destruct();
	}

	public function it_throws_on_producers_check_and_return_for_handler_with_no_media_type_producer(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache
	)
	{
		$handlerName = 'test';
		$httpMethod = 'GET';
		$handler = new Example\Handler\NoMediaTypeProducer();

		$handlerManager->get($handlerName)->shouldBeCalledTimes(2)->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldThrow(AnnotationException::class)->during('hasProducers', [$handlerName, $httpMethod]);
		$this->getProducers($handlerName, $httpMethod)->shouldThrow(AnnotationException::class)->during('current', []);
		$this->__destruct();
	}


	public function it_executes_cached_handler_method_if_cache_exists(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache,
	 	Example\Handler\ValidMethod $handlerObject,
		Request $request,
		$handlerData
	)
	{
		$handlerName = 'test';
		$httpMethod = 'TEST';
		$handlerClassName = Example\Handler\ValidMethod::class;
		$handlerMethod = 'testMethod';
		$handlerObject->testMethod($request)->shouldBeCalledOnce()->willReturn($handlerData);

		$cacheData = [
			[$handlerName => $handlerClassName],
			[$handlerClassName => []],
			[$handlerClassName => [$httpMethod => $handlerMethod]],
			[$handlerClassName => []],
			[$handlerClassName => []],
			[$handlerClassName => []],
		];

		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn($cacheData);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->executeHandlerMethod($handlerName, $httpMethod, $handlerObject, $request)->shouldBe($handlerData);
		$this->__destruct();
	}

	public function it_executes_handler_method_and_saves_metadata_to_cache_on_destruct_if_cache_is_empty(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache,
		Example\Handler\ValidMethod $handlerObject,
		Request $request,
		$handlerData
	)
	{
		$handlerName = 'test';
		$httpMethod = 'TEST';
		$handlerClassName = Example\Handler\ValidMethod::class;
		$handler = new Example\Handler\ValidMethod();
		$handlerMethod = 'testMethod';
		$handlerObject->testMethod($request)->shouldBeCalledOnce()->willReturn($handlerData);

		$cacheChecker =  function(array $cacheData) use ($handlerName, $handlerClassName, $httpMethod, $handlerMethod)
		{
			return (($cacheData[0] === [$handlerName => $handlerClassName])
				&& ($cacheData[2] === [$handlerClassName => [$httpMethod => $handlerMethod]])
			);
		};

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);

		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);
		$cache->set(PH\MetadataProvider\Annotation::CACHE_KEY, Argument::that($cacheChecker))->shouldBeCalledOnce();

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->executeHandlerMethod($handlerName, $httpMethod, $handlerObject, $request)->shouldBe($handlerData);
		$this->__destruct();
	}

	public function it_throws_on_handler_method_execute_for_invalid_handler(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache,
		Example\Handler\ValidMethod $handlerObject,
		Request $request
	)
	{
		$handlerName = 'test';
		$httpMethod = 'TEST';

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn(null);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->shouldThrow(\InvalidArgumentException::class)
			->during('executeHandlerMethod', [$handlerName, $httpMethod, $handlerObject, $request])
		;
		$this->__destruct();
	}

	public function it_throws_on_handler_method_execute_for_invalid_handler_method(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache,
		Example\Handler\ValidMethod $handlerObject,
		Request $request
	)
	{
		$handlerName = 'test';
		$httpMethod = 'UNKNOWN';
		$handler = new Example\Handler\ValidMethod();

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);
		$cache->set(PH\MetadataProvider\Annotation::CACHE_KEY, Argument::any())->shouldBeCalledOnce();

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->shouldThrow(\InvalidArgumentException::class)
			->during('executeHandlerMethod', [$handlerName, $httpMethod, $handlerObject, $request])
		;
		$this->__destruct();
	}

	public function it_throws_on_handler_method_execute_for_handler_method_having_several_required_parameters(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache,
		Example\Handler\ValidMethod $handlerObject,
		Request $request
	)
	{
		$handlerName = 'test';
		$httpMethod = 'TEST';
		$handler = new Example\Handler\SeveralRequiredParametersMethod();

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->shouldThrow(\LogicException::class)
			->during('executeHandlerMethod', [$handlerName, $httpMethod, $handlerObject, $request])
		;
		$this->__destruct();
	}

	public function it_throws_on_handler_method_execute_for_invalid_handler_object(
		PluginManagerInterface $handlerManager,
		CacheInterface $cache,
		$handlerObject,
		Request $request
	)
	{
		$handlerName = 'test';
		$httpMethod = 'TEST';
		$handler = new Example\Handler\ValidMethod();

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$cache->get(PH\MetadataProvider\Annotation::CACHE_KEY)->shouldBeCalledOnce()->willReturn(null);
		$cache->set(PH\MetadataProvider\Annotation::CACHE_KEY, Argument::any())->shouldBeCalledOnce();

		$this->beConstructedWith($handlerManager, $cache);
		$this->shouldImplement(PH\MetadataProviderInterface::class);
		$this->shouldThrow(\InvalidArgumentException::class)
			->during('executeHandlerMethod', [$handlerName, $httpMethod, $handlerObject, $request])
		;
		$this->__destruct();
	}
}
