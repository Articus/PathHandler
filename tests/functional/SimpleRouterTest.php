<?php

namespace Test\PathHandler;

use Articus\PathHandler\SimpleRouter;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;
use Zend\Router\Http\TreeRouteStack;
use Prophecy\Argument;
use Prophecy\Prophecy;
use Zend\Psr7Bridge\Zend\Request as ZendRequest;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Router\RouteMatch;


class SimpleRouterTest extends \Codeception\Test\Unit
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

	protected function createSimpleRoute()
	{
		$treeRouter = $this->prophesize(TreeRouteStack::class);
		$treeRouter->addMethodProphecy(
			(new Prophecy\MethodProphecy($treeRouter, 'match', [Argument::type(Request::class)]))
				->willReturn('test:payload')
				->shouldBeCalledTimes(1)
		);
		return new SimpleRouter($treeRouter->reveal());
	}

	public function testAddRouteFailure()
	{
		$tester = $this->tester;

		$treeRouter = $this->prophesize(TreeRouteStack::class);
		$router = new SimpleRouter($treeRouter->reveal());
		$exception = new \LogicException('Articus\PathHandler\SimpleRouter does not support dynamic route adding. Please, supply all routes in constructor.');
		$tester->expectException($exception, function () use (&$router)
		{
			$router->addRoute(new Route('test', ''));
		});
	}

	public function testMatchForUnknownRoute()
	{
		$tester = $this->tester;

		$treeRouter = $this->prophesize(TreeRouteStack::class);
		$treeRouter->addMethodProphecy(
			(new Prophecy\MethodProphecy($treeRouter, 'match', [Argument::type(ZendRequest::class)]))
				->willReturn(null)
				->shouldBeCalledTimes(1)
		);
		$router = new SimpleRouter($treeRouter->reveal());
		$request = $this->createRequest('GET', '/test', [], [], 'test:123');

		$matchResult = $router->match($request);
		$tester->assertInstanceOf(RouteResult::class, $matchResult);
		$tester->assertTrue($matchResult->isFailure());
	}

	public function testMatchForKnownRoute()
	{
		$tester = $this->tester;

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
		$router = new SimpleRouter($treeRouter->reveal());

		$matchResult = $router->match($request);
		$tester->assertInstanceOf(RouteResult::class, $matchResult);
		$tester->assertTrue($matchResult->isSuccess());
		$tester->assertEquals($path, $matchResult->getMatchedRouteName());
		$tester->assertEquals($options, $matchResult->getMatchedParams());

		$route = $matchResult->getMatchedRoute();
		$tester->assertInstanceOf(Route::class, $route);
		$tester->assertEquals($path, $route->getPath());
	}

	public function testGenerateUriForUnknownRoute()
	{
		$tester = $this->tester;

		$exception = new \Zend\Router\Exception\RuntimeException('Route with name "test" not found');

		$treeRouter = $this->prophesize(TreeRouteStack::class);
		$treeRouter->addMethodProphecy(
			(new Prophecy\MethodProphecy(
				$treeRouter,
				'assemble',
				[
					Argument::type('array'), Argument::type('array')
				]
			))
				->willThrow($exception)
				->shouldBeCalledTimes(1)
		);
		$router = new SimpleRouter($treeRouter->reveal());

		$tester->expectException($exception, function () use (&$router)
		{
			$router->generateUri('test');
		});
	}

	public function testGenerateUriForKnownRoute()
	{
		$tester = $this->tester;

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
		$router = new SimpleRouter($treeRouter->reveal());

		$tester->assertEquals($path, $router->generateUri($name, $substitutions));
	}


}