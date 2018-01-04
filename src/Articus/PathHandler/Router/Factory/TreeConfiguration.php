<?php
namespace Articus\PathHandler\Router\Factory;

use Articus\PathHandler\Router;
use Interop\Container\ContainerInterface;
use Zend\Router\Http\TreeRouteStack;
use Zend\ServiceManager\Factory\FactoryInterface;

class TreeConfiguration implements FactoryInterface
{
	/**
	 * Key inside Config service
	 * @var string
	 */
	protected $configKey;
	/**
	 * Factory constructor.
	 */
	public function __construct($configKey = Router\TreeConfiguration::class)
	{
		$this->configKey = $configKey;
	}

	/**
	 * Small hack to simplify configuration when you want to pass custom config key but do not want to create extra class or anonymous function.
	 * So for example in your configuration YAML file you can use:
	 * dependencies:
	 *   factories:
	 *     my_router: [Articus\PathHandler\Router\Factory\TreeConfiguration, my_router_config]
	 * my_router_config:
	 *   routes: []
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
		$treeRouter = TreeRouteStack::factory(empty($config[$this->configKey])? [] : $config[$this->configKey]);
		return new Router\TreeConfiguration($treeRouter);
	}

}