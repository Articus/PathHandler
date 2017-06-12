<?php

namespace Articus\PathHandler;

use Interop\Container\ContainerInterface;
use Zend\Cache\StorageFactory;
use Zend\Expressive\Router\ZendRouter;
use Zend\Router\Http\TreeRouteStack;
use Zend\ServiceManager\Factory\FactoryInterface;

class MiddlewareFactory implements FactoryInterface
{
	const CONFIG_KEY = 'path_handler';

	/**
	 * @inheritdoc
	 * @return Middleware
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$config = $container->get('config');
		$options = new Options(isset($config[self::CONFIG_KEY])? $config[self::CONFIG_KEY] : []);

		//Prepare router
		$router = null;
		switch (true)
		{
			case empty($options->getRoutes()):
				throw new \LogicException('PathHandler router is not configured.');
			case is_array($options->getRoutes()):
				$router = new SimpleRouter(TreeRouteStack::factory($options->getRoutes()));
				break;
			case (is_string($options->getRoutes()) && $container->has($options->getRoutes())):
				$router = $container->get($options->getRoutes());
				break;
			default:
				throw new \LogicException('Invalid configuration for PathHandler router.');
		}

		//Prepare handler plugin manager
		$handlerPluginManager = null;
		switch (true)
		{
			case empty($options->getHandlers()):
				throw new \LogicException('PathHandler handler plugin manager is not configured.');
			case is_array($options->getHandlers()):
				$handlerPluginManager = new PluginManager($container, $options->getHandlers());
				break;
			case (is_string($options->getHandlers()) && $container->has($options->getHandlers())):
				$handlerPluginManager = $container->get($options->getHandlers());
				break;
			default:
				throw new \LogicException('Invalid configuration for PathHandler handler plugin manager .');
		}

		//Prepare metadata cache storage
		$metadataCacheStorage = null;
		switch (true)
		{
			case empty($options->getMetadataCache()):
				throw new \LogicException('PathHandler metadata cache storage is not configured.');
			case is_array($options->getMetadataCache()):
				$metadataCacheStorage = StorageFactory::factory($options->getMetadataCache());
				break;
			case (is_string($options->getMetadataCache()) && $container->has($options->getMetadataCache())):
				$metadataCacheStorage = $container->get($options->getMetadataCache());
				break;
			default:
				throw new \LogicException('Invalid configuration for PathHandler metadata cache storage.');
		}

		//Prepare consumer plugin manager
		$consumerPluginManager = null;
		switch (true)
		{
			case is_array($options->getConsumers()):
				$consumerPluginManager = new Consumer\PluginManager($container, $options->getConsumers());
				break;
			case (is_string($options->getConsumers()) && $container->has($options->getConsumers())):
				$consumerPluginManager = $container->get($options->getConsumers());
				break;
			default:
				$consumerPluginManager = new Consumer\PluginManager($container);
				break;
		}

		//Prepare attribute plugin manager
		$attributePluginManager = null;
		switch (true)
		{
			case is_array($options->getAttributes()):
				$attributePluginManager = new Attribute\PluginManager($container, $options->getAttributes());
				break;
			case (is_string($options->getAttributes()) && $container->has($options->getAttributes())):
				$attributePluginManager = $container->get($options->getAttributes());
				break;
			default:
				$attributePluginManager = new Attribute\PluginManager($container);
				break;
		}

		//Prepare producer plugin manager
		$producerPluginManager = null;
		switch (true)
		{
			case is_array($options->getProducers()):
				$producerPluginManager = new Producer\PluginManager($container, $options->getProducers());
				break;
			case (is_string($options->getProducers()) && $container->has($options->getProducers())):
				$producerPluginManager = $container->get($options->getProducers());
				break;
			default:
				$producerPluginManager = new Producer\PluginManager($container);
				break;
		}

		return new Middleware(
			$options->getHandlerAttr(),
			$router,
			$handlerPluginManager,
			$metadataCacheStorage,
			$consumerPluginManager,
			$attributePluginManager,
			$producerPluginManager
		);
	}

}