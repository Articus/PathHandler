<?php
declare(strict_types=1);

namespace Articus\PathHandler\Router;

use FastRoute as FR;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\SimpleCache\CacheInterface;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use Mezzio\Router\RouterInterface;
use function array_diff_key;
use function array_keys;
use function array_merge;
use function array_reverse;
use function implode;
use function is_string;
use function preg_match;
use function rawurldecode;
use function sprintf;

/**
 * Router based on nikic/fast-route, alternative for mezzio/mezzio-fastroute
 */
class FastRoute implements RouterInterface
{
	public const CACHE_KEY = 'fast-route';

	/**
	 * Map "route name" -> "route data"
	 * @var array<string, Route>
	 */
	protected array $routes = [];

	protected FR\Dispatcher $dispatcher;

	/**
	 * @var array<string, mixed>
	 */
	protected array $parsedRoutes;

	public function __construct(
		protected CacheInterface $cache
	)
	{
	}

	/**
	 * @inheritdoc
	 */
	public function addRoute(Route $route): void
	{
		$routeName = $route->getName();
		if (isset($this->routes[$routeName]))
		{
			throw new InvalidArgumentException(sprintf('Route %s has already been registered.', $routeName));
		}
		$this->routes[$routeName] = $route;
	}

	/**
	 * @inheritdoc
	 */
	public function match(Request $request): RouteResult
	{
		$this->ascertainRoutingData();

		$path = rawurldecode($request->getUri()->getPath());
		$match = $this->dispatcher->dispatch($request->getMethod(), $path);
		switch ($match[0])
		{
			case FR\Dispatcher::FOUND:
				$route = $this->routes[$match[1]];
				$params = array_merge($route->getOptions()['defaults'] ?? [], $match[2]);
				return RouteResult::fromRoute($route, $params);
			case FR\Dispatcher::METHOD_NOT_ALLOWED:
				return RouteResult::fromRouteFailure($match[1]);
			default:
				return RouteResult::fromRouteFailure(null);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function generateUri(string $name, array $substitutions = [], array $options = []): string
	{
		$routeName = $name;
		$this->ascertainRoutingData($routeName);

		//Gather all parameters that can be used to substitute route parts
		$parameters = array_merge(
			$this->routes[$routeName]->getOptions()['defaults'] ?? [],
			$options['defaults'] ?? [],
			$substitutions
		);
		//Look for longest route that can be assembled with parameters
		$parts = [];
		foreach (array_reverse($this->parsedRoutes[$routeName]) as $segments)
		{
			$parts = [];
			foreach ($segments as $segment)
			{
				if (is_string($segment))
				{
					$parts[] = $segment;
				}
				else
				{
					[$segmentName, $mask] = $segment;
					if (empty($parameters[$segmentName]))
					{
						//Failure, not enough parameters, stop and check if there are shorter route variant (with less optional parameters)
						$parts = [];
						break;
					}
					elseif (!preg_match('~^' . $mask . '$~', $parameters[$segmentName]))
					{
						throw new InvalidArgumentException(sprintf(
							'Value for parameter "%s" does not match route mask "%s"', $segmentName, $mask
						));
					}
					else
					{
						$parts[] = $parameters[$segmentName];
					}
				}
			}
			if (!empty($parts))
			{
				//Success, all route parts are gathered
				break;
			}
		}
		if (empty($parts))
		{
			throw new InvalidArgumentException(sprintf(
				'Failed to generate URI from route "%s": parameter list (%s) is incomplete.',
				$routeName,
				implode(', ', array_keys($parameters))
			));
		}
		return implode('', $parts);
	}

	/**
	 * Ensures that all data required for routing is loaded and valid
	 */
	protected function ascertainRoutingData(null|string $routeName = null): void
	{
		if (($routeName !== null) && empty($this->routes[$routeName]))
		{
			throw new InvalidArgumentException(sprintf('Route %s is not registered.', $routeName));
		}

		if (!(isset($this->dispatcher) && isset($this->parsedRoutes) && empty(array_diff_key($this->routes, $this->parsedRoutes))))
		{
			$routingData = $this->cache->get(self::CACHE_KEY);
			//Check if cached routing data corresponds with added routes
			if (($routingData !== null) && (!empty(array_diff_key($this->routes, $routingData[1]))))
			{
				$routingData = null;
			}
			//Generate and cache routing data anew if needed
			if ($routingData === null)
			{
				$routingData = $this->generateRoutingData();
				$this->cache->set(self::CACHE_KEY, $routingData);
			}
			$this->dispatcher = new FR\Dispatcher\GroupCountBased($routingData[0]);
			$this->parsedRoutes = $routingData[1];
		}
	}

	/**
	 * Generates data for routing: ("data required for dispatcher construction", "data about parsed routes for URI generation")
	 * @return array{0: array, 1: array}
	 */
	protected function generateRoutingData(): array
	{
		$parsedRoutes = [];
		$parser = new FR\RouteParser\Std();
		$dispatcherDataGenerator = new FR\DataGenerator\GroupCountBased();
		foreach ($this->routes as $routeName => $route)
		{
			$parsedRoute = $parser->parse($route->getPath());
			foreach ($route->getAllowedMethods() ?? ['*'] as $httpMethod)
			{
				foreach ($parsedRoute as $segments)
				{
					$dispatcherDataGenerator->addRoute($httpMethod, $segments, $routeName);
				}
			}
			$parsedRoutes[$routeName] = $parsedRoute;
		}
		return [$dispatcherDataGenerator->getData(), $parsedRoutes];
	}
}
