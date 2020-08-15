<?php
declare(strict_types=1);

namespace Articus\PathHandler\MetadataProvider;

use Articus\PathHandler\Annotation as PHA;
use Articus\PathHandler\MetadataProviderInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;
use Laminas\ServiceManager\PluginManagerInterface;
use Laminas\Stdlib\FastPriorityQueue;

/**
 * Provider that gets handler metadata from handler class annotations
 */
class Annotation implements MetadataProviderInterface
{
	public const CACHE_KEY = 'metadata';

	/**
	 * @var PluginManagerInterface
	 */
	protected $handlerPluginManager;

	/**
	 * @var CacheInterface
	 */
	protected $cache;

	/**
	 * @var bool
	 */
	protected $needCacheUpdate = false;

	/**
	 * Map <handler name> -> <handler class name>
	 * @var string[] Map<string, string>
	 */
	protected $handlerClassNames;

	/**
	 * Map <handler class name> -> <sorted list of routes>
	 * @var string[][] Map<string, string[]>
	 */
	protected $routes;

	/**
	 * Map <handler class name> -> <http method> -> <handler method name>
	 * @var string[][] Map<string, Map<string, string>>
	 */
	protected $handlerMethodNames;

	/**
	 * Map <handler class name> -> <handler method name> -> <sorted list of consumers>
	 * @var string[][][] Map<string, Map<string, string[]>>
	 */
	protected $consumers;

	/**
	 * Map <handler class name> -> <handler method name> -> <sorted list of attributes>
	 * @var string[][][] Map<string, Map<string, string[]>>
	 */
	protected $attributes;

	/**
	 * Map <handler class name> -> <handler method name> -> <sorted list of producers>
	 * @var string[][][] Map<string, Map<string, string[]>>
	 */
	protected $producers;

	/**
	 * MetadataProvider constructor.
	 * @param PluginManagerInterface $handlerPluginManager
	 * @param CacheInterface $cache
	 */
	public function __construct(PluginManagerInterface $handlerPluginManager, CacheInterface $cache)
	{
		$this->handlerPluginManager = $handlerPluginManager;
		$this->cache = $cache;

		//Restore internal data from cache
		[
			$this->handlerClassNames,
			$this->routes,
			$this->handlerMethodNames,
			$this->consumers,
			$this->attributes,
			$this->producers
		] = $this->cache->get(self::CACHE_KEY) ?? [[], [], [], [], [], []];
	}

	public function __destruct()
	{
		//Dump updated internal data to cache
		if ($this->needCacheUpdate)
		{
			$this->cache->set(
				self::CACHE_KEY,
				[
					$this->handlerClassNames,
					$this->routes,
					$this->handlerMethodNames,
					$this->consumers,
					$this->attributes,
					$this->producers
				]
			);
		}
	}

	/**
	 * @inheritdoc
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	public function getHttpMethods(string $handlerName): array
	{
		$this->ascertainMetadata($handlerName);
		$handlerClassName = $this->handlerClassNames[$handlerName];
		return \array_keys($this->handlerMethodNames[$handlerClassName]);
	}

	/**
	 * @inheritdoc
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	public function getRoutes(string $handlerName): \Generator
	{
		$this->ascertainMetadata($handlerName);
		$handlerClassName = $this->handlerClassNames[$handlerName];
		yield from ($this->routes[$handlerClassName]);
	}

	/**
	 * @inheritdoc
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	public function hasConsumers(string $handlerName, string $httpMethod): bool
	{
		$this->ascertainMetadata($handlerName, $httpMethod);
		$handlerClassName = $this->handlerClassNames[$handlerName];
		$handlerMethodName = $this->handlerMethodNames[$handlerClassName][$httpMethod];
		return (!empty($this->consumers[$handlerClassName][$handlerMethodName]));
	}

	/**
	 * @inheritdoc
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	public function getConsumers(string $handlerName, string $httpMethod): \Generator
	{
		$this->ascertainMetadata($handlerName, $httpMethod);
		$handlerClassName = $this->handlerClassNames[$handlerName];
		$handlerMethodName = $this->handlerMethodNames[$handlerClassName][$httpMethod];
		yield from ($this->consumers[$handlerClassName][$handlerMethodName]);
	}

	/**
	 * @inheritdoc
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	public function getAttributes(string $handlerName, string $httpMethod): \Generator
	{
		$this->ascertainMetadata($handlerName, $httpMethod);
		$handlerClassName = $this->handlerClassNames[$handlerName];
		$handlerMethodName = $this->handlerMethodNames[$handlerClassName][$httpMethod];
		yield from ($this->attributes[$handlerClassName][$handlerMethodName]);
	}

	/**
	 * @inheritdoc
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	public function hasProducers(string $handlerName, string $httpMethod): bool
	{
		$this->ascertainMetadata($handlerName, $httpMethod);
		$handlerClassName = $this->handlerClassNames[$handlerName];
		$handlerMethodName = $this->handlerMethodNames[$handlerClassName][$httpMethod];
		return (!empty($this->producers[$handlerClassName][$handlerMethodName]));
	}

	/**
	 * @inheritdoc
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	public function getProducers(string $handlerName, string $httpMethod): \Generator
	{
		$this->ascertainMetadata($handlerName, $httpMethod);
		$handlerClassName = $this->handlerClassNames[$handlerName];
		$handlerMethodName = $this->handlerMethodNames[$handlerClassName][$httpMethod];
		yield from ($this->producers[$handlerClassName][$handlerMethodName]);
	}

	/**
	 * @inheritdoc
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	public function executeHandlerMethod(string $handlerName, string $httpMethod, $handler, ServerRequestInterface $request)
	{
		$this->ascertainMetadata($handlerName, $httpMethod);
		$handlerClassName = $this->handlerClassNames[$handlerName];
		$handlerMethodName = $this->handlerMethodNames[$handlerClassName][$httpMethod];

		//TODO replace with pregenerated code
		if (!($handler instanceof $handlerClassName))
		{
			throw new \InvalidArgumentException(\sprintf(
				'Invalid handler object: expecting %s, not %s.',
				$handlerClassName,
				\is_object($handler) ? \get_class($handler) : \gettype($handler)
			));
		}
		return $handler->{$handlerMethodName}($request);
	}

	/**
	 * Ensures that metadata for specified handler name was loaded
	 * and optionally checks if this metadata contains information about specified HTTP method
	 * @param string $handlerName
	 * @param string|null $httpMethod
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	protected function ascertainMetadata(string $handlerName, ?string $httpMethod = null): void
	{
		if (empty($this->handlerClassNames[$handlerName]))
		{
			try
			{
				$this->loadMetadata($handlerName);
				$this->needCacheUpdate = true;
			}
			catch (\Throwable $e)
			{
				//Reset all metadata
				[
					$this->handlerClassNames,
					$this->routes,
					$this->handlerMethodNames,
					$this->consumers,
					$this->attributes,
					$this->producers
				] = [[], [], [], [], [], []];
				throw $e;
			}
		}

		if (($httpMethod !== null) && empty($this->handlerMethodNames[$this->handlerClassNames[$handlerName]][$httpMethod]))
		{
			throw new \InvalidArgumentException(\sprintf(
				'Handler %s is not configured to handle %s-method.', $handlerName, $httpMethod
			));
		}
	}

	/**
	 * Loads metadata for specified handler name
	 * @param string $handlerName
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	protected function loadMetadata(string $handlerName): void
	{
		$handler = $this->handlerPluginManager->get($handlerName);
		if (!\is_object($handler))
		{
			throw new \InvalidArgumentException(\sprintf('Handler %s is %s, not object.', $handlerName, \gettype($handler)));
		}
		$handlerClassName = \get_class($handler);
		$this->handlerClassNames[$handlerName] = $handlerClassName;

		$routes = new FastPriorityQueue();
		$handlerMethodNames = [];
		$commonConsumers = new FastPriorityQueue();
		$commonAttributes = new FastPriorityQueue();
		$commonProducers = new FastPriorityQueue();

		$reflection = new \ReflectionClass($handlerClassName);
		$reader = new AnnotationReader();

		//Process class annotations
		foreach ($reader->getClassAnnotations($reflection) as $annotation)
		{
			switch (true)
			{
				case ($annotation instanceof PHA\Route):
					$routes->insert([$annotation->name, $annotation->pattern, $annotation->defaults], $annotation->priority);
					break;
				case ($annotation instanceof PHA\Consumer):
					$commonConsumers->insert([$annotation->mediaRange, $annotation->name, $annotation->options], $annotation->priority);
					break;
				case ($annotation instanceof PHA\Attribute):
					$commonAttributes->insert([$annotation->name, $annotation->options], $annotation->priority);
					break;
				case ($annotation instanceof PHA\Producer):
					$commonProducers->insert([$annotation->mediaType, $annotation->name, $annotation->options], $annotation->priority);
					break;
			}
		}
		if ($routes->isEmpty())
		{
			throw new \LogicException(\sprintf('Invalid metadata for %s: no route.', $handlerClassName));
		}
		$this->routes[$handlerClassName] = $routes->toArray();

		//Process public method annotations
		foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method)
		{
			$handlerMethodName = $method->getName();
			$hasMetadata = false;
			$consumers = clone $commonConsumers;
			$attributes = clone $commonAttributes;
			$producers = clone $commonProducers;
			foreach ($reader->getMethodAnnotations($method) as $annotation)
			{
				switch (true)
				{
					case ($annotation instanceof PHA\HttpMethod):
						$httpMethod = $annotation->getValue();
						if (!empty($handlerMethodNames[$httpMethod]))
						{
							throw new \LogicException(\sprintf(
								'Invalid metadata for %s: both %s and %s are declared to handle %s-method.',
								$handlerClassName,
								$handlerMethodNames[$httpMethod],
								$handlerMethodName,
								$httpMethod
							));
						}
						$handlerMethodNames[$httpMethod] = $handlerMethodName;
						$hasMetadata = true;
						break;
					case ($annotation instanceof PHA\Consumer):
						$consumers->insert([$annotation->mediaRange, $annotation->name, $annotation->options], $annotation->priority);
						$hasMetadata = true;
						break;
					case ($annotation instanceof PHA\Attribute):
						$attributes->insert([$annotation->name, $annotation->options], $annotation->priority);
						$hasMetadata = true;
						break;
					case ($annotation instanceof PHA\Producer):
						$producers->insert([$annotation->mediaType, $annotation->name, $annotation->options], $annotation->priority);
						$hasMetadata = true;
						break;
				}
			}
			if ($hasMetadata)
			{
				if ($method->getNumberOfRequiredParameters() > 1)
				{
					throw new \LogicException(\sprintf(
						'Invalid method %s with metadata for %s: more than one required parameter.',
						$handlerMethodName,
						$handlerClassName
					));
				}
				$this->consumers[$handlerClassName][$handlerMethodName] = $consumers->toArray();
				$this->attributes[$handlerClassName][$handlerMethodName] = $attributes->toArray();
				$this->producers[$handlerClassName][$handlerMethodName] = $producers->toArray();
			}
		}
		if (empty($handlerMethodNames))
		{
			throw new \LogicException(\sprintf('Invalid metadata for %s: no HTTP methods.', $handlerClassName));
		}
		$this->handlerMethodNames[$handlerClassName] = $handlerMethodNames;
	}
}