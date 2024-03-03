<?php
declare(strict_types=1);

namespace Articus\PathHandler;

use Articus\PluginManager as PM;
use LogicException;
use Mezzio\Router\Route;
use Mezzio\Router\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use function is_array;
use function is_string;

/**
 * Factory that provides Mezzio router with all PathHandler routes injected into it
 */
class RouteInjectionFactory implements PM\ServiceFactoryInterface
{
	use PM\ConfigAwareFactoryTrait;
	use CacheKeyAwareTrait;

	public const DEFAULT_ATTRIBUTE_PLUGIN_MANAGER = 'Articus\PathHandler\Attribute\PluginManager';
	public const DEFAULT_CONSUMER_PLUGIN_MANAGER = 'Articus\PathHandler\Consumer\PluginManager';
	public const DEFAULT_HANDLER_PLUGIN_MANAGER = 'Articus\PathHandler\Handler\PluginManager';
	public const DEFAULT_PRODUCER_PLUGIN_MANAGER = 'Articus\PathHandler\Producer\PluginManager';

	public function __construct(string $configKey = self::class)
	{
		$this->configKey = $configKey;
	}

	public function __invoke(ContainerInterface $container, string $name): RouterInterface
	{
		$config = $this->getServiceConfig($container);

		$result = self::getInjectableRouter($container, $config['router'] ?? null);

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

	protected static function getInjectableRouter(ContainerInterface $container, mixed $options): RouterInterface
	{
		$result = null;
		switch (true)
		{
			case ($options === null):
			case is_array($options):
				$cache = self::getCache($container, Router\FastRoute::CACHE_KEY, $options['cache'] ?? null);
				$result = new Router\FastRoute($cache);
				break;
			case (is_string($options) && $container->has($options)):
				$result = $container->get($options);
				if (!($result instanceof RouterInterface))
				{
					throw new LogicException('Invalid router service for PathHandler.');
				}
				break;
			default:
				throw new LogicException('Invalid router configuration for PathHandler.');
		}
		return $result;
	}

	protected static function getMetadataProvider(ContainerInterface $container): MetadataProviderInterface
	{
		return $container->get(MetadataProviderInterface::class);
	}

	protected static function getHandlerManager(ContainerInterface $container): PM\PluginManagerInterface
	{
		return $container->get(self::DEFAULT_HANDLER_PLUGIN_MANAGER);
	}

	protected static function getConsumerManager(ContainerInterface $container): PM\PluginManagerInterface
	{
		return $container->get(self::DEFAULT_CONSUMER_PLUGIN_MANAGER);
	}

	protected static function getAttributeManager(ContainerInterface $container): PM\PluginManagerInterface
	{
		return $container->get(self::DEFAULT_ATTRIBUTE_PLUGIN_MANAGER);
	}

	protected static function getProducerManager(ContainerInterface $container): PM\PluginManagerInterface
	{
		return $container->get(self::DEFAULT_PRODUCER_PLUGIN_MANAGER);
	}

	protected static function getResponseGenerator(ContainerInterface $container): ResponseFactoryInterface
	{
		return $container->get(ResponseFactoryInterface::class);
	}
}
