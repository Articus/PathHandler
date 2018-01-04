<?php

namespace Test\PathHandler;

use Articus\PathHandler\Router\FastRouteAnnotation;
use Articus\PathHandler\Router\TreeConfiguration;
use Zend\Cache\Storage\StorageInterface;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;
use Zend\Router\Http\TreeRouteStack;
use Prophecy\Argument;
use Prophecy\Prophecy;
use Zend\Psr7Bridge\Zend\Request as ZendRequest;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Router\RouteMatch;
use Test\PathHandler\Sample;


class RouterTest extends \Codeception\Test\Unit
{
	use RequestTrait;
	/**
	 * @var \Test\PathHandler\FunctionalTester
	 */
	protected $tester;

	protected function _before()
	{
	}

	protected function _after()
	{
	}

	protected function createFastRouter(array $handers, $handlerAttr)
	{
		$storage = $this->prophesize(StorageInterface::class);
		$storage->getItem('metadata')->willReturn(null)->shouldBeCalledTimes(1);
		$storage->setItem('metadata', Argument::type('array'))->shouldBeCalledTimes(1);
		$fastRouter = new FastRouteAnnotation($storage->reveal(), $handers, $handlerAttr);

		return $fastRouter;
	}

	public function provideAddRouteFailure()
	{
		$zendTreeRouter = $this->prophesize(TreeRouteStack::class);
		$treeRouter = new TreeConfiguration($zendTreeRouter->reveal());
		$treeException = new \LogicException(
			'Articus\PathHandler\Router\TreeConfiguration does not support dynamic route adding. Please, supply all routes in constructor.'
		);

		$fastRouter = $this->createFastRouter([], 'handler');
		$fastException = new \LogicException('Articus\PathHandler\Router\FastRouteAnnotation does not support dynamic route adding. Routes should be declared via annotations in handlers specified in constructor.');

		return [
			'tree configuration' => [$treeRouter, $treeException],
			'fast route annotation' => [$fastRouter, $fastException],
		];
	}

	/**
	 * @dataProvider provideAddRouteFailure
	 */
	public function testAddRouteFailure(RouterInterface $router, \Exception $exception)
	{
		$tester = $this->tester;

		$tester->expectException($exception, function () use (&$router)
		{
			$router->addRoute(new Route('test', ''));
		});
	}

	public function provideMatchForUnknownRoute()
	{
		$zendTreeRouter = $this->prophesize(TreeRouteStack::class);
		$zendTreeRouter->addMethodProphecy(
			(new Prophecy\MethodProphecy($zendTreeRouter, 'match', [Argument::type(ZendRequest::class)]))
				->willReturn(null)
				->shouldBeCalledTimes(1)
		);
		$treeRouter = new TreeConfiguration($zendTreeRouter->reveal());

		$fastRouter = $this->createFastRouter([], 'handler');

		return [
			'tree configuration' => [$treeRouter],
			'fast route annotation' => [$fastRouter],
		];
	}

	/**
	 * @dataProvider provideMatchForUnknownRoute
	 */
	public function testMatchForUnknownRoute(RouterInterface $router)
	{
		$tester = $this->tester;

		$request = $this->createRequest('GET', '/test', [], [], 'test:123');

		$matchResult = $router->match($request);
		$tester->assertInstanceOf(RouteResult::class, $matchResult);
		$tester->assertTrue($matchResult->isFailure());
	}

	public function provideMatchForKnownRoute()
	{
		return [
			'tree configuration' => $this->provideMatchForKnownRouteForTree(),
			'fast route annotation, static' => $this->provideMatchForKnownRouteForFastRoute(
				'/static/test',
				'/static/test',
				[Sample\Handler\EmptyWithStaticRoute::class]
			),
			'fast route annotation, variable' => $this->provideMatchForKnownRouteForFastRoute(
				'/variable/123',
				'/variable/{test}',
				[Sample\Handler\EmptyWithVariableRoute::class],
				['test' => '123']
			),
			'fast route annotation, variables' => $this->provideMatchForKnownRouteForFastRoute(
				'/variables/123/and/qwer',
				'/variables/{test1}/and/{test2}',
				[Sample\Handler\EmptyWithVariablesRoute::class],
				['test1' => '123', 'test2' => 'qwer']
			),
			'fast route annotation, masked variable' => $this->provideMatchForKnownRouteForFastRoute(
				'/variable/123',
				'/variable/{test:\\d+}',
				[Sample\Handler\EmptyWithMaskedVariableRoute::class],
				['test' => '123']
			),
			'fast route annotation, optional' => $this->provideMatchForKnownRouteForFastRoute(
				'/optional',
				'/optional[/test]',
				[Sample\Handler\EmptyWithOptionalRoute::class]
			),
			'fast route annotation, optional full' => $this->provideMatchForKnownRouteForFastRoute(
				'/optional/test',
				'/optional[/test]',
				[Sample\Handler\EmptyWithOptionalRoute::class]
			),
			'fast route annotation, optional variable' => $this->provideMatchForKnownRouteForFastRoute(
				'/optional',
				'/optional[/variable/{test}]',
				[Sample\Handler\EmptyWithOptionalVariableRoute::class]
			),
			'fast route annotation, optional variable full' => $this->provideMatchForKnownRouteForFastRoute(
				'/optional/variable/123', 
				'/optional[/variable/{test}]', 
				[Sample\Handler\EmptyWithOptionalVariableRoute::class],
				['test' => '123']
			),
			'fast route annotation, optionals' => $this->provideMatchForKnownRouteForFastRoute(
				'/optionals', 
				'/optionals[/test1[/test2]]', 
				[Sample\Handler\EmptyWithOptionalsRoute::class]
			),
			'fast route annotation, optionals short' => $this->provideMatchForKnownRouteForFastRoute(
				'/optionals/test1', 
				'/optionals[/test1[/test2]]', 
				[Sample\Handler\EmptyWithOptionalsRoute::class]
			),
			'fast route annotation, optionals full' => $this->provideMatchForKnownRouteForFastRoute(
				'/optionals/test1/test2',
				'/optionals[/test1[/test2]]',
				[Sample\Handler\EmptyWithOptionalsRoute::class]
			),
			'fast route annotation, multiple (static)' => $this->provideMatchForKnownRouteForFastRoute(
				'/static/test',
				'/static/test',
				[Sample\Handler\EmptyWithRoutes::class]
			),
			'fast route annotation, multiple (variable)' => $this->provideMatchForKnownRouteForFastRoute(
				'/variable/123',
				'/variable/{test}',
				[Sample\Handler\EmptyWithRoutes::class],
				['test' => '123']
			),
			'fast route annotation, multiple (optional)' => $this->provideMatchForKnownRouteForFastRoute(
				'/optional',
				'/optional[/test]',
				[Sample\Handler\EmptyWithRoutes::class]
			),
			'fast route annotation, multiple (optional full)' => $this->provideMatchForKnownRouteForFastRoute(
				'/optional/test',
				'/optional[/test]',
				[Sample\Handler\EmptyWithRoutes::class]
			),
			'fast route annotation, with name' => $this->provideMatchForKnownRouteForFastRoute(
				'/static/test',
				'/static/test',
				[Sample\Handler\EmptyWithRouteWithName::class],
				[],
				0,
				'test'
			),
			'fast route annotation, with defaults' => $this->provideMatchForKnownRouteForFastRoute(
				'/static/test',
				'/static/test',
				[Sample\Handler\EmptyWithRouteWithDefaults::class],
				['test1' => 123, 'test2' => 'qwer']
			),
			'fast route annotation, with unequal priority, higher wins' => $this->provideMatchForKnownRouteForFastRoute(
				'/priority/123',
				'/priority/{test:\d+}',
				[Sample\Handler\EmptyWithRouteWithLowPriority::class, Sample\Handler\EmptyWithRouteWithHighPriority::class],
				['test' => '123'],
				1
			),
			'fast route annotation, with equal priority, first wins' => $this->provideMatchForKnownRouteForFastRoute(
				'/priority/123',
				'/priority/{test:\d+}',
				[Sample\Handler\EmptyWithRouteWithEqualPriority1::class, Sample\Handler\EmptyWithRouteWithEqualPriority2::class],
				['test' => '123'],
				0
			),
		];
	}

	protected function provideMatchForKnownRouteForTree()
	{
		$request = $this->createRequest('GET', '/test', [], [], 'test:123');
		$path = 'test';
		$options = ['test', 123];

		$treeMatchResult = $this->prophesize(RouteMatch::class);
		$treeMatchResult->addMethodProphecy(
			(new Prophecy\MethodProphecy($treeMatchResult, 'getMatchedRouteName', []))
				->willReturn('test')
				->shouldBeCalledTimes(2)
		);
		$treeMatchResult->addMethodProphecy(
			(new Prophecy\MethodProphecy($treeMatchResult, 'getParams', []))
				->willReturn($options)
				->shouldBeCalledTimes(1)
		);

		$treeRouter = $this->prophesize(TreeRouteStack::class);
		$treeRouter->addMethodProphecy(
			(new Prophecy\MethodProphecy($treeRouter, 'match', [Argument::type(ZendRequest::class)]))
				->willReturn($treeMatchResult->reveal())
				->shouldBeCalledTimes(1)
		);
		$router = new TreeConfiguration($treeRouter->reveal());
		return [$request, $path, $path, $options, $router];
	}

	protected function provideMatchForKnownRouteForFastRoute($path, $pattern, array $handlers, $extraOptions = [], $handlerIndex = 0, $name = null)
	{
		$request = $this->createRequest('GET', $path, [], [], 'test:123');
		$router = $this->createFastRouter($handlers, 'handler_attr');
		$options = array_merge(['handler_attr' => $handlers[$handlerIndex]], $extraOptions);
		return [$request, $name?:$pattern, $pattern, $options, $router];
	}

	/**
	 * @dataProvider provideMatchForKnownRoute
	 */
	public function testMatchForKnownRoute(Request $request, $name, $path, $options, RouterInterface $router)
	{
		$tester = $this->tester;

		$matchResult = $router->match($request);
		$tester->assertInstanceOf(RouteResult::class, $matchResult);
		$tester->assertTrue($matchResult->isSuccess());
		$tester->assertEquals($name, $matchResult->getMatchedRouteName());
		$tester->assertEquals($options, $matchResult->getMatchedParams());

		$route = $matchResult->getMatchedRoute();
		$tester->assertInstanceOf(Route::class, $route);
		$tester->assertEquals($path, $route->getPath());
	}

	public function provideGenerateUriForUnknownRoute()
	{
		$treeException = new \Zend\Router\Exception\RuntimeException('Route with name "test" not found');
		$zendTreeRouter = $this->prophesize(TreeRouteStack::class);
		$zendTreeRouter->addMethodProphecy(
			(new Prophecy\MethodProphecy(
				$zendTreeRouter,
				'assemble',
				[
					Argument::type('array'), Argument::type('array')
				]
			))
				->willThrow($treeException)
				->shouldBeCalledTimes(1)
		);
		$treeRouter = new TreeConfiguration($zendTreeRouter->reveal());

		$fastException = new \LogicException('Unknown route "test".');
		$fastRouter = $this->createFastRouter([], 'handler');

		return [
			'tree configuration' => [$treeRouter, $treeException],
			'fast route annotation' => [$fastRouter, $fastException],
		];
	}

	/**
	 * @dataProvider provideGenerateUriForUnknownRoute
	 */
	public function testGenerateUriForUnknownRoute(RouterInterface $router, \Exception $exception)
	{
		$tester = $this->tester;

		$tester->expectException($exception, function () use (&$router)
		{
			$router->generateUri('test');
		});
	}

	public function provideGenerateUriForKnownRoute()
	{
		return [
			'tree configuration' => $this->provideGenerateUriForKnownRouteForTree(),
			'fast route annotation, static' => $this->provideGenerateUriForKnownRouteForFastRoute(
				'/static/test',
				'/static/test',
				[Sample\Handler\EmptyWithStaticRoute::class]
			),
			'fast route annotation, variable' => $this->provideGenerateUriForKnownRouteForFastRoute(
				'/variable/123',
				'/variable/{test}',
				[Sample\Handler\EmptyWithVariableRoute::class],
				['test' => '123']
			),
			'fast route annotation, variables' => $this->provideGenerateUriForKnownRouteForFastRoute(
				'/variables/123/and/qwer',
				'/variables/{test1}/and/{test2}',
				[Sample\Handler\EmptyWithVariablesRoute::class],
				['test1' => '123', 'test2' => 'qwer']
			),
			'fast route annotation, masked variable' => $this->provideGenerateUriForKnownRouteForFastRoute(
				'/variable/123',
				'/variable/{test:\\d+}',
				[Sample\Handler\EmptyWithMaskedVariableRoute::class],
				['test' => '123']
			),
			'fast route annotation, optional' => $this->provideGenerateUriForKnownRouteForFastRoute(
				'/optional/test',
				'/optional[/test]',
				[Sample\Handler\EmptyWithOptionalRoute::class]
			),
			'fast route annotation, optional without variable' => $this->provideGenerateUriForKnownRouteForFastRoute(
				'/optional',
				'/optional[/variable/{test}]',
				[Sample\Handler\EmptyWithOptionalVariableRoute::class]
			),
			'fast route annotation, optional with variable' => $this->provideGenerateUriForKnownRouteForFastRoute(
				'/optional/variable/123',
				'/optional[/variable/{test}]',
				[Sample\Handler\EmptyWithOptionalVariableRoute::class],
				['test' => '123']
			),
			'fast route annotation, optionals' => $this->provideGenerateUriForKnownRouteForFastRoute(
				'/optionals/test1/test2',
				'/optionals[/test1[/test2]]',
				[Sample\Handler\EmptyWithOptionalsRoute::class]
			),
			'fast route annotation, multiple (static)' => $this->provideGenerateUriForKnownRouteForFastRoute(
				'/static/test',
				'/static/test',
				[Sample\Handler\EmptyWithRoutes::class]
			),
			'fast route annotation, multiple (variable)' => $this->provideGenerateUriForKnownRouteForFastRoute(
				'/variable/123',
				'/variable/{test}',
				[Sample\Handler\EmptyWithRoutes::class],
				['test' => '123']
			),
			'fast route annotation, multiple (optional)' => $this->provideGenerateUriForKnownRouteForFastRoute(
				'/optional/test',
				'/optional[/test]',
				[Sample\Handler\EmptyWithRoutes::class]
			),
			'fast route annotation, with name' => $this->provideGenerateUriForKnownRouteForFastRoute(
				'/static/test',
				'test',
				[Sample\Handler\EmptyWithRouteWithName::class]
			),
		];
	}

	protected function provideGenerateUriForKnownRouteForTree()
	{
		$name = 'test';
		$path = 'test_uri';
		$substitutions = ['test', 123];
		$options = [
			'name' => $name,
			'only_return_path' => true,
		];

		$treeRouter = $this->prophesize(TreeRouteStack::class);
		$treeRouter->addMethodProphecy(
			(new Prophecy\MethodProphecy(
				$treeRouter,
				'assemble',
				[
					$substitutions, $options
				]
			))
				->willReturn($path)
				->shouldBeCalledTimes(1)
		);
		$router = new TreeConfiguration($treeRouter->reveal());

		return [$path, $router, $name, $substitutions];
	}

	protected function provideGenerateUriForKnownRouteForFastRoute($path, $name, array $handlers, array $substitutions = [])
	{
		$router = $this->createFastRouter($handlers, 'handler');
		return [$path, $router, $name, $substitutions];
	}
	/**
	 * @dataProvider provideGenerateUriForKnownRoute
	 */
	public function testGenerateUriForKnownRoute($path, RouterInterface $router, $name, array $substitutions)
	{
		$tester = $this->tester;
		$tester->assertEquals($path, $router->generateUri($name, $substitutions));
	}


}