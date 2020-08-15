<?php
declare(strict_types=1);

namespace Articus\PathHandler\RouteInjection;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Mezzio\Router\Route;
use Mezzio\Router\RouterInterface;
use Laminas\ServiceManager\PluginManagerInterface;

/**
 * Factory that provides zend expressive router with all PathHandler routes injected into it
 */
class Factory extends PH\ConfigAwareFactory
{
	protected const CACHE_KEYS = [
		PH\MetadataProvider\Annotation::class => PH\MetadataProvider\Annotation::CACHE_KEY,
		PH\Router\FastRoute::class => PH\Router\FastRoute::CACHE_KEY,
	];

	public function __construct(string $configKey = self::class)
	{
		parent::__construct($configKey);
	}

	/**
	 * @inheritdoc
	 * @return RouterInterface
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \ReflectionException
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RouterInterface
	{
		$options = new Options(\array_merge($this->getServiceConfig($container), $options ?? []));

		$result = self::getInjectableRouter($container, $options->router);

		$handlerPluginManager = self::getHandlerPluginManager($container, $options->handlers);
		$consumerPluginManager = self::getConsumerPluginManager($container, $options->consumers);
		$attributePluginManager = self::getAttributePluginManager($container, $options->attributes);
		$producerPluginManager = self::getProducerPluginManager($container, $options->producers);
		$metadataProvider = self::getMetadataProvider($container, $handlerPluginManager, $options->metadata);
		$responseGenerator = self::getResponseGenerator($container);

		//Inject routes
		foreach ($options->paths as $pathPrefix => $handlerNames)
		{
			foreach ($handlerNames as $handlerName)
			{
				$httpMethods = $metadataProvider->getHttpMethods($handlerName);
				foreach ($metadataProvider->getRoutes($handlerName) as [$routeName, $pattern, $defaults])
				{
					$middleware = new PH\Middleware(
						$handlerName,
						$metadataProvider,
						$handlerPluginManager,
						$consumerPluginManager,
						$attributePluginManager,
						$producerPluginManager,
						$responseGenerator
					);
					$route = new Route($pathPrefix . $pattern, $middleware, $httpMethods, $routeName);
					if (!empty($defaults))
					{
						$route->setOptions(['defaults' => $defaults]);
					}
					$result->addRoute($route);
				}
			}
		}

		return $result;
	}

	/**
	 * @param ContainerInterface $container
	 * @param string $cacheAwareClass
	 * @param $options
	 * @return CacheInterface
	 */
	protected static function getCache(ContainerInterface $container, string $cacheAwareClass, $options): CacheInterface
	{
		$result = null;
		switch (true)
		{
			case ($options === null):
			case \is_array($options):
				$result = new PH\Cache\DataFilePerKey(self::CACHE_KEYS[$cacheAwareClass], $options['directory'] ?? null);
				break;
			case (\is_string($options) && $container->has($options)):
				$result = $container->get($options);
				if (!($result instanceof CacheInterface))
				{
					throw new \LogicException(\sprintf('Invalid cache service for "%s".', $cacheAwareClass));
				}
				break;
			default:
				throw new \LogicException(\sprintf('Invalid configuration for "%s" cache.', $cacheAwareClass));
		}
		return $result;
	}

	/**
	 * @param ContainerInterface $container
	 * @param array|string $options
	 * @return RouterInterface
	 */
	protected static function getInjectableRouter(ContainerInterface $container, $options): RouterInterface
	{
		$result = null;
		switch (true)
		{
			case empty($options):
				throw new \LogicException('PathHandler router is not configured.');
			case \is_array($options):
				$cache = self::getCache($container, PH\Router\FastRoute::class, $options['cache'] ?? []);
				$result = new PH\Router\FastRoute($cache);
				break;
			case (\is_string($options) && $container->has($options)):
				$result = $container->get($options);
				if (!($result instanceof RouterInterface))
				{
					throw new \LogicException('Invalid router for PathHandler.');
				}
				break;
			default:
				throw new \LogicException('Invalid configuration for PathHandler router.');
		}
		return $result;
	}

	/**
	 * @param ContainerInterface $container
	 * @param array|string $options
	 * @return PluginManagerInterface
	 */
	protected static function getHandlerPluginManager(ContainerInterface $container, $options): PluginManagerInterface
	{
		$result = null;
		switch (true)
		{
			case \is_array($options):
				$result = new PH\PluginManager($container, $options);
				break;
			case (\is_string($options) && $container->has($options)):
				$result = $container->get($options);
				if (!($result instanceof PluginManagerInterface))
				{
					throw new \LogicException('Invalid handler plugin manager for PathHandler.');
				}
				break;
			default:
				throw new \LogicException('Invalid configuration for PathHandler handler plugin manager.');
		}
		return $result;
	}

	/**
	 * @param ContainerInterface $container
	 * @param array|string $options
	 * @return PluginManagerInterface
	 */
	protected static function getConsumerPluginManager(ContainerInterface $container, $options): PluginManagerInterface
	{
		$result = null;
		switch (true)
		{
			case \is_array($options):
				$result = new PH\Consumer\PluginManager($container, $options);
				break;
			case (\is_string($options) && $container->has($options)):
				$result = $container->get($options);
				if (!($result instanceof PluginManagerInterface))
				{
					throw new \LogicException('Invalid consumer plugin manager for PathHandler.');
				}
				break;
			default:
				throw new \LogicException('Invalid configuration for PathHandler consumer plugin manager.');
		}
		return $result;
	}

	/**
	 * @param ContainerInterface $container
	 * @param array|string $options
	 * @return PluginManagerInterface
	 */
	protected static function getAttributePluginManager(ContainerInterface $container, $options): PluginManagerInterface
	{
		$result = null;
		switch (true)
		{
			case \is_array($options):
				$result = new PH\Attribute\PluginManager($container, $options);
				break;
			case (\is_string($options) && $container->has($options)):
				$result = $container->get($options);
				if (!($result instanceof PluginManagerInterface))
				{
					throw new \LogicException('Invalid attribute plugin manager for PathHandler.');
				}
				break;
			default:
				throw new \LogicException('Invalid configuration for PathHandler attribute plugin manager.');
		}
		return $result;
	}

	/**
	 * @param ContainerInterface $container
	 * @param array|string $options
	 * @return PluginManagerInterface
	 */
	protected static function getProducerPluginManager(ContainerInterface $container, $options): PluginManagerInterface
	{
		$result = null;
		switch (true)
		{
			case \is_array($options):
				$result = new PH\Producer\PluginManager($container, $options);
				break;
			case (\is_string($options) && $container->has($options)):
				$result = $container->get($options);
				if (!($result instanceof PluginManagerInterface))
				{
					throw new \LogicException('Invalid producer plugin manager for PathHandler.');
				}
				break;
			default:
				throw new \LogicException('Invalid configuration for PathHandler producer plugin manager.');
		}
		return $result;
	}

	/**
	 * @param ContainerInterface $container
	 * @return callable
	 */
	protected static function getResponseGenerator(ContainerInterface $container): callable
	{
		$result = $container->get(ResponseInterface::class);
		if (!\is_callable($result))
		{
			throw new \LogicException('Invalid response generator for PathHandler.');
		}
		return $result;
	}

	/**
	 * @param ContainerInterface $container
	 * @param PluginManagerInterface $handlerPluginManager
	 * @param $options
	 * @return PH\MetadataProviderInterface
	 */
	protected static function getMetadataProvider(ContainerInterface $container, PluginManagerInterface $handlerPluginManager, $options): PH\MetadataProviderInterface
	{
		$result = null;
		switch (true)
		{
			case empty($options):
				throw new \LogicException('PathHandler metadata provider is not configured.');
			case \is_array($options):
				$cache = self::getCache($container, PH\MetadataProvider\Annotation::class, $options['cache'] ?? []);
				$result = new PH\MetadataProvider\Annotation($handlerPluginManager, $cache);
				break;
			case (\is_string($options) && $container->has($options)):
				$result = $container->get($options);
				if (!($result instanceof PH\MetadataProviderInterface))
				{
					throw new \LogicException('Invalid metadata provider for PathHandler.');
				}
				break;
			default:
				throw new \LogicException('Invalid configuration for PathHandler metadata provider.');
		}
		return $result;
	}
}