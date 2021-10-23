<?php
declare(strict_types=1);

namespace Articus\PathHandler;

use Interop\Container\ContainerInterface;
use Mezzio\Router\Route;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Factory that provides Mezzio router with all PathHandler routes injected into it
 */
class RouteInjectionFactory extends ConfigAwareFactory
{
	use CacheKeyAwareTrait;

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
		$config = \array_merge($this->getServiceConfig($container), $options ?? []);

		$result = self::getInjectableRouter($container, $config['router'] ?? ['cache' => null]);

		$handlerPluginManager = self::getHandlerManager($container);
		$consumerPluginManager = self::getConsumerManager($container);
		$attributePluginManager = self::getAttributeManager($container);
		$producerPluginManager = self::getProducerManager($container);
		$metadataProvider = self::getMetadataProvider($container);
		$responseGenerator = self::getResponseGenerator($container);
		$defaultProducer = [
			$config['default_producer']['media_type'] ?? 'text/plain',
			$config['default_producer']['name'] ?? Producer\Text::class,
			$config['default_producer']['options'] ?? [],
		];

		//Inject routes
		foreach (($config['paths'] ?? []) as $pathPrefix => $handlerNames)
		{
			foreach ($handlerNames as $handlerName)
			{
				$httpMethods = $metadataProvider->getHttpMethods($handlerName);
				foreach ($metadataProvider->getRoutes($handlerName) as [$routeName, $pattern, $defaults])
				{
					$middleware = new Middleware(
						$handlerName,
						$metadataProvider,
						$handlerPluginManager,
						$consumerPluginManager,
						$attributePluginManager,
						$producerPluginManager,
						$responseGenerator,
						$defaultProducer
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
	 * @param null|array|string $options
	 * @return RouterInterface
	 */
	protected static function getInjectableRouter(ContainerInterface $container, $options): RouterInterface
	{
		$result = null;
		switch (true)
		{
			case empty($options):
				throw new \LogicException('Router is not configured for PathHandler.');
			case \is_array($options):
				$cache = self::getCache($container, Router\FastRoute::CACHE_KEY, $options['cache'] ?? []);
				$result = new Router\FastRoute($cache);
				break;
			case (\is_string($options) && $container->has($options)):
				$result = $container->get($options);
				if (!($result instanceof RouterInterface))
				{
					throw new \LogicException('Invalid router service for PathHandler.');
				}
				break;
			default:
				throw new \LogicException('Invalid router configuration for PathHandler.');
		}
		return $result;
	}

	protected static function getMetadataProvider(ContainerInterface $container): MetadataProviderInterface
	{
		return $container->get(MetadataProviderInterface::class);
	}

	protected static function getHandlerManager(ContainerInterface $container): Handler\PluginManager
	{
		return $container->get(Handler\PluginManager::class);
	}

	protected static function getConsumerManager(ContainerInterface $container): Consumer\PluginManager
	{
		return $container->get(Consumer\PluginManager::class);
	}

	protected static function getAttributeManager(ContainerInterface $container): Attribute\PluginManager
	{
		return $container->get(Attribute\PluginManager::class);
	}

	protected static function getProducerManager(ContainerInterface $container): Producer\PluginManager
	{
		return $container->get(Producer\PluginManager::class);
	}

	protected static function getResponseGenerator(ContainerInterface $container): callable
	{
		return $container->get(ResponseInterface::class);
	}
}