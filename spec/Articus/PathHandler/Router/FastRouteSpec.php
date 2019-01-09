<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Router;

use Articus\PathHandler as PH;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Zend\Cache\Storage\StorageInterface as CacheStorage;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;


class FastRouteSpec extends ObjectBehavior
{
	public function getMatchers(): array
	{
		return [
			'beRouteResult' => function($subject, bool $success, ?array $allowedMethods, ?Route $route, ?array $matchedParams)
			{
				if (!($subject instanceof RouteResult))
				{
					throw new FailureException(\sprintf(
						'Invalid route result: expecting %s, not %s.',
						RouteResult::class,
						\is_object($subject) ? \get_class($subject) : \gettype($subject)
					));
				}
				if ($subject->isSuccess() !== $success)
				{
					throw new FailureException(\sprintf(
						'Invalid route result success: expecting %s.', $success ? 'true' : 'false'
					));
				}
				if ($subject->getAllowedMethods() !== $allowedMethods)
				{
					throw new FailureException(\sprintf(
						'Invalid route result allowed HTTP methods: expecting %s, not %s.',
						\implode(',', $allowedMethods ?? ['null']),
						\implode(',', $subject->getAllowedMethods() ?? ['null'])
					));
				}
				if (($route !== null) && ($subject->getMatchedRoute() !== $route))
				{
					throw new FailureException('Invalid route result route.');
				}
				if (($matchedParams !== null) && ($subject->getMatchedParams() !== $matchedParams))
				{
					throw new FailureException('Invalid route result matched params.');
				}
				return true;
			}
		];
	}

	public function let(CacheStorage $cache)
	{
		$this->beConstructedWith($cache);
	}

	public function it_matches_static_route_that_was_registered(
		CacheStorage $cache,
		MiddlewareInterface $middleware,
		Request $request,
		UriInterface $uri
	)
	{
		$httpMethods = ['TEST'];
		$path = '/static/test';
		$route = new Route($path, $middleware->getWrappedObject(), $httpMethods);

		$cache->getItem(PH\Router\FastRoute::class)->shouldBeCalledOnce()->willReturn(null);
		$cache->setItem(PH\Router\FastRoute::class, Argument::any())->shouldBeCalledOnce();

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethods[0]);
		$request->getUri()->shouldBeCalledOnce()->willReturn($uri);
		$uri->getPath()->shouldBeCalledOnce()->willReturn($path);

		$this->addRoute($route);
		$this->match($request)->shouldBeRouteResult(true, $httpMethods, $route, []);
	}

	public function it_matches_variable_route_that_was_registered(
		CacheStorage $cache,
		MiddlewareInterface $middleware,
		Request $request,
		UriInterface $uri
	)
	{
		$httpMethods = ['TEST'];
		$route = new Route('/variables/{test1}/and/{test2}', $middleware->getWrappedObject(), $httpMethods);

		$cache->getItem(PH\Router\FastRoute::class)->shouldBeCalledOnce()->willReturn(null);
		$cache->setItem(PH\Router\FastRoute::class, Argument::any())->shouldBeCalledOnce();

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethods[0]);
		$request->getUri()->shouldBeCalledOnce()->willReturn($uri);
		$uri->getPath()->shouldBeCalledOnce()->willReturn('/variables/123/and/456');

		$this->addRoute($route);
		$this->match($request)->shouldBeRouteResult(true, $httpMethods, $route, ['test1' => '123', 'test2' => '456']);
	}

	public function it_matches_masked_variable_route_that_was_registered(
		CacheStorage $cache,
		MiddlewareInterface $middleware,
		Request $request1,
		UriInterface $uri1,
		Request $request2,
		UriInterface $uri2
	)
	{
		$httpMethods = ['TEST'];
		$route = new Route('/variable/{test:\\d+}', $middleware->getWrappedObject(), $httpMethods);

		$cache->getItem(PH\Router\FastRoute::class)->shouldBeCalledOnce()->willReturn(null);
		$cache->setItem(PH\Router\FastRoute::class, Argument::any())->shouldBeCalledOnce();

		$request1->getMethod()->shouldBeCalledOnce()->willReturn($httpMethods[0]);
		$request1->getUri()->shouldBeCalledOnce()->willReturn($uri1);
		$uri1->getPath()->shouldBeCalledOnce()->willReturn('/variable/123');

		$request2->getMethod()->shouldBeCalledOnce()->willReturn($httpMethods[0]);
		$request2->getUri()->shouldBeCalledOnce()->willReturn($uri2);
		$uri2->getPath()->shouldBeCalledOnce()->willReturn('/variable/qwer');

		$this->addRoute($route);
		$this->match($request1)->shouldBeRouteResult(true, $httpMethods, $route, ['test' => '123']);
		$this->match($request2)->shouldBeRouteResult(false, null, null, null);
	}

	public function it_matches_optional_route_that_was_registered(
		CacheStorage $cache,
		MiddlewareInterface $middleware,
		Request $request1,
		UriInterface $uri1,
		Request $request2,
		UriInterface $uri2,
		Request $request3,
		UriInterface $uri3
	)
	{
		$httpMethods = ['TEST'];
		$route = new Route('/optionals[/test1[/test2]]', $middleware->getWrappedObject(), $httpMethods);

		$cache->getItem(PH\Router\FastRoute::class)->shouldBeCalledOnce()->willReturn(null);
		$cache->setItem(PH\Router\FastRoute::class, Argument::any())->shouldBeCalledOnce();

		$request1->getMethod()->shouldBeCalledOnce()->willReturn($httpMethods[0]);
		$request1->getUri()->shouldBeCalledOnce()->willReturn($uri1);
		$uri1->getPath()->shouldBeCalledOnce()->willReturn('/optionals');

		$request2->getMethod()->shouldBeCalledOnce()->willReturn($httpMethods[0]);
		$request2->getUri()->shouldBeCalledOnce()->willReturn($uri2);
		$uri2->getPath()->shouldBeCalledOnce()->willReturn('/optionals/test1');

		$request3->getMethod()->shouldBeCalledOnce()->willReturn($httpMethods[0]);
		$request3->getUri()->shouldBeCalledOnce()->willReturn($uri3);
		$uri3->getPath()->shouldBeCalledOnce()->willReturn('/optionals/test1/test2');

		$this->addRoute($route);
		$this->match($request1)->shouldBeRouteResult(true, $httpMethods, $route, []);
		$this->match($request2)->shouldBeRouteResult(true, $httpMethods, $route, []);
		$this->match($request3)->shouldBeRouteResult(true, $httpMethods, $route, []);
	}

	public function it_matches_optional_variable_route_that_was_registered(
		CacheStorage $cache,
		MiddlewareInterface $middleware,
		Request $request1,
		UriInterface $uri1,
		Request $request2,
		UriInterface $uri2
	)
	{
		$httpMethods = ['TEST'];
		$route = new Route('/optional[/variable/{test}]', $middleware->getWrappedObject(), $httpMethods);

		$cache->getItem(PH\Router\FastRoute::class)->shouldBeCalledOnce()->willReturn(null);
		$cache->setItem(PH\Router\FastRoute::class, Argument::any())->shouldBeCalledOnce();

		$request1->getMethod()->shouldBeCalledOnce()->willReturn($httpMethods[0]);
		$request1->getUri()->shouldBeCalledOnce()->willReturn($uri1);
		$uri1->getPath()->shouldBeCalledOnce()->willReturn('/optional');

		$request2->getMethod()->shouldBeCalledOnce()->willReturn($httpMethods[0]);
		$request2->getUri()->shouldBeCalledOnce()->willReturn($uri2);
		$uri2->getPath()->shouldBeCalledOnce()->willReturn('/optional/variable/qwer');

		$this->addRoute($route);
		$this->match($request1)->shouldBeRouteResult(true, $httpMethods, $route, []);
		$this->match($request2)->shouldBeRouteResult(true, $httpMethods, $route, ['test' => 'qwer']);
	}

	public function it_matches_routes_in_order_they_were_registered(
		CacheStorage $cache,
		MiddlewareInterface $middleware,
		Request $request1,
		UriInterface $uri1,
		Request $request2,
		UriInterface $uri2
	)
	{
		$httpMethods = ['TEST'];
		$route1 = new Route('/test/123', $middleware->getWrappedObject(), $httpMethods);
		$route2 = new Route('/test/{test}', $middleware->getWrappedObject(), $httpMethods);

		$cache->getItem(PH\Router\FastRoute::class)->shouldBeCalledOnce()->willReturn(null);
		$cache->setItem(PH\Router\FastRoute::class, Argument::any())->shouldBeCalledOnce();

		$request1->getMethod()->shouldBeCalledOnce()->willReturn($httpMethods[0]);
		$request1->getUri()->shouldBeCalledOnce()->willReturn($uri1);
		$uri1->getPath()->shouldBeCalledOnce()->willReturn('/test/123');

		$request2->getMethod()->shouldBeCalledOnce()->willReturn($httpMethods[0]);
		$request2->getUri()->shouldBeCalledOnce()->willReturn($uri2);
		$uri2->getPath()->shouldBeCalledOnce()->willReturn('/test/qwer');

		$this->addRoute($route1);
		$this->addRoute($route2);
		$this->match($request1)->shouldBeRouteResult(true, $httpMethods, $route1, []);
		$this->match($request2)->shouldBeRouteResult(true, $httpMethods, $route2, ['test' => 'qwer']);
	}

	public function it_matches_route_that_was_registered_with_defaults(
		CacheStorage $cache,
		MiddlewareInterface $middleware,
		Request $request1,
		UriInterface $uri1,
		Request $request2,
		UriInterface $uri2
	)
	{
		$httpMethods = ['TEST'];
		$route1 = new Route('/test1/{test}', $middleware->getWrappedObject(), $httpMethods);
		$route1->setOptions(['defaults' => ['test1' => 123, 'test2' => 456]]);
		$route2 = new Route('/test2/{test1}', $middleware->getWrappedObject(), $httpMethods);
		$route2->setOptions(['defaults' => ['test1' => 123, 'test2' => 456]]);

		$cache->getItem(PH\Router\FastRoute::class)->shouldBeCalledOnce()->willReturn(null);
		$cache->setItem(PH\Router\FastRoute::class, Argument::any())->shouldBeCalledOnce();

		$request1->getMethod()->shouldBeCalledOnce()->willReturn($httpMethods[0]);
		$request1->getUri()->shouldBeCalledOnce()->willReturn($uri1);
		$uri1->getPath()->shouldBeCalledOnce()->willReturn('/test1/qwer');

		$request2->getMethod()->shouldBeCalledOnce()->willReturn($httpMethods[0]);
		$request2->getUri()->shouldBeCalledOnce()->willReturn($uri2);
		$uri2->getPath()->shouldBeCalledOnce()->willReturn('/test2/qwer');

		$this->addRoute($route1);
		$this->addRoute($route2);
		$this->match($request1)->shouldBeRouteResult(true, $httpMethods, $route1, ['test1' => 123, 'test2' => 456, 'test' => 'qwer']);
		$this->match($request2)->shouldBeRouteResult(true, $httpMethods, $route2, ['test1' => 'qwer', 'test2' => 456]);
	}

	public function it_fails_to_match_route_that_was_not_registered(CacheStorage $cache, Request $request, UriInterface $uri)
	{
		$cache->getItem(PH\Router\FastRoute::class)->shouldBeCalledOnce()->willReturn(null);
		$cache->setItem(PH\Router\FastRoute::class, Argument::any())->shouldBeCalledOnce();

		$request->getMethod()->shouldBeCalledOnce()->willReturn('TEST');
		$request->getUri()->shouldBeCalledOnce()->willReturn($uri);
		$uri->getPath()->shouldBeCalledOnce()->willReturn('/test');

		$this->match($request)->shouldBeRouteResult(false, null, null, null);
	}

	public function it_fails_to_match_route_that_was_registered_with_another_http_method(
		CacheStorage $cache,
		MiddlewareInterface $middleware,
		Request $request,
		UriInterface $uri
	)
	{
		$httpMethods = ['TEST'];
		$path = '/test';

		$cache->getItem(PH\Router\FastRoute::class)->shouldBeCalledOnce()->willReturn(null);
		$cache->setItem(PH\Router\FastRoute::class, Argument::any())->shouldBeCalledOnce();

		$request->getMethod()->shouldBeCalledOnce()->willReturn('INVALID');
		$request->getUri()->shouldBeCalledOnce()->willReturn($uri);
		$uri->getPath()->shouldBeCalledOnce()->willReturn($path);

		$this->addRoute(new Route($path, $middleware->getWrappedObject(), $httpMethods));
		$this->match($request)->shouldBeRouteResult(false, $httpMethods, null, null);
	}

	public function it_registers_route_after_matching(
		CacheStorage $cache,
		MiddlewareInterface $middleware,
		Request $request,
		UriInterface $uri
	)
	{
		$httpMethods = ['TEST'];
		$path = '/test';
		$route = new Route($path, $middleware->getWrappedObject(), $httpMethods);

		$cache->getItem(PH\Router\FastRoute::class)->shouldBeCalledTimes(2)->willReturn(null);
		$cache->setItem(PH\Router\FastRoute::class, Argument::any())->shouldBeCalledTimes(2);

		$request->getMethod()->shouldBeCalledTimes(2)->willReturn($httpMethods[0]);
		$request->getUri()->shouldBeCalledTimes(2)->willReturn($uri);
		$uri->getPath()->shouldBeCalledTimes(2)->willReturn($path);

		$this->match($request)->shouldBeRouteResult(false, null, null, null);
		$this->addRoute($route);
		$this->match($request)->shouldBeRouteResult(true, $httpMethods, $route, []);
	}

	public function it_resets_outdated_cache_on_match(
		CacheStorage $cache,
		MiddlewareInterface $middleware,
		Request $request,
		UriInterface $uri
	)
	{
		$httpMethods = ['TEST'];
		$path = '/test';
		$route = new Route($path, $middleware->getWrappedObject(), $httpMethods);

		$cache->getItem(PH\Router\FastRoute::class)->shouldBeCalledOnce()->willReturn([[[],[]], []]);
		$cache->setItem(PH\Router\FastRoute::class, Argument::any())->shouldBeCalledOnce();

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethods[0]);
		$request->getUri()->shouldBeCalledOnce()->willReturn($uri);
		$uri->getPath()->shouldBeCalledOnce()->willReturn($path);

		$this->addRoute($route);
		$this->match($request)->shouldBeRouteResult(true, $httpMethods, $route, []);
	}

	public function it_throws_on_route_register_for_duplicate_route_name(MiddlewareInterface $middleware)
	{
		$name = 'test';

		$route1 = new Route('/test/1', $middleware->getWrappedObject(), ['TEST1'], $name);
		$route2 = new Route('/test/2', $middleware->getWrappedObject(), ['TEST2'], $name);

		$this->addRoute($route1);
		$this->shouldThrow(\InvalidArgumentException::class)->during('addRoute', [$route1]);
		$this->shouldThrow(\InvalidArgumentException::class)->during('addRoute', [$route2]);
	}

	public function it_generates_uri_for_static_route_that_was_registered(CacheStorage $cache, MiddlewareInterface $middleware)
	{
		$httpMethods = ['TEST'];
		$path = '/static/test';
		$route = new Route($path, $middleware->getWrappedObject(), $httpMethods);

		$cache->getItem(PH\Router\FastRoute::class)->shouldBeCalledOnce()->willReturn(null);
		$cache->setItem(PH\Router\FastRoute::class, Argument::any())->shouldBeCalledOnce();


		$this->addRoute($route);
		$this->generateUri($route->getName())->shouldBe($path);
	}

	public function it_generates_uri_for_variable_route_that_was_registered(CacheStorage $cache, MiddlewareInterface $middleware)
	{
		$httpMethods = ['TEST'];
		$path = '/variable/{test1}/and/{test2}';
		$route = new Route($path, $middleware->getWrappedObject(), $httpMethods);

		$cache->getItem(PH\Router\FastRoute::class)->shouldBeCalledOnce()->willReturn(null);
		$cache->setItem(PH\Router\FastRoute::class, Argument::any())->shouldBeCalledOnce();

		$this->addRoute($route);
		$this->generateUri($route->getName(), ['test1' => '123', 'test2' => '456'])->shouldBe('/variable/123/and/456');
	}

	public function it_generates_uri_for_optional_route_that_was_registered(CacheStorage $cache, MiddlewareInterface $middleware)
	{
		$httpMethods = ['TEST'];
		$path = '/optional[/test1[/test2]]';
		$route = new Route($path, $middleware->getWrappedObject(), $httpMethods);

		$cache->getItem(PH\Router\FastRoute::class)->shouldBeCalledOnce()->willReturn(null);
		$cache->setItem(PH\Router\FastRoute::class, Argument::any())->shouldBeCalledOnce();

		$this->addRoute($route);
		$this->generateUri($route->getName())->shouldBe('/optional/test1/test2');
	}

	public function it_generates_uri_for_optional_variable_route_that_was_registered(CacheStorage $cache, MiddlewareInterface $middleware)
	{
		$httpMethods = ['TEST'];
		$path = '/test[/test1[/{test}]]';
		$route = new Route($path, $middleware->getWrappedObject(), $httpMethods);

		$cache->getItem(PH\Router\FastRoute::class)->shouldBeCalledOnce()->willReturn(null);
		$cache->setItem(PH\Router\FastRoute::class, Argument::any())->shouldBeCalledOnce();

		$this->addRoute($route);
		$this->generateUri($route->getName())->shouldBe('/test/test1');
		$this->generateUri($route->getName(), ['test' => '123'])->shouldBe('/test/test1/123');
		$this->generateUri($route->getName(), [], ['defaults' => ['test' => '456']])->shouldBe('/test/test1/456');
		$this->generateUri($route->getName(), ['test' => '123'], ['defaults' => ['test' => '456']])->shouldBe('/test/test1/123');
	}

	public function it_throws_on_uri_generation_for_route_that_was_not_registered()
	{
		$this->shouldThrow(\InvalidArgumentException::class)->during('generateUri', ['test']);
	}

	public function it_throws_on_uri_generation_for_variable_route_without_all_substitutions(
		CacheStorage $cache,
		MiddlewareInterface $middleware
	)
	{
		$httpMethods = ['TEST'];
		$path = '/variable/{test}';
		$route = new Route($path, $middleware->getWrappedObject(), $httpMethods);

		$cache->getItem(PH\Router\FastRoute::class)->shouldBeCalledOnce()->willReturn(null);
		$cache->setItem(PH\Router\FastRoute::class, Argument::any())->shouldBeCalledOnce();

		$this->addRoute($route);
		$this->shouldThrow(\InvalidArgumentException::class)->during('generateUri', [$route->getName()]);
	}

	public function it_throws_on_uri_generation_for_masked_variable_route_if_substitution_does_not_match_mask(
		CacheStorage $cache,
		MiddlewareInterface $middleware
	)
	{
		$httpMethods = ['TEST'];
		$path = '/variable/{test:\\d+}';
		$route = new Route($path, $middleware->getWrappedObject(), $httpMethods);

		$cache->getItem(PH\Router\FastRoute::class)->shouldBeCalledOnce()->willReturn(null);
		$cache->setItem(PH\Router\FastRoute::class, Argument::any())->shouldBeCalledOnce();

		$this->addRoute($route);
		$this->shouldThrow(\InvalidArgumentException::class)->during('generateUri', [$route->getName(), ['test' => 'qwer']]);
	}

}
