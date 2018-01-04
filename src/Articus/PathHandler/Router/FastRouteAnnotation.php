<?php
namespace Articus\PathHandler\Router;

use Articus\PathHandler\Annotation as PHA;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use FastRoute;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Cache\Storage\StorageInterface as CacheStorage;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;
use Zend\Stdlib\FastPriorityQueue;

/**
 * Simple wrapper for FastRoute that populates it with routes declared in handler annotations
 */
class FastRouteAnnotation implements RouterInterface
{
	const METADATA_CACHE_KEY = 'metadata';
	/**
	 * @var FastRoute\Dispatcher
	 */
	protected $dispatcher;

	/**
	 * @var PHA\Route[][]|array[] - hack to make IDE autocomplete happy, real structure is [PHA\Route, array][]
	 */
	protected $routes;

	/**
	 * FastRouteAnnotation constructor.
	 * @param CacheStorage $metadataCacheStorage
	 * @param array $handlers
	 * @param string $handlerAttr
	 */
	public function __construct(CacheStorage $metadataCacheStorage, array $handlers, $handlerAttr)
	{
		AnnotationRegistry::registerFile(__DIR__ . '/../Annotation/Route.php');
		$metadata = $metadataCacheStorage->getItem(self::METADATA_CACHE_KEY);
		if ($metadata === null)
		{
			$metadata = self::readMetadata($handlers, $handlerAttr);
			$metadataCacheStorage->setItem(self::METADATA_CACHE_KEY, $metadata);
		}
		list($dispatchData, $this->routes) = $metadata;
		$this->dispatcher = new FastRoute\Dispatcher\GroupCountBased($dispatchData);
	}

	protected static function readMetadata(array $handlers, $handlerAttr)
	{
		//Parse handler class routing annotations
		$reader = new AnnotationReader();
		/** @var FastPriorityQueue|PHA\Route[] $queue - hack to make IDE autocomplete happy, when will PHP allow generics? */
		$queue = new FastPriorityQueue();
		foreach ($handlers as $handler)
		{
			$reflection = new \ReflectionClass($handler);
			$noRoute = true;
			foreach ($reader->getClassAnnotations($reflection) as $annotation)
			{
				if ($annotation instanceof PHA\Route)
				{
					//Set default name
					if (empty($annotation->name))
					{
						$annotation->name = $annotation->pattern;
					}
					//Set default handler parameter
					if (empty($annotation->defaults[$handlerAttr]))
					{
						$annotation->defaults[$handlerAttr] = $handler;
					}
					$queue->insert($annotation, $annotation->priority);
					$noRoute = false;
				}
			}
			if ($noRoute)
			{
				throw new \LogicException(sprintf('No route declared for handler %s.', $handler));
			}
		}
		//Prepare metadata
		$routes = [];
		$parser = new FastRoute\RouteParser\Std();
		$dataGenerator = new FastRoute\DataGenerator\GroupCountBased();
		foreach ($queue as $annotation)
		{
			$routeData = $parser->parse($annotation->pattern);
			$routes[$annotation->name] = [$annotation, $routeData];
			foreach ($routeData as $segments)
			{
				$dataGenerator->addRoute('*', $segments, $annotation->name);
			}
		}
		return [$dataGenerator->getData(), $routes];
	}

	/**
	 * @inheritdoc
	 */
	public function addRoute(Route $route)
	{
		throw new \LogicException(sprintf(
			'%s does not support dynamic route adding. Routes should be declared via annotations in handlers specified in constructor.',
			self::class
		));
	}

	/**
	 * @inheritdoc
	 */
	public function match(Request $request)
	{
		$match = $this->dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

		$result = null;
		if ($match[0] === FastRoute\Dispatcher::FOUND)
		{
			$annotation = $this->routes[$match[1]][0];
			$result = RouteResult::fromRoute(
				new Route($annotation->pattern, '', Route::HTTP_METHOD_ANY, $annotation->name),
				array_merge($annotation->defaults, $match[2])
			);
		}
		else
		{
			$result = RouteResult::fromRouteFailure();
		}
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function generateUri($name, array $substitutions = [], array $options = [])
	{
		if (empty($this->routes[$name]))
		{
			throw new \LogicException(sprintf('Unknown route "%s".', $name));
		}
		list($annotation, $routeData) = $this->routes[$name];
		//Gather all parameters that can be used to substitute route parts
		$parameters = array_merge(
			$annotation->defaults,
			empty($options['defaults'])? [] : $options['defaults'],
			$substitutions
		);
		//Look for longest route that can be assembled with parameters
		$parts = [];
		foreach (array_reverse($routeData) as $segments)
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
					list($name, $mask) = $segment;
					if (empty($parameters[$name]))
					{
						//Failure, not enough parameters, stop and check if there are shorter route variant (with less optional parameters)
						$parts = [];
						break;
					}
					elseif (!preg_match('~^' . $mask . '$~', $parameters[$name]))
					{
						throw new \InvalidArgumentException(sprintf('Parameter value for [%s] did not match the regex `%s`', $name, $mask));
					}
					else
					{
						$parts[] = $parameters[$name];
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
			throw new \InvalidArgumentException(sprintf(
				'Failed to generate URI from route "%s": parameter list (%s) is incomplete.',
				$name,
				implode(', ', array_keys($parameters))
			));
		}
		return implode('', $parts);
	}

}