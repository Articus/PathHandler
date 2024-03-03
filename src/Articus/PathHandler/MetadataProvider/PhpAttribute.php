<?php
declare(strict_types=1);

namespace Articus\PathHandler\MetadataProvider;

use Articus\PathHandler\MetadataProviderInterface;
use Articus\PathHandler\PhpAttribute as PHA;
use Articus\PluginManager\PluginManagerInterface;
use Generator;
use InvalidArgumentException;
use Laminas\Stdlib\FastPriorityQueue;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use ReflectionMethod;
use Throwable;
use function array_keys;
use function get_class;
use function get_debug_type;
use function is_object;
use function sprintf;

/**
 * Provider that gets handler metadata from handler class PHP attributes
 */
class PhpAttribute implements MetadataProviderInterface
{
	public const CACHE_KEY = 'php-attribute-metadata';

	protected bool $needCacheUpdate = false;

	/**
	 * Map "handler name" -> "handler class name"
	 * @var array<string, string>
	 */
	protected array $handlerClassNames;

	/**
	 * Map "handler class name" -> "sorted list of routes"
	 * @var array<string, array<array{0: string, 1: string, 2: array}>>
	 */
	protected array $routes;

	/**
	 * Map "handler class name" -> "http method" -> "handler method name"
	 * @var array<string, array<string, string>>
	 */
	protected array $handlerMethodNames;

	/**
	 * Map "handler class name" -> "handler method name" -> "sorted list of consumers"
	 * @var array<string, array<string, array<array{0: string, 1: string, 2: array}>>>
	 */
	protected array $consumers;

	/**
	 * Map "handler class name" -> "handler method name" -> "sorted list of attributes"
	 * @var array<string, array<string, array<array{0: string, 1: array}>>>
	 */
	protected array $attributes;

	/**
	 * Map "handler class name" -> "handler method name" -> "sorted list of producers"
	 * @var array<string, array<string, array<array{0: string, 1: string, 2: array}>>>
	 */
	protected array $producers;

	public function __construct(
		protected PluginManagerInterface $handlerPluginManager,
		protected CacheInterface $cache
	)
	{
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
	 */
	public function getHttpMethods(string $handlerName): array
	{
		$this->ascertainMetadata($handlerName);
		$handlerClassName = $this->handlerClassNames[$handlerName];
		return array_keys($this->handlerMethodNames[$handlerClassName]);
	}

	/**
	 * @inheritdoc
	 */
	public function getRoutes(string $handlerName): Generator
	{
		$this->ascertainMetadata($handlerName);
		$handlerClassName = $this->handlerClassNames[$handlerName];
		yield from ($this->routes[$handlerClassName]);
	}

	/**
	 * @inheritdoc
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
	 */
	public function getConsumers(string $handlerName, string $httpMethod): Generator
	{
		$this->ascertainMetadata($handlerName, $httpMethod);
		$handlerClassName = $this->handlerClassNames[$handlerName];
		$handlerMethodName = $this->handlerMethodNames[$handlerClassName][$httpMethod];
		yield from ($this->consumers[$handlerClassName][$handlerMethodName]);
	}

	/**
	 * @inheritdoc
	 */
	public function getAttributes(string $handlerName, string $httpMethod): Generator
	{
		$this->ascertainMetadata($handlerName, $httpMethod);
		$handlerClassName = $this->handlerClassNames[$handlerName];
		$handlerMethodName = $this->handlerMethodNames[$handlerClassName][$httpMethod];
		yield from ($this->attributes[$handlerClassName][$handlerMethodName]);
	}

	/**
	 * @inheritdoc
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
	 */
	public function getProducers(string $handlerName, string $httpMethod): Generator
	{
		$this->ascertainMetadata($handlerName, $httpMethod);
		$handlerClassName = $this->handlerClassNames[$handlerName];
		$handlerMethodName = $this->handlerMethodNames[$handlerClassName][$httpMethod];
		yield from ($this->producers[$handlerClassName][$handlerMethodName]);
	}

	/**
	 * @inheritdoc
	 */
	public function executeHandlerMethod(string $handlerName, string $httpMethod, object $handler, ServerRequestInterface $request): mixed
	{
		$this->ascertainMetadata($handlerName, $httpMethod);
		$handlerClassName = $this->handlerClassNames[$handlerName];
		$handlerMethodName = $this->handlerMethodNames[$handlerClassName][$httpMethod];

		//TODO replace with pregenerated code
		if (!($handler instanceof $handlerClassName))
		{
			throw new InvalidArgumentException(sprintf(
				'Invalid handler object: expecting %s, not %s.', $handlerClassName, get_debug_type($handler)
			));
		}
		return $handler->{$handlerMethodName}($request);
	}

	/**
	 * Ensures that metadata for specified handler name was loaded
	 * and optionally checks if this metadata contains information about specified HTTP method
	 */
	protected function ascertainMetadata(string $handlerName, null|string $httpMethod = null): void
	{
		if (empty($this->handlerClassNames[$handlerName]))
		{
			try
			{
				$this->loadMetadata($handlerName);
				$this->needCacheUpdate = true;
			}
			catch (Throwable $e)
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
			throw new InvalidArgumentException(sprintf(
				'Handler %s is not configured to handle %s-method.', $handlerName, $httpMethod
			));
		}
	}

	/**
	 * Loads metadata for specified handler name
	 */
	protected function loadMetadata(string $handlerName): void
	{
		$handler = ($this->handlerPluginManager)($handlerName, []);
		if (!is_object($handler))
		{
			throw new InvalidArgumentException(sprintf('Handler %s is %s, not object.', $handlerName, get_debug_type($handler)));
		}
		$handlerClassName = get_class($handler);
		$this->handlerClassNames[$handlerName] = $handlerClassName;

		$routes = new FastPriorityQueue();
		$handlerMethodNames = [];
		$commonConsumers = new FastPriorityQueue();
		$commonAttributes = new FastPriorityQueue();
		$commonProducers = new FastPriorityQueue();

		$classReflection = new ReflectionClass($handlerClassName);

		//Process class annotations
		foreach ($classReflection->getAttributes() as $phpAttributeReflection)
		{
			$phpAttribute = match ($phpAttributeReflection->getName())
			{
				PHA\Route::class,
				PHA\Consumer::class,
				PHA\Attribute::class,
				PHA\Producer::class
					=> $phpAttributeReflection->newInstance(),
				default => null,
			};
			switch (true)
			{
				case ($phpAttribute instanceof PHA\Route):
					$routes->insert([$phpAttribute->name, $phpAttribute->pattern, $phpAttribute->defaults], $phpAttribute->priority);
					break;
				case ($phpAttribute instanceof PHA\Consumer):
					$commonConsumers->insert([$phpAttribute->mediaRange, $phpAttribute->name, $phpAttribute->options], $phpAttribute->priority);
					break;
				case ($phpAttribute instanceof PHA\Attribute):
					$commonAttributes->insert([$phpAttribute->name, $phpAttribute->options], $phpAttribute->priority);
					break;
				case ($phpAttribute instanceof PHA\Producer):
					$commonProducers->insert([$phpAttribute->mediaType, $phpAttribute->name, $phpAttribute->options], $phpAttribute->priority);
					break;
			}
		}
		if ($routes->isEmpty())
		{
			throw new LogicException(sprintf('Invalid metadata for %s: no route.', $handlerClassName));
		}
		$this->routes[$handlerClassName] = $routes->toArray();

		//Process public method annotations
		foreach ($classReflection->getMethods(ReflectionMethod::IS_PUBLIC) as $methodReflection)
		{
			$handlerMethodName = $methodReflection->getName();
			$hasMetadata = false;
			$consumers = clone $commonConsumers;
			$attributes = clone $commonAttributes;
			$producers = clone $commonProducers;
			foreach ($methodReflection->getAttributes() as $phpAttributeReflection)
			{
				$phpAttribute = match ($phpAttributeReflection->getName())
				{
					PHA\HttpMethod::class,
					PHA\Get::class,
					PHA\Post::class,
					PHA\Patch::class,
					PHA\Put::class,
					PHA\Delete::class,
					PHA\Consumer::class,
					PHA\Attribute::class,
					PHA\Producer::class
						=> $phpAttributeReflection->newInstance(),
					default => null,
				};
				switch (true)
				{
					case ($phpAttribute instanceof PHA\HttpMethod):
						$httpMethod = $phpAttribute->name;
						if (!empty($handlerMethodNames[$httpMethod]))
						{
							throw new LogicException(sprintf(
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
					case ($phpAttribute instanceof PHA\Consumer):
						$consumers->insert([$phpAttribute->mediaRange, $phpAttribute->name, $phpAttribute->options], $phpAttribute->priority);
						$hasMetadata = true;
						break;
					case ($phpAttribute instanceof PHA\Attribute):
						$attributes->insert([$phpAttribute->name, $phpAttribute->options], $phpAttribute->priority);
						$hasMetadata = true;
						break;
					case ($phpAttribute instanceof PHA\Producer):
						$producers->insert([$phpAttribute->mediaType, $phpAttribute->name, $phpAttribute->options], $phpAttribute->priority);
						$hasMetadata = true;
						break;
				}
			}
			if ($hasMetadata)
			{
				if ($methodReflection->getNumberOfRequiredParameters() > 1)
				{
					throw new LogicException(sprintf(
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
			throw new LogicException(sprintf('Invalid metadata for %s: no HTTP methods.', $handlerClassName));
		}
		$this->handlerMethodNames[$handlerClassName] = $handlerMethodNames;
	}
}
