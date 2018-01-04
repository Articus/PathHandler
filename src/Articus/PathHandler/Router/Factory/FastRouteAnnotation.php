<?php
namespace Articus\PathHandler\Router\Factory;

use Articus\PathHandler\Router;
use Interop\Container\ContainerInterface;
use Zend\Cache\StorageFactory;
use Zend\ServiceManager\Factory\FactoryInterface;

class FastRouteAnnotation implements FactoryInterface
{
	/**
	 * Key inside Config service
	 * @var string
	 */
	protected $configKey;
	/**
	 * Factory constructor.
	 */
	public function __construct($configKey = Router\FastRouteAnnotation::class)
	{
		$this->configKey = $configKey;
	}

	/**
	 * Small hack to simplify configuration when you want to pass custom config key but do not want to create extra class or anonymous function.
	 * So for example in your configuration YAML file you can use:
	 * dependencies:
	 *   factories:
	 *     my_router: [Articus\PathHandler\Router\Factory\FastRouteAnnotation, my_router_config]
	 * my_router_config:
	 *   handlers: [App\MyHandler]
	 * path_handler:
	 *   routes: my_router
	 */
	public static function __callStatic($name, array $arguments)
	{
		if (count($arguments) < 3)
		{
			throw new \InvalidArgumentException(sprintf(
				'To invoke %s with custom configuration key statically 3 arguments are required: container, service name and options.',
				static::class
			));
		}
		return (new static($name))->__invoke($arguments[0], $arguments[1], $arguments[2]);
	}


	/**
	 * @inheritdoc
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$config = $container->get('config');
		//Get router options
		$options = new Router\Options\FastRouteAnnotation(empty($config[$this->configKey])? [] : $config[$this->configKey]);
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