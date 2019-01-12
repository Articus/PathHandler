<?php
declare(strict_types=1);

namespace Articus\PathHandler\RouteInjection;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Cache\StorageFactory;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouterInterface;
use Zend\ServiceManager\PluginManagerInterface;

/**
 * Factory that provides zend expressive router with all PathHandler routes injected into it
 */
class Factory extends PH\ConfigAwareFactory
{
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

		$result = self::getInjectableRouter($container, $options->getRouter());

		$handlerPluginManager = self::getHandlerPluginManager($container, $options->getHandlers());
		$consumerPluginManager = self::getConsumerPluginManager($container, $options->getConsumers());
		$attributePluginManager = self::getAttributePluginManager($container, $options->getAttributes());
		$producerPluginManager = self::getProducerPluginManager($container, $options->getProducers());
		$metadataProvider = self::getMetadataProvider($container, $handlerPluginManager, $options->getMetadata());
		$responseGenerator = self::getResponseGenerator($container);

		//Inject routes
		foreach ($options->getPaths() as $pathPrefix => $handlerNames)
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
				$result = new PH\Router\FastRoute(StorageFactory::factory($options['cache'] ?? []));
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
				$result = new PH\MetadataProvider\Annotation(
					$handlerPluginManager,
					StorageFactory::factory($options['cache'] ?? [])
				);
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