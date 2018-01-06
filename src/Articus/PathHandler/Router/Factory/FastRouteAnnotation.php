<?php
namespace Articus\PathHandler\Router\Factory;

use Articus\PathHandler\ConfigAwareFactory;
use Articus\PathHandler\Router;
use Interop\Container\ContainerInterface;
use Zend\Cache\StorageFactory;

class FastRouteAnnotation extends ConfigAwareFactory
{
	public function __construct($configKey = Router\FastRouteAnnotation::class)
	{
		parent::__construct($configKey);
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		//Get router options
		$options = new Router\Options\FastRouteAnnotation($this->getServiceConfig($container));
		//Prepare metadata cache storage
		$metadataCacheStorage = null;
		switch (true)
		{
			case empty($options->getMetadataCache()):
				throw new \LogicException(sprintf('Router (%s) metadata cache storage is not configured.', $requestedName));
			case is_array($options->getMetadataCache()):
				$metadataCacheStorage = StorageFactory::factory($options->getMetadataCache());
				break;
			case (is_string($options->getMetadataCache()) && $container->has($options->getMetadataCache())):
				$metadataCacheStorage = $container->get($options->getMetadataCache());
				break;
			default:
				throw new \LogicException(sprintf('Invalid configuration for router (%s) metadata cache storage.', $requestedName));
		}

		return new Router\FastRouteAnnotation($metadataCacheStorage, $options->getHandlers(), $options->getHandlerAttr());
	}

}